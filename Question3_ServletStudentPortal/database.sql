-- ============================================================================
-- Student Management Servlet Database Script
-- Database Name: student_management_servlet
-- Technology: Java Servlets, JSP, JDBC, MySQL, Apache Tomcat 10.1+
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `student_management_servlet`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `student_management_servlet`;

-- ----------------------------------------------------------------------------
-- Table: users (Portal Authentication)
-- Passwords stored as SHA-256 cryptographic hashes
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(64) NOT NULL COMMENT 'SHA-256 hex digest (64 chars)',
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'ADMIN',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: students (Student Profile Directory)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `roll_number` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL,
  `course` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_students_roll` (`roll_number`),
  INDEX `idx_students_course` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table: student_files (Images, Verification Documents & Files)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_files` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `stored_filename` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_type` VARCHAR(50) NOT NULL,
  `file_size` INT NOT NULL,
  `upload_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_student_files_student_id` (`student_id`),
  CONSTRAINT `fk_student_files_student`
    FOREIGN KEY (`student_id`)
    REFERENCES `students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Sample Users (Passwords hashed via SHA-256)
-- 1. admin    / Admin@123    -> e86f78a8a3caf0b60d8e74e5942aa6d86dc150cd3c03338aef25b7d2d7e3acc7
-- 2. faculty  / Faculty@2026  -> e319b9b384755195cdbe9df503b0c96691902663ecad4243575b436101360b36
-- ----------------------------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`) VALUES
(1, 'admin', 'e86f78a8a3caf0b60d8e74e5942aa6d86dc150cd3c03338aef25b7d2d7e3acc7', 'System Administrator', 'ADMIN'),
(2, 'faculty', 'e319b9b384755195cdbe9df503b0c96691902663ecad4243575b436101360b36', 'Prof. Ramesh Kulkarni', 'FACULTY')
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`);

-- ----------------------------------------------------------------------------
-- Sample Students
-- ----------------------------------------------------------------------------
INSERT INTO `students` (`id`, `name`, `roll_number`, `email`, `course`, `created_at`) VALUES
(1, 'Aarav Sharma', '23CSE0101', 'aarav.sharma@college.edu', 'CSE', '2026-08-15 09:30:00'),
(2, 'Ananya Patel', '23ECE0204', 'ananya.patel@college.edu', 'ECE', '2026-08-16 11:15:00'),
(3, 'Rohan Verma', '23MECH0310', 'rohan.verma@college.edu', 'Mechanical', '2026-08-17 14:00:00'),
(4, 'Sneha Reddy', '23IT0415', 'sneha.reddy@college.edu', 'IT', '2026-08-18 10:45:00'),
(5, 'Vikram Singh', '23CIVIL0520', 'vikram.singh@college.edu', 'Civil', '2026-08-19 16:20:00'),
(6, 'Pooja Iyer', '23EEE0625', 'pooja.iyer@college.edu', 'EEE', '2026-08-20 13:10:00')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- ----------------------------------------------------------------------------
-- Sample Student Files Metadata
-- ----------------------------------------------------------------------------
INSERT INTO `student_files` (`id`, `student_id`, `original_filename`, `stored_filename`, `file_path`, `file_type`, `file_size`, `upload_date`) VALUES
(1, 1, 'aarav_profile.jpg', 'aarav_profile.jpg', 'uploads/aarav_profile.jpg', 'image/jpeg', 245760, '2026-08-15 09:35:00'),
(2, 1, 'admission_form.pdf', 'admission_form.pdf', 'uploads/admission_form.pdf', 'application/pdf', 524288, '2026-08-15 09:36:00'),
(3, 2, 'ananya_photo.png', 'ananya_photo.png', 'uploads/ananya_photo.png', 'image/png', 184320, '2026-08-16 11:20:00'),
(4, 3, 'transcript_sem1.pdf', 'transcript_sem1.pdf', 'uploads/transcript_sem1.pdf', 'application/pdf', 412000, '2026-08-17 14:05:00'),
(5, 4, 'sneha_avatar.jpg', 'sneha_avatar.jpg', 'uploads/sneha_avatar.jpg', 'image/jpeg', 204800, '2026-08-18 10:50:00')
ON DUPLICATE KEY UPDATE `original_filename`=VALUES(`original_filename`);
