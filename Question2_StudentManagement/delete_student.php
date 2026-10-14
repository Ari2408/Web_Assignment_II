<?php
/**
 * Delete Student Controller (delete_student.php)
 * Handles secure POST-based deletion of a student record and permanently unlinks
 * all associated files from disk to prevent orphaned files.
 */
require_once __DIR__ . '/db.php';

// Enforce POST method for destructive state-changing actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    setFlash('error', 'Method Not Allowed. Deletions must be performed via POST.');
    header('Location: index.php');
    exit();
}

// 1. Verify CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    setFlash('error', 'Invalid security token or session expired. Action aborted.');
    header('Location: index.php');
    exit();
}

// 2. Validate Student ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('error', 'Invalid student identifier provided for deletion.');
    header('Location: index.php');
    exit();
}

try {
    // 3. Fetch student info to confirm existence and get name for feedback
    $stmtStudent = $pdo->prepare("SELECT name, roll_number FROM students WHERE id = :id LIMIT 1");
    $stmtStudent->execute([':id' => $id]);
    $student = $stmtStudent->fetch();

    if (!$student) {
        setFlash('error', 'Student record not found or already deleted.');
        header('Location: index.php');
        exit();
    }

    // 4. Fetch all associated uploaded files to delete from disk
    $stmtFiles = $pdo->prepare("SELECT stored_filename FROM student_files WHERE student_id = :id");
    $stmtFiles->execute([':id' => $id]);
    $files = $stmtFiles->fetchAll();

    $deletedFilesCount = 0;
    foreach ($files as $fileRecord) {
        $filePath = UPLOAD_DIR . $fileRecord['stored_filename'];
        if (file_exists($filePath) && is_file($filePath)) {
            if (@unlink($filePath)) {
                $deletedFilesCount++;
            }
        }
    }

    // 5. Delete student record (Foreign key ON DELETE CASCADE cleans up student_files records)
    $stmtDelete = $pdo->prepare("DELETE FROM students WHERE id = :id");
    $stmtDelete->execute([':id' => $id]);

    $feedback = 'Student "' . $student['name'] . '" (' . $student['roll_number'] . ')';
    if ($deletedFilesCount > 0) {
        $feedback .= ' and ' . $deletedFilesCount . ' attached file(s)';
    }
    $feedback .= ' successfully deleted.';

    setFlash('success', $feedback);

} catch (PDOException $e) {
    setFlash('error', 'Database deletion error: ' . $e->getMessage());
}

header('Location: index.php');
exit();
