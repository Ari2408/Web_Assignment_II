<?php
/**
 * Delete Individual File Controller (delete_file.php)
 * Handles secure POST-based deletion of an uploaded student file, removing both
 * the physical file from disk and its metadata row from MySQL.
 */
require_once __DIR__ . '/db.php';

// Enforce POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    setFlash('error', 'Method Not Allowed. File deletion must be performed via POST.');
    header('Location: index.php');
    exit();
}

// 1. Verify CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Security token invalid or expired. Action aborted.');
    header('Location: index.php');
    exit();
}

// 2. Validate File ID
$fileId = filter_input(INPUT_POST, 'file_id', FILTER_VALIDATE_INT);
if (!$fileId) {
    setFlash('error', 'Invalid file identifier.');
    header('Location: index.php');
    exit();
}

try {
    // 3. Fetch file details to locate file on disk and determine student redirection
    $stmtFile = $pdo->prepare("SELECT id, student_id, original_filename, stored_filename FROM student_files WHERE id = :id LIMIT 1");
    $stmtFile->execute([':id' => $fileId]);
    $fileRecord = $stmtFile->fetch();

    if (!$fileRecord) {
        setFlash('error', 'File record not found in database.');
        header('Location: index.php');
        exit();
    }

    $studentId = (int) $fileRecord['student_id'];
    $originalName = $fileRecord['original_filename'];
    $diskPath = UPLOAD_DIR . $fileRecord['stored_filename'];

    // 4. Delete physical file from disk
    if (file_exists($diskPath) && is_file($diskPath)) {
        @unlink($diskPath);
    }

    // 5. Delete metadata record from database
    $stmtDelete = $pdo->prepare("DELETE FROM student_files WHERE id = :id");
    $stmtDelete->execute([':id' => $fileId]);

    setFlash('success', 'File "' . $originalName . '" successfully removed.');
    header('Location: view_student.php?id=' . $studentId . '#documents');
    exit();

} catch (PDOException $e) {
    setFlash('error', 'Database error deleting file record: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}
