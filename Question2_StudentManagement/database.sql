-- ============================================================================
-- Student Management System Database Script
-- Database Name: student_management
-- Author: Web Technology Lab Assignment
-- Compatible with: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+ (XAMPP default)
-- ============================================================================

-- Create database if not exists with UTF-8 support
CREATE DATABASE IF NOT EXISTS `student_management`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `student_management`;

-- ----------------------------------------------------------------------------
-- Table structure for: students
-- Stores primary student profile and academic enrollment details
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL COMMENT 'Full name of the student',
  `roll_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique institutional roll or enrollment number',
  `email` VARCHAR(150) NOT NULL COMMENT 'Institutional or personal email address',
  `course` VARCHAR(100) NOT NULL COMMENT 'Enrolled engineering branch/course',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of student registration',
  INDEX `idx_students_roll` (`roll_number`),
  INDEX `idx_students_course` (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Table structure for: student_files
-- Stores metadata of verification documents and profile pictures
-- Foreign key cascades deletion when student record is deleted
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_files` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL COMMENT 'Foreign key referencing students(id)',
  `original_filename` VARCHAR(255) NOT NULL COMMENT 'Sanitized original filename as uploaded by user',
  `stored_filename` VARCHAR(255) NOT NULL COMMENT 'Cryptographically secure unique filename stored on disk',
  `file_path` VARCHAR(255) NOT NULL COMMENT 'Relative path on server where file is stored',
  `file_type` VARCHAR(50) NOT NULL COMMENT 'MIME type of the file (e.g., application/pdf, image/jpeg)',
  `file_size` INT NOT NULL COMMENT 'File size in bytes',
  `upload_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when file was uploaded',
  INDEX `idx_student_files_student_id` (`student_id`),
  CONSTRAINT `fk_student_files_student`
    FOREIGN KEY (`student_id`)
    REFERENCES `students` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Sample Initial Data for Testing & Demonstration
-- ----------------------------------------------------------------------------
INSERT INTO `students` (`id`, `name`, `roll_number`, `email`, `course`, `created_at`) VALUES
(1, 'Aarav Sharma', '23CSE0101', 'aarav.sharma@example.edu', 'CSE', '2026-08-15 09:30:00'),
(2, 'Ananya Patel', '23ECE0204', 'ananya.patel@example.edu', 'ECE', '2026-08-16 11:15:00'),
(3, 'Rohan Verma', '23MECH0310', 'rohan.verma@example.edu', 'Mechanical', '2026-08-17 14:00:00'),
(4, 'Sneha Reddy', '23IT0415', 'sneha.reddy@example.edu', 'IT', '2026-08-18 10:45:00'),
(5, 'Vikram Singh', '23CIVIL0520', 'vikram.singh@example.edu', 'Civil', '2026-08-19 16:20:00'),
(6, 'Pooja Iyer', '23EEE0625', 'pooja.iyer@example.edu', 'EEE', '2026-08-20 13:10:00')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);
