# Student Management System with CRUD Operations and File Uploads

A complete, production-ready **Student Management System** developed with **PHP 8, MySQL (PDO), HTML5, CSS3, and JavaScript** for the Web Technology laboratory assignment. Built specifically for local execution via **XAMPP (Apache + MySQL)** without third-party frameworks.

---

## 1. Project Objectives

- **Full CRUD Functionality**: Implement Create, Read, Update, and Delete operations for student academic records using secure, prepared SQL statements.
- **Secure File Upload Handling**: Support multi-document and profile image uploads (PDF, JPG, JPEG, PNG) up to 2 MB with strict server-side MIME verification and unique filename hashing.
- **Relational Database Design**: Design normalized MySQL tables (`students` and `student_files`) with primary keys, unique constraints, and foreign key cascading deletion (`ON DELETE CASCADE`).
- **Web Application Security**: Protect against common vulnerabilities including SQL Injection (PDO prepared statements), Cross-Site Scripting (HTML escaping), Cross-Site Request Forgery (session-bound CSRF tokens), and arbitrary file execution (`.htaccess` protection).
- **Responsive Admin Interface**: Build a responsive admin dashboard with metric summary cards, search and course filter controls, custom modals, and image thumbnail galleries.

---

## 2. Technologies Used

- **Backend**: PHP 8+ (Vanilla PHP with PHP Data Objects — PDO)
- **Database**: MySQL 5.7+ / 8.0+ or MariaDB (via XAMPP)
- **Frontend**: Semantic HTML5, Custom CSS3 (Flexbox & CSS Grid), Vanilla JavaScript (ES6+)
- **Web Server**: Apache HTTP Server (via XAMPP)
- **Database Administration**: phpMyAdmin

---

## 3. Project Directory Structure

```text
Question2_StudentManagement/
├── index.php           # Admin dashboard: metrics cards, search/filter, students table
├── db.php              # PDO connection, session, CSRF, flash message, and helper functions
├── add_student.php     # Form & server validation to create a new student (+ optional upload)
├── edit_student.php    # Form & server validation to update an existing student's credentials
├── delete_student.php  # POST controller to delete student and unlink all physical files
├── view_student.php    # Detailed student profile, file upload box, and documents gallery
├── upload_file.php     # Dedicated POST handler for validating and storing student files
├── delete_file.php     # POST controller to remove individual files from disk and MySQL
├── download_file.php   # Secure file delivery handler with proper MIME headers & disposition
├── header.php          # Reusable header with navigation bar and flash message alerts
├── footer.php          # Reusable footer with deletion modal dialog and client scripts
├── style.css           # Complete responsive stylesheet for dashboard, forms, and cards
├── database.sql        # MySQL database schema script with sample student records
├── README.md           # Full setup instructions, architectural details, and documentation
└── uploads/            # Server storage directory for uploaded documents and images
    ├── .htaccess       # Security policy preventing script execution inside uploads/
    └── index.php       # Directory index blocker returning HTTP 403 Forbidden
```

---

## 4. Database Architecture (`database.sql`)

The system uses a relational database named `student_management` containing two interconnected tables:

```
+--------------------------------+          +----------------------------------+
|           students             |          |          student_files           |
+--------------------------------+          +----------------------------------+
| *id (INT PK, AUTO_INC)         | 1      * | *id (INT PK, AUTO_INC)           |
|  name (VARCHAR 100)            | <--------|  student_id (INT FK -> students) |
|  roll_number (VARCHAR 50, UNQ) |          |  original_filename (VARCHAR 255) |
|  email (VARCHAR 150)           |          |  stored_filename (VARCHAR 255)   |
|  course (VARCHAR 100)          |          |  file_path (VARCHAR 255)         |
|  created_at (TIMESTAMP)        |          |  file_type (VARCHAR 50)          |
+--------------------------------+          |  file_size (INT)                 |
                                            |  upload_date (TIMESTAMP)         |
                                            +----------------------------------+
```

### Table Definitions
1. **`students`**:
   - `id`: Primary key, auto-increment integer.
   - `name`: Full name of the candidate.
   - `roll_number`: Unique institutional enrollment number.
   - `email`: Institutional/personal email address.
   - `course`: Engineering branch (CSE, ECE, EEE, IT, Mechanical, Civil).
   - `created_at`: Registration timestamp.

2. **`student_files`**:
   - `id`: Primary key, auto-increment integer.
   - `student_id`: Foreign key referencing `students(id)` with `ON DELETE CASCADE`.
   - `original_filename`: Client's original filename (e.g. `transcript.pdf`), sanitized.
   - `stored_filename`: Cryptographically generated unique filename on disk (e.g. `9f8e7d6c5b4a3210.pdf`).
   - `file_path`: Relative disk path (`uploads/...`).
   - `file_type`: Validated MIME type (e.g. `application/pdf`, `image/jpeg`).
   - `file_size`: File size in bytes.
   - `upload_date`: Upload timestamp.

---

## 5. Explanation of PHP Pages and Architecture

