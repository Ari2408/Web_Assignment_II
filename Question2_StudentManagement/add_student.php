<?php
/**
 * Add Student Page (add_student.php)
 * Handles registration form display and server-side processing for new student creation,
 * including roll number uniqueness validation and optional document attachment.
 */
require_once __DIR__ . '/db.php';

$pageTitle = 'Add New Student';

$errors = [];
$name = '';
$rollNumber = '';
$email = '';
$course = '';

// Handle Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid or expired session security token. Please refresh and try again.';
    }

    // 2. Sanitize and retrieve form inputs
    $name = trim($_POST['name'] ?? '');
    $rollNumber = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    // 3. Server-side Validation: Name
    if ($name === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be between 2 and 100 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
        $errors['name'] = 'Name can only contain alphabetic letters, spaces, hyphens, and apostrophes.';
    }

    // 4. Server-side Validation: Roll Number & Uniqueness
    if ($rollNumber === '') {
        $errors['roll_number'] = 'Roll number is required.';
    } elseif (!preg_match('/^[A-Za-z0-9\/-]{2,50}$/', $rollNumber)) {
        $errors['roll_number'] = 'Roll number must be between 2 and 50 alphanumeric characters (e.g., 23CSE0101).';
    } else {
        // Query database to ensure uniqueness of roll number
        try {
            $stmtCheckRoll = $pdo->prepare("SELECT id FROM students WHERE roll_number = :roll LIMIT 1");
            $stmtCheckRoll->execute([':roll' => $rollNumber]);
            if ($stmtCheckRoll->fetch()) {
                $errors['roll_number'] = 'This roll number is already registered for another student.';
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Database error checking roll number: ' . $e->getMessage();
        }
    }

    // 5. Server-side Validation: Email
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }

    // 6. Server-side Validation: Course
    if ($course === '' || !in_array($course, ALLOWED_COURSES, true)) {
        $errors['course'] = 'Please select a valid course from the list.';
    }

    // 7. Validate Optional Attached File
    $fileUploaded = false;
    $fileOriginalName = '';
    $fileStoredName = '';
    $fileRelPath = '';
    $fileMime = '';
    $fileSize = 0;

    if (isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['document'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['document'] = 'File upload failed with error code: ' . $file['error'];
        } elseif ($file['size'] > MAX_FILE_SIZE) {
            $errors['document'] = 'File size exceeds 2 MB limit (' . formatBytes($file['size']) . ').';
        } else {
            // Validate extension
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
                $errors['document'] = 'Disallowed file extension. Accepted formats: PDF, JPG, JPEG, PNG.';
            } else {
                // Validate true MIME type using PHP fileinfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detectedMime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($detectedMime, ALLOWED_MIME_TYPES, true)) {
                    $errors['document'] = 'Invalid file MIME type (' . htmlspecialchars($detectedMime) . '). Only legitimate PDF and images are accepted.';
                } else {
                    $fileUploaded = true;
                    $fileOriginalName = basename($file['name']);
                    $fileStoredName = bin2hex(random_bytes(16)) . '.' . $ext;
                    $fileRelPath = 'uploads/' . $fileStoredName;
                    $fileMime = $detectedMime;
                    $fileSize = $file['size'];
                }
            }
        }
    }

    // 8. If No Errors: Execute Database Insertion within a Transaction
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert Student Record
            $stmtInsert = $pdo->prepare("
                INSERT INTO students (name, roll_number, email, course, created_at)
                VALUES (:name, :roll_number, :email, :course, NOW())
            ");
            $stmtInsert->execute([
                ':name'        => $name,
                ':roll_number' => $rollNumber,
                ':email'       => $email,
                ':course'      => $course
            ]);

            $studentId = (int) $pdo->lastInsertId();

            // Move file and insert into student_files if provided
            if ($fileUploaded) {
                // Ensure upload directory exists
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }

                $targetPath = UPLOAD_DIR . $fileStoredName;
                if (!move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {
                    throw new Exception('Failed to save uploaded file to disk.');
                }

                // Insert into student_files
                $stmtFile = $pdo->prepare("
                    INSERT INTO student_files (student_id, original_filename, stored_filename, file_path, file_type, file_size, upload_date)
                    VALUES (:student_id, :original_filename, :stored_filename, :file_path, :file_type, :file_size, NOW())
                ");
                $stmtFile->execute([
                    ':student_id'        => $studentId,
                    ':original_filename' => $fileOriginalName,
                    ':stored_filename'   => $fileStoredName,
                    ':file_path'         => $fileRelPath,
                    ':file_type'         => $fileMime,
                    ':file_size'         => $fileSize
                ]);
            }

            $pdo->commit();

            setFlash('success', 'Student "' . $name . '" registered successfully!');
            header('Location: view_student.php?id=' . $studentId);
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            // Clean up stored file if created before DB failure
            if ($fileUploaded && isset($targetPath) && file_exists($targetPath)) {
                @unlink($targetPath);
            }
            $errors['database'] = 'Failed to register student: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="breadcrumb-nav">
    <a href="index.php">Dashboard</a> &rsaquo; <span>Add New Student</span>
</div>

<div class="form-container">
    <div class="card form-card">
        <div class="card-header-bar">
            <div class="header-titles">
                <h2 class="section-title">Register New Student</h2>
                <p class="section-subtitle">Enter student academic details and attach optional verification documents.</p>
            </div>
            <a href="index.php" class="btn btn-secondary btn-sm">
                &larr; Back to Directory
            </a>
        </div>

        <?php if (!empty($errors['csrf']) || !empty($errors['database'])): ?>
            <div class="alert alert-error">
                <?php echo e($errors['csrf'] ?? $errors['database']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_student.php" enctype="multipart/form-data" class="app-form" novalidate>
            <!-- CSRF Protection Token -->
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">

            <!-- Form Row: Name & Roll Number -->
            <div class="form-row">
                <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                    <label for="name" class="form-label">
                        Full Name <span class="required-star">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        placeholder="e.g. Aarav Sharma"
                        value="<?php echo e($name); ?>"
                        required
                    >
                    <?php if (isset($errors['name'])): ?>
                        <span class="error-text">⚠ <?php echo e($errors['name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($errors['roll_number']) ? 'has-error' : ''; ?>">
                    <label for="roll_number" class="form-label">
                        Roll Number <span class="required-star">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="roll_number" 
                        name="roll_number" 
                        class="form-control" 
                        placeholder="e.g. 23CSE0101"
                        value="<?php echo e($rollNumber); ?>"
                        required
                    >
                    <?php if (isset($errors['roll_number'])): ?>
                        <span class="error-text">⚠ <?php echo e($errors['roll_number']); ?></span>
                    <?php else: ?>
                        <span class="input-hint">Must be unique institutional enrollment number.</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Form Row: Email & Course -->
            <div class="form-row">
                <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                    <label for="email" class="form-label">
                        Email Address <span class="required-star">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="e.g. student@college.edu"
                        value="<?php echo e($email); ?>"
                        required
                    >
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-text">⚠ <?php echo e($errors['email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($errors['course']) ? 'has-error' : ''; ?>">
                    <label for="course" class="form-label">
                        Engineering Course / Branch <span class="required-star">*</span>
                    </label>
                    <select id="course" name="course" class="form-control form-select" required>
                        <option value="" disabled <?php echo ($course === '') ? 'selected' : ''; ?>>-- Select Branch --</option>
                        <?php foreach (ALLOWED_COURSES as $c): ?>
                            <option value="<?php echo e($c); ?>" <?php echo ($course === $c) ? 'selected' : ''; ?>>
                                <?php echo e($c); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['course'])): ?>
                        <span class="error-text">⚠ <?php echo e($errors['course']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- File Upload Section (Optional during registration) -->
            <div class="form-group <?php echo isset($errors['document']) ? 'has-error' : ''; ?>">
                <label for="document" class="form-label">
                    Profile Photo or Verification Document <span class="optional-label">(Optional)</span>
                </label>
                <div class="file-upload-box">
                    <input 
                        type="file" 
                        id="document" 
                        name="document" 
                        class="file-control" 
                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                    >
                    <div class="file-upload-instructions">
                        <span class="upload-icon">📄</span>
                        <span class="upload-prompt">Choose a file to attach to this student profile</span>
                        <span class="upload-limits">Accepted: <strong>PDF, JPG, JPEG, PNG</strong> &bull; Max size: <strong>2 MB</strong></span>
                    </div>
                </div>
                <?php if (isset($errors['document'])): ?>
                    <span class="error-text">⚠ <?php echo e($errors['document']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Form Actions -->
            <div class="form-actions-bar">
                <button type="submit" class="btn btn-primary">
                    <span>💾 Save &amp; Register Student</span>
                </button>
                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
