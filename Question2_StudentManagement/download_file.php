<?php
/**
 * Secure File Delivery Handler (download_file.php)
 * Serves uploaded documents and images securely with proper MIME headers and disposition,
 * preventing arbitrary file execution and path traversal attacks.
 */
require_once __DIR__ . '/db.php';

// 1. Validate File ID
$fileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$mode = ($_GET['mode'] ?? 'inline') === 'download' ? 'attachment' : 'inline';

if (!$fileId) {
    http_response_code(400);
    exit('Invalid file ID.');
}

try {
    // 2. Fetch File Record from Database
    $stmt = $pdo->prepare("SELECT original_filename, stored_filename, file_type, file_size FROM student_files WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        http_response_code(404);
        exit('File not found in system records.');
    }

    $filePath = UPLOAD_DIR . $file['stored_filename'];

    // 3. Confirm File Exists on Disk
    if (!file_exists($filePath) || !is_file($filePath)) {
        http_response_code(404);
        exit('File could not be found on server storage.');
    }

    // 4. Sanitize Filename for HTTP Header
    $safeFilename = str_replace(['"', "'", "\r", "\n"], '', basename($file['original_filename']));

    // 5. Send Secure Download/Display Headers
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['file_type']);
    header('Content-Disposition: ' . $mode . '; filename="' . $safeFilename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($filePath));
    header('Expires: 0');
    header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('X-Content-Type-Options: nosniff');

    // Clear output buffer and stream file
    if (ob_get_level()) {
        ob_end_clean();
    }
    readfile($filePath);
    exit();

} catch (PDOException $e) {
    http_response_code(500);
    exit('Database error: ' . $e->getMessage());
}
