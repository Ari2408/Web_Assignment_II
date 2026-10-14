<?php
/**
 * Dedicated File Upload Handler (upload_file.php)
 * Handles secure file uploads for an existing student with server-side validation
 * for size, extension, MIME type, and unique filename generation.
 */
require_once __DIR__ . '/db.php';

// Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    setFlash('error', 'Method Not Allowed. Uploads must be submitted via POST.');
    header('Location: index.php');
    exit();
}

// 1. Verify CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Security token invalid or expired. Upload rejected.');
    header('Location: index.php');
    exit();
}

// 2. Validate Target Student ID
$studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
if (!$studentId) {
    setFlash('error', 'Invalid student identifier provided.');
    header('Location: index.php');
    exit();
}

// Confirm student exists in database
try {
    $stmtStudent = $pdo->prepare("SELECT id, name FROM students WHERE id = :id LIMIT 1");
    $stmtStudent->execute([':id' => $studentId]);
    $student = $stmtStudent->fetch();

    if (!$student) {
        setFlash('error', 'Student not found in database.');
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    setFlash('error', 'Database verification error: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}

// 3. Validate Uploaded File
if (!isset($_FILES['student_file']) || $_FILES['student_file']['error'] === UPLOAD_ERR_NO_FILE) {
    setFlash('error', 'Please select a file to upload.');
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();
}

$uploadedFile = $_FILES['student_file'];

// Check upload status code
if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE specified in the form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary server folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
    ];
    $msg = $errorMessages[$uploadedFile['error']] ?? 'Unknown upload error occurred.';
    setFlash('error', 'Upload failed: ' . $msg);
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();
}

// Check File Size (Max 2 MB)
if ($uploadedFile['size'] > MAX_FILE_SIZE) {
    setFlash('error', 'File size exceeds 2 MB limit (' . formatBytes($uploadedFile['size']) . '). Please choose a smaller file.');
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();
}

// Check File Extension
$originalName = basename($uploadedFile['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
    setFlash('error', 'Disallowed file extension ".' . htmlspecialchars($extension) . '". Only PDF, JPG, JPEG, and PNG files are accepted.');
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();
}

// Verify Real MIME Type (Server-side inspection, ignoring client headers)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedMime = finfo_file($finfo, $uploadedFile['tmp_name']);
finfo_close($finfo);

if (!in_array($detectedMime, ALLOWED_MIME_TYPES, true)) {
    setFlash('error', 'Invalid file type detected (' . htmlspecialchars($detectedMime) . '). Only legitimate PDF and images are accepted.');
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();
}

// 4. Generate Unique Stored Filename to Prevent Collisions & Overwrites
$uniqueStoredFilename = bin2hex(random_bytes(16)) . '.' . $extension;
$destinationPath = UPLOAD_DIR . $uniqueStoredFilename;
$relativePath = 'uploads/' . $uniqueStoredFilename;

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        setFlash('error', 'Server error: Unable to create uploads directory.');
        header('Location: view_student.php?id=' . $studentId . '#documents');
        exit();
    }
}

// Move file from temporary directory to uploads/
if (!move_uploaded_file($uploadedFile['tmp_name'], $destinationPath)) {
    setFlash('error', 'Failed to move uploaded file to destination storage.');
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();
}

// 5. Store Metadata in MySQL student_files Table
try {
    $stmtInsert = $pdo->prepare("
        INSERT INTO student_files (student_id, original_filename, stored_filename, file_path, file_type, file_size, upload_date)
        VALUES (:student_id, :orig_name, :stored_name, :file_path, :file_type, :file_size, NOW())
    ");
    $stmtInsert->execute([
        ':student_id'   => $studentId,
        ':orig_name'    => $originalName,
        ':stored_name'  => $uniqueStoredFilename,
        ':file_path'    => $relativePath,
        ':file_type'    => $detectedMime,
        ':file_size'    => $uploadedFile['size']
    ]);

    setFlash('success', 'File "' . $originalName . '" (' . formatBytes($uploadedFile['size']) . ') uploaded successfully!');

} catch (PDOException $e) {
    // If DB insert fails, remove the newly uploaded file from disk to avoid orphans
    if (file_exists($destinationPath)) {
        @unlink($destinationPath);
    }
    setFlash('error', 'Database error saving file metadata: ' . $e->getMessage());
}

header('Location: view_student.php?id=' . $studentId . '#documents');
exit();
