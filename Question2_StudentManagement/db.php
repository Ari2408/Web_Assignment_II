<?php
/**
 * ============================================================================
 * Database Connection & Global Helpers (db.php)
 * Student Management System - Web Technology Laboratory Assignment
 * ============================================================================
 * Provides secure PDO database connection, session initialization, CSRF token
 * handling, flash messaging, input sanitization, and configuration constants.
 */

// Strict error reporting for clean development; disabled from showing credentials in production
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start PHP session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------------------------------------------
// 1. Database Configuration Constants (XAMPP Defaults)
// ----------------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ----------------------------------------------------------------------------
// 2. Application & File Upload Constants
// ----------------------------------------------------------------------------
// Absolute path to the uploads directory
define('UPLOAD_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);

// Maximum allowed file size: 2 Megabytes (2 * 1024 * 1024 bytes)
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

// Allowed document and image extensions
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);

// Allowed MIME types verified via finfo_file
define('ALLOWED_MIME_TYPES', [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
]);

// Allowed academic branches / courses
define('ALLOWED_COURSES', ['CSE', 'ECE', 'EEE', 'IT', 'Mechanical', 'Civil']);

// ----------------------------------------------------------------------------
// 3. Establish PDO Database Connection
// ----------------------------------------------------------------------------
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // Graceful error display without exposing database credentials
    $errorMessage = "Database Connection Failed: Unable to connect to MySQL server.";
    $detailedHint = "Please ensure XAMPP MySQL service is running and 'database.sql' has been imported.";
    
    // Output user-friendly error page
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body class="error-page-body">
        <div class="error-dialog-card">
            <div class="error-dialog-icon">⚠️</div>
            <h2>Database Connection Error</h2>
            <p><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="error-hint-box">
                <strong>Troubleshooting Steps:</strong>
                <ul>
                    <li>Open <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> next to MySQL and Apache.</li>
                    <li>Verify the database <code>student_management</code> exists by opening <a href="http://localhost/phpmyadmin" target="_blank">phpMyAdmin</a>.</li>
                    <li>Import <code>database.sql</code> if you haven't already.</li>
                    <li>Check credentials in <code>db.php</code> (Default XAMPP user: <code>root</code>, password: <em>blank</em>).</li>
                </ul>
            </div>
            <p class="error-subtext">Original system error: <?php echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// ----------------------------------------------------------------------------
// 4. Security & Sanitization Helper Functions
// ----------------------------------------------------------------------------

/**
 * Escapes output strings to protect against Cross-Site Scripting (XSS).
 *
 * @param string|null $string The raw string to escape.
 * @return string The sanitized, HTML-safe string.
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generates or retrieves the active CSRF protection token.
 *
 * @return string 64-character hexadecimal CSRF token.
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token against the session token.
 *
 * @param string|null $token The token submitted via POST.
 * @return bool True if valid, false otherwise.
 */
function verifyCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sets a flash message to be displayed on the next page view.
 *
 * @param string $type The message type: 'success', 'error', 'warning', or 'info'.
 * @param string $message The notification text.
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieves and clears the current flash message from session.
 *
 * @return array|null Array containing 'type' and 'message', or null if none.
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Formats bytes into a human-readable string (KB or MB).
 *
 * @param int $bytes File size in bytes.
 * @return string Formatted string (e.g. "1.45 MB").
 */
function formatBytes(int $bytes): string {
    if ($bytes < 1024) {
        return $bytes . ' B';
    } elseif ($bytes < 1048576) {
        return round($bytes / 1024, 1) . ' KB';
    } else {
        return round($bytes / 1048576, 2) . ' MB';
    }
}