| File Name | Role & Architecture Explanation |
| :--- | :--- |
| **`db.php`** | Core configuration and utility library. Establishes a PDO MySQL connection with exception handling, starts sessions, sets UTF-8 charset, defines system constants (`UPLOAD_DIR`, `MAX_FILE_SIZE`, `ALLOWED_EXTENSIONS`, `ALLOWED_MIME_TYPES`), and exposes security helpers: `e()` for XSS escaping, `generateCsrfToken()`, `verifyCsrfToken()`, `setFlash()`, and `formatBytes()`. |
| **`index.php`** | Admin Dashboard. Queries aggregate statistics (Total Students, Uploaded Documents, Active Branches, Total Storage), provides dynamic search by name/roll/email and course dropdown filter, and renders a data table with student avatars, file counts, and action buttons. |
| **`add_student.php`** | Student Registration Controller & Form. Sanitizes all inputs, validates roll number format and ensures uniqueness in MySQL. Supports optional document/photo upload during initial creation wrapped inside a **PDO database transaction** (`beginTransaction`, `commit`, `rollBack`). |
| **`edit_student.php`** | Student Update Controller & Form. Loads existing student details by ID. Verifies roll number uniqueness *excluding* the active student ID (`WHERE roll_number = :roll AND id != :id`). Updates record using prepared statements and sets flash notification. |
| **`delete_student.php`** | Secure Deletion Controller. Strictly accepts `POST` requests verified with a CSRF token. Queries all attached files for the student, safely removes them from disk using PHP `unlink()`, then deletes the student record from `students` (cascading file metadata cleanup). |
| **`view_student.php`** | Student Profile & File Management Interface. Renders comprehensive academic credentials, an inline file upload form, and a responsive media gallery showing image thumbnail previews and document cards with View, Download, and Delete actions. |
| **`upload_file.php`** | Dedicated File Upload Handler. Accepts `POST` requests, verifies CSRF token, inspects true MIME type with `finfo_file()`, checks file size (≤ 2 MB), generates a random 16-byte hex filename (`bin2hex(random_bytes(16))`), moves the file to `uploads/`, and logs metadata in MySQL. |
| **`delete_file.php`** | Individual File Deletion Handler. Accepts `POST` requests with CSRF token, finds the file on server storage, unlinks it from disk, and removes the corresponding row from `student_files`. |
| **`download_file.php`** | Secure File Delivery Gateway. Serves files through HTTP headers (`Content-Type`, `Content-Disposition`, `X-Content-Type-Options: nosniff`) for either inline display or attachment download, preventing direct access and execution of stored files. |
| **`header.php` / `footer.php`** | Shared layout partials providing the top navigation bar, flash message banners, accessible deletion confirmation modal dialog, and mobile-friendly styling. |

---

## 6. Security & Hardening Implementation

1. **SQL Injection Prevention**:
   - Every single database interaction across all files utilizes PDO prepared statements with parameterized inputs (`:param` / `?`).
   - Native emulated prepares are disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
2. **Cross-Site Scripting (XSS) Prevention**:
   - All user-supplied data displayed in HTML is escaped using the global helper `e($string)`, which implements `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')`.
3. **Cross-Site Request Forgery (CSRF) Prevention**:
   - All state-changing forms (`add_student.php`, `edit_student.php`, `delete_student.php`, `upload_file.php`, `delete_file.php`) inject a hidden 64-character hexadecimal CSRF token generated via `random_bytes(32)`. Submissions are verified with `hash_equals()`.
4. **POST-Only Destructive Actions**:
   - Deletion of students or files cannot be triggered via simple GET links. They strictly enforce `$_SERVER['REQUEST_METHOD'] === 'POST'`.
5. **Secure File Upload Handling**:
   - **True MIME Inspection**: Uses PHP's `finfo_file(FILEINFO_MIME_TYPE)` to inspect file headers rather than trusting client-provided `$_FILES['file']['type']`.
   - **Extension Whitelist**: Only `.pdf`, `.jpg`, `.jpeg`, and `.png` are accepted.
   - **Size Restriction**: Files exceeding 2 MB are rejected before moving.
   - **Non-Colliding Stored Filenames**: Files are saved with cryptographically random names (`bin2hex(random_bytes(16)) . '.' . $ext`). Original filenames are retained purely as metadata.
   - **Execution Prevention**: An `.htaccess` file inside `uploads/` disables script execution engines (`SetHandler default-handler`, `RemoveHandler .php .phtml .php8`), and `uploads/index.php` blocks directory listing.

---

## 7. Step-by-Step Setup Instructions for XAMPP

### Step 1: Start Apache and MySQL in XAMPP
1. Launch the **XAMPP Control Panel** on your computer.
2. Click the **Start** button next to **Apache**. Verify that the module turns green with active ports (e.g., `80, 443`).
3. Click the **Start** button next to **MySQL**. Verify that the module turns green with active ports (e.g., `3306`).

