<?php
/**
 * Shared Header Template (header.php)
 * Renders HTML head, navigation bar, and flash notifications.
 */
require_once __DIR__ . '/db.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$flash = getFlash();
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | Student Management' : 'Student Management System'; ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="app-layout">
        <!-- Top Navigation Bar -->
        <header class="navbar">
            <div class="nav-container">
                <a href="index.php" class="nav-brand">
                    <span class="brand-icon">🎓</span>
                    <span class="brand-text">
                        <strong>EduManage</strong>
                        <span class="brand-sub">Student Portal</span>
                    </span>
                </a>
                
                <nav class="nav-links">
                    <a href="index.php" class="nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
                        <span class="nav-icon">📊</span> Dashboard
                    </a>
                    <a href="add_student.php" class="btn btn-nav-primary">
                        <span class="btn-icon">➕</span> Add Student
                    </a>
                </nav>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="main-body">
            <div class="content-container">

                <!-- Global Flash Notification Banner -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo e($flash['type']); ?>" role="alert" id="flashAlert">
                        <div class="alert-icon">
                            <?php echo ($flash['type'] === 'success') ? '✅' : '⚠️'; ?>
                        </div>
                        <div class="alert-message">
                            <?php echo e($flash['message']); ?>
                        </div>
                        <button type="button" class="alert-close" onclick="document.getElementById('flashAlert').style.display='none';" aria-label="Close">
                            &times;
                        </button>
                    </div>
                <?php endif; ?>
