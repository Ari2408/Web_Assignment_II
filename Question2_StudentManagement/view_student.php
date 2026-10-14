<?php
/**
 * View Student Profile & File Management (view_student.php)
 * Displays detailed student academic record, document upload form, and an interactive
 * gallery of attached verification documents and images with thumbnail previews and downloads.
 */
require_once __DIR__ . '/db.php';

// 1. Retrieve & Validate Student ID from GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('error', 'Invalid student ID specified.');
    header('Location: index.php');
    exit();
}

// 2. Fetch Student Information
try {
    $stmtStudent = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
    $stmtStudent->execute([':id' => $id]);
    $student = $stmtStudent->fetch();

    if (!$student) {
        setFlash('error', 'Student record not found.');
        header('Location: index.php');
        exit();
    }

    // 3. Fetch All Associated Uploaded Files
    $stmtFiles = $pdo->prepare("
        SELECT id, original_filename, stored_filename, file_path, file_type, file_size, upload_date
        FROM student_files
        WHERE student_id = :id
        ORDER BY id DESC
    ");
    $stmtFiles->execute([':id' => $id]);
    $files = $stmtFiles->fetchAll();

} catch (PDOException $e) {
    setFlash('error', 'Database error: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}

$pageTitle = $student['name'] . ' Profile';

// Generate initials for avatar
$initials = '';
$nameParts = explode(' ', trim($student['name']));
foreach ($nameParts as $part) {
    if (!empty($part)) $initials .= strtoupper($part[0]);
    if (strlen($initials) >= 2) break;
}

require_once __DIR__ . '/header.php';
?>

<div class="breadcrumb-nav">
    <a href="index.php">Dashboard</a> &rsaquo; 
    <span><?php echo e($student['name']); ?></span>
</div>

<div class="profile-layout">
    <!-- Left Column: Student Details Card -->
    <div class="profile-sidebar">
        <div class="card profile-card">
            <div class="profile-header">
                <div class="profile-avatar" aria-hidden="true">
                    <?php echo e($initials ?: 'ST'); ?>
                </div>
                <h2 class="profile-name"><?php echo e($student['name']); ?></h2>
                <span class="profile-roll"><?php echo e($student['roll_number']); ?></span>
                <span class="course-badge course-badge-<?php echo strtolower(e($student['course'])); ?>">
                    <?php echo e($student['course']); ?> Engineering
                </span>
            </div>

            <div class="profile-info-list">
                <div class="info-row">
                    <span class="info-label">Student ID</span>
                    <strong class="info-value">#<?php echo e($student['id']); ?></strong>
                </div>
                <div class="info-row">
                    <span class="info-label">Email Address</span>
                    <a href="mailto:<?php echo e($student['email']); ?>" class="info-value info-link">
                        <?php echo e($student['email']); ?>
                    </a>
                </div>
                <div class="info-row">
                    <span class="info-label">Branch / Department</span>
                    <strong class="info-value"><?php echo e($student['course']); ?></strong>
                </div>
                <div class="info-row">
                    <span class="info-label">Enrolled On</span>
                    <strong class="info-value"><?php echo date('F d, Y \a\t h:i A', strtotime($student['created_at'])); ?></strong>
                </div>
                <div class="info-row">
                    <span class="info-label">Uploaded Files</span>
                    <strong class="info-value"><?php echo count($files); ?> document<?php echo count($files) === 1 ? '' : 's'; ?></strong>
                </div>
            </div>

            <div class="profile-actions">
                <a href="edit_student.php?id=<?php echo e($student['id']); ?>" class="btn btn-primary btn-block">
                    <span>✏️ Edit Profile</span>
                </a>
                <button 
                    type="button" 
                    class="btn btn-danger-outline btn-block" 
                    onclick="confirmDelete(<?php echo (int) $student['id']; ?>, '<?php echo addslashes(e($student['name'])); ?>', '<?php echo addslashes(e($student['roll_number'])); ?>')"
                >
                    <span>🗑️ Delete Student</span>
                </button>
                <a href="index.php" class="btn btn-secondary btn-block">
                    &larr; Back to Directory
                </a>
            </div>
        </div>
    </div>

    <!-- Right Column: Document Upload & Attached Files Gallery -->
    <div class="profile-main" id="documents">
        
        <!-- Upload Document Box -->
        <div class="card upload-card">
            <div class="card-header-bar">
                <div class="header-titles">
                    <h3 class="section-title">Upload Verification Document or Photo</h3>
                    <p class="section-subtitle">Add verification documents, certificates, or profile pictures for this student.</p>
                </div>
            </div>

            <form method="POST" action="upload_file.php" enctype="multipart/form-data" class="upload-inline-form">
                <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                <input type="hidden" name="student_id" value="<?php echo e($student['id']); ?>">

                <div class="upload-controls-row">
                    <div class="file-picker-wrapper">
                        <input 
                            type="file" 
                            name="student_file" 
                            id="student_file" 
                            class="file-control"
                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                            required
                        >
                        <span class="upload-specs-hint">Allowed: <strong>PDF, JPG, JPEG, PNG</strong> &bull; Max: <strong>2 MB</strong></span>
                    </div>

                    <button type="submit" class="btn btn-primary upload-btn">
                        <span>📤 Upload File</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Attached Documents Gallery -->
        <div class="card files-gallery-card">
            <div class="card-header-bar">
                <div class="header-titles">
                    <h3 class="section-title">Attached Documents &amp; Media</h3>
                    <p class="section-subtitle">Manage all documents and images uploaded for <strong><?php echo e($student['name']); ?></strong>.</p>
                </div>
                <span class="badge badge-counter"><?php echo count($files); ?> File<?php echo count($files) === 1 ? '' : 's'; ?></span>
            </div>

            <?php if (empty($files)): ?>
                <div class="empty-state files-empty">
                    <span class="empty-icon">📁</span>
                    <h4>No Documents Attached</h4>
                    <p>No documents or profile images have been uploaded for this student yet.</p>
                    <p class="empty-hint">Use the upload box above to attach a verification PDF or photo.</p>
                </div>
            <?php else: ?>
                <div class="files-grid">
                    <?php foreach ($files as $file): ?>
                        <?php 
                            $isImage = strpos($file['file_type'], 'image/') === 0;
                            $ext = strtoupper(pathinfo($file['original_filename'], PATHINFO_EXTENSION));
                            $downloadUrl = 'download_file.php?id=' . e($file['id']) . '&mode=download';
                            $viewUrl = 'download_file.php?id=' . e($file['id']) . '&mode=inline';
                        ?>
                        <div class="file-item-card">
                            <div class="file-item-preview">
                                <?php if ($isImage): ?>
                                    <a href="<?php echo $viewUrl; ?>" target="_blank" title="Click to view full image" class="image-preview-link">
                                        <img src="<?php echo $viewUrl; ?>" alt="<?php echo e($file['original_filename']); ?>" class="file-thumbnail" loading="lazy">
                                    </a>
                                <?php else: ?>
                                    <div class="file-pdf-placeholder" aria-hidden="true">
                                        <span class="pdf-icon">📄</span>
                                        <span class="pdf-tag"><?php echo e($ext); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="file-item-info">
                                <span class="file-title" title="<?php echo e($file['original_filename']); ?>">
                                    <?php echo e($file['original_filename']); ?>
                                </span>
                                <div class="file-sub-meta">
                                    <span class="file-size-badge"><?php echo formatBytes((int) $file['file_size']); ?></span>
                                    <span class="file-date"><?php echo date('M d, Y', strtotime($file['upload_date'])); ?></span>
                                </div>
                            </div>

                            <div class="file-item-actions">
                                <a href="<?php echo $viewUrl; ?>" target="_blank" class="btn-file-action" title="View in new browser tab">
                                    👁️ View
                                </a>
                                <a href="<?php echo $downloadUrl; ?>" class="btn-file-action" title="Download file to device">
                                    ⬇️ Download
                                </a>
                                <form method="POST" action="delete_file.php" onsubmit="return confirm('Permanently delete this file (<?php echo addslashes(e($file['original_filename'])); ?>)?');" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
                                    <input type="hidden" name="file_id" value="<?php echo e($file['id']); ?>">
                                    <button type="submit" class="btn-file-action btn-file-delete" title="Delete file">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