### Step 2: Place the Project in the XAMPP `htdocs` Directory
Copy or move the `Question2_StudentManagement` folder into your XAMPP web root directory:
```text
C:\xampp\htdocs\Question2_StudentManagement\
```

Ensure the folder structure inside `htdocs` matches:
```text
C:\xampp\htdocs\Question2_StudentManagement\
  ├── index.php
  ├── db.php
  ├── add_student.php
  ├── edit_student.php
  ├── delete_student.php
  ├── view_student.php
  ├── upload_file.php
  ├── delete_file.php
  ├── download_file.php
  ├── header.php
  ├── footer.php
  ├── style.css
  ├── database.sql
  ├── README.md
  └── uploads/
```

### Step 3: Import `database.sql` Using phpMyAdmin
1. Open your browser and navigate to:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab in the top navigation bar.
3. Click **Choose File** (or "Browse") and select:
   `C:\xampp\htdocs\Question2_StudentManagement\database.sql`
4. Leave character set as `utf-8` and format as `SQL`.
5. Click **Import** (or **Go**) at the bottom.
6. Verify that the database `student_management` has been created with two tables: `students` and `student_files`, populated with 6 sample student records.

### Step 4: Configure Database Credentials in `db.php`
Open `db.php` in any text editor. The default XAMPP settings are already pre-configured:
```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', ''); // Blank password by default in XAMPP
```
*(If your MySQL server uses a custom password or port, update `DB_PASS` or `DB_PORT` accordingly).*

### Step 5: Verify the `uploads/` Directory
The `uploads/` directory has been created in the project with `.htaccess` and `index.php` protection. On Windows/XAMPP, write permissions are granted by default. If on Linux or macOS, ensure write permissions:
```bash
chmod 777 uploads/
```

### Step 6: Open the Project in Your Browser
Open your web browser and navigate to:
```text
http://localhost/Question2_StudentManagement/
```
The Admin Dashboard should load immediately, displaying metric summary cards, search controls, and the sample student records table.

---

## 8. Verification and Testing Walkthrough

Follow these sequential steps to test all CRUD and file upload features:

| Test Step | Action | Expected Result |
| :--- | :--- | :--- |
| **1. View Dashboard** | Navigate to `http://localhost/Question2_StudentManagement/` | Displays 6 initial students, metrics (Total Students: 6, Uploaded Documents: 0, Active Branches: 6). |
| **2. Search & Filter** | Enter "Aarav" in search or select "CSE" in branch filter | Table filters in real-time. Click "Clear" to restore all records. |
| **3. Create Student (Valid)** | Click **Add Student**. Enter: Name: "Meera Nair", Roll: "23CSE0199", Email: "meera.nair@example.edu", Course: "CSE". | Redirects to Meera's profile with green flash notification: *"Student 'Meera Nair' registered successfully!"*. |
| **4. Duplicate Roll Test** | Click **Add Student**. Attempt to enter existing roll: "23CSE0199". | Submission is blocked. Displays inline error: *"This roll number is already registered for another student."* |
| **5. Edit Student** | On Meera's profile, click **Edit Profile**. Change course to "IT". Click **Update Student**. | Updates record. Profile now reflects "IT Engineering" with success message. |
| **6. Upload Valid Image** | In the upload box on Meera's profile, choose a `.jpg` or `.png` photo (< 2 MB). Click **Upload File**. | File is uploaded to `uploads/`. An image card appears with a **thumbnail preview**, file size, and upload timestamp. |
| **7. Upload Valid PDF** | Choose a `.pdf` document (< 2 MB). Click **Upload File**. | Document card appears with a red **📄 PDF** badge, file size, and "View" / "Download" buttons. |
| **8. Verify in MySQL** | Open phpMyAdmin -> `student_management` -> `student_files` table | Verify 2 rows are linked to Meera's `student_id` with `original_filename`, secure `stored_filename`, `file_type`, and `file_size`. |
| **9. Test File Download** | Click **Download** on the PDF card | File is downloaded to your device with its original human-readable filename. |
| **10. Disallowed File Test** | Attempt to upload a `.txt`, `.exe`, or file > 2 MB | Upload is rejected with error: *"Disallowed file extension"* or *"File size exceeds 2 MB limit"*. |
| **11. Delete Single File** | Click the red 🗑️ icon on one of the uploaded file cards | Confirmation prompt appears. After clicking OK, file is unlinked from disk and removed from MySQL. |
| **12. Delete Student Record** | Click **Delete Student** on Meera's profile. Confirm in modal. | Meera's record is deleted from `students`, her remaining files are deleted from `uploads/`, and dashboard shows updated counts. |

---

## 9. Conclusion

This Student Management System successfully demonstrates the core principles of web application engineering: robust server-side architecture using PHP 8 Data Objects (PDO), relational database normalization with MySQL, and responsive frontend design with semantic HTML5 and CSS3. By implementing comprehensive input validation, foreign key cascade constraints, CSRF token validation, and multi-layer file upload verification, the application provides an enterprise-grade administrative platform that adheres to modern web security standards without relying on external frameworks.
