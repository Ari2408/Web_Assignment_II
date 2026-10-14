<?php
/**
 * Edit Student Page (edit_student.php)
 * Handles updating student details with server-side validation and uniqueness verification.
 */
require_once __DIR__ . '/db.php';

$pageTitle = 'Edit Student';

// 1. Retrieve & Validate Student ID from GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    setFlash('error', 'Invalid student ID specified.');
    header('Location: index.php');
    exit();
}

// 2. Fetch Existing Student Data
try {
    $stmtFetch = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
    $stmtFetch->execute([':id' => $id]);
    $student = $stmtFetch->fetch();

    if (!$student) {
        setFlash('error', 'Student record not found.');
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    setFlash('error', 'Database error: ' . $e->getMessage());
    header('Location: index.php');
    exit();
}

$errors = [];
$name = $student['name'];
$rollNumber = $student['roll_number'];
$email = $student['email'];
$course = $student['course'];

// 3. Handle Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid or expired session security token. Please refresh the page and try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $rollNumber = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    // Validation: Name
    if ($name === '') {
        $errors['name'] = 'Full name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors['name'] = 'Name must be between 2 and 100 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
        $errors['name'] = 'Name can only contain alphabetic letters, spaces, hyphens, and apostrophes.';
    }

    // Validation: Roll Number & Uniqueness excluding current student
    if ($rollNumber === '') {
        $errors['roll_number'] = 'Roll number is required.';
    } elseif (!preg_match('/^[A-Za-z0-9\/-]{2,50}$/', $rollNumber)) {
        $errors['roll_number'] = 'Roll number must be between 2 and 50 alphanumeric characters.';
    } else {
        try {
            $stmtCheckRoll = $pdo->prepare("SELECT id FROM students WHERE roll_number = :roll AND id != :id LIMIT 1");
            $stmtCheckRoll->execute([
                ':roll' => $rollNumber,
                ':id'   => $id
            ]);
            if ($stmtCheckRoll->fetch()) {
                $errors['roll_number'] = 'This roll number is already assigned to another student.';
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Error verifying roll number uniqueness: ' . $e->getMessage();
        }
    }

    // Validation: Email
    if ($email === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    // Validation: Course
    if ($course === '' || !in_array($course, ALLOWED_COURSES, true)) {
        $errors['course'] = 'Please select a valid course from the list.';
    }

    // Update Record if No Validation Errors
    if (empty($errors)) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE students 
                SET name = :name, roll_number = :roll_number, email = :email, course = :course
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':name'        => $name,
                ':roll_number' => $rollNumber,
                ':email'       => $email,
                ':course'      => $course,
                ':id'          => $id
            ]);

            setFlash('success', 'Student details for "' . $name . '" updated successfully!');
            header('Location: view_student.php?id=' . $id);
            exit();

        } catch (PDOException $e) {
            $errors['database'] = 'Failed to update student: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="breadcrumb-nav">
    <a href="index.php">Dashboard</a> &rsaquo; 
    <a href="view_student.php?id=<?php echo e($student['id']); ?>"><?php echo e($student['name']); ?></a> &rsaquo; 
    <span>Edit Details</span>
</div>

<div class="form-container">
    <div class="card form-card">
        <div class="card-header-bar">
            <div class="header-titles">
                <h2 class="section-title">Edit Student Profile</h2>
                <p class="section-subtitle">Modify academic credentials for <strong><?php echo e($student['name']); ?></strong> (ID: #<?php echo e($student['id']); ?>).</p>
            </div>
            <div class="header-tools">
                <a href="view_student.php?id=<?php echo e($student['id']); ?>" class="btn btn-secondary btn-sm">
                    &larr; View Profile
                </a>
            </div>
        </div>

        <?php if (!empty($errors['csrf']) || !empty($errors['database'])): ?>
            <div class="alert alert-error">
                <?php echo e($errors['csrf'] ?? $errors['database']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_student.php?id=<?php echo e($student['id']); ?>" class="app-form" novalidate>
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
                        value="<?php echo e($rollNumber); ?>"
                        required
                    >
                    <?php if (isset($errors['roll_number'])): ?>
                        <span class="error-text">⚠ <?php echo e($errors['roll_number']); ?></span>
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

            <!-- Form Actions -->
            <div class="form-actions-bar">
                <button type="submit" class="btn btn-primary">
                    <span>💾 Update Student</span>
                </button>
                <a href="view_student.php?id=<?php echo e($student['id']); ?>" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
