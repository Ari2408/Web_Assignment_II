package com.studentportal.dao;

import com.studentportal.model.Student;
import com.studentportal.model.StudentFile;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

/**
 * Data Access Object for Student and StudentFile database operations.
 * Implements prepared statements for all SQL operations.
 */
public class StudentDAO {

    /**
     * Retrieves all student records, along with their primary profile image file ID if uploaded.
     *
     * @return List of Student entities
     */
    public List<Student> getAllStudents() {
        List<Student> students = new ArrayList<>();

        String sql = "SELECT s.id, s.name, s.roll_number, s.email, s.course, s.created_at, " +
                     "       (SELECT f.id FROM student_files f " +
                     "        WHERE f.student_id = s.id AND f.file_type LIKE 'image/%' " +
                     "        ORDER BY f.id ASC LIMIT 1) AS profile_image_id " +
                     "FROM students s " +
                     "ORDER BY s.id ASC";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {

            while (rs.next()) {
                Student student = new Student(
                        rs.getInt("id"),
                        rs.getString("name"),
                        rs.getString("roll_number"),
                        rs.getString("email"),
                        rs.getString("course"),
                        rs.getTimestamp("created_at")
                );

                int imgId = rs.getInt("profile_image_id");
                if (!rs.wasNull() && imgId > 0) {
                    student.setProfileImageFileId(imgId);
                }

                students.add(student);
            }
        } catch (SQLException e) {
            System.err.println("[ERROR] Failed to fetch students: " + e.getMessage());
        }

        return students;
    }

    /**
     * Retrieves a single student record by their primary key, including their attached documents and photos.
     *
     * @param studentId Primary key ID of the student
     * @return Student object if found, null otherwise
     */
    public Student getStudentById(int studentId) {
        String sql = "SELECT s.id, s.name, s.roll_number, s.email, s.course, s.created_at, " +
                     "       (SELECT f.id FROM student_files f " +
                     "        WHERE f.student_id = s.id AND f.file_type LIKE 'image/%' " +
                     "        ORDER BY f.id ASC LIMIT 1) AS profile_image_id " +
                     "FROM students s " +
                     "WHERE s.id = ? LIMIT 1";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, studentId);

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    Student student = new Student(
                            rs.getInt("id"),
                            rs.getString("name"),
                            rs.getString("roll_number"),
                            rs.getString("email"),
                            rs.getString("course"),
                            rs.getTimestamp("created_at")
                    );

                    int imgId = rs.getInt("profile_image_id");
                    if (!rs.wasNull() && imgId > 0) {
                        student.setProfileImageFileId(imgId);
                    }

                    // Attach all associated documents and media
                    student.setFiles(getFilesByStudentId(studentId));

                    return student;
                }
            }
        } catch (SQLException e) {
            System.err.println("[ERROR] Failed to fetch student by ID " + studentId + ": " + e.getMessage());
        }

        return null;
    }

    /**
     * Retrieves all file metadata records associated with a given student ID.
     *
     * @param studentId Foreign key student ID
     * @return List of StudentFile entities
     */
    public List<StudentFile> getFilesByStudentId(int studentId) {
        List<StudentFile> files = new ArrayList<>();

        String sql = "SELECT id, student_id, original_filename, stored_filename, file_path, " +
                     "       file_type, file_size, upload_date " +
                     "FROM student_files " +
                     "WHERE student_id = ? " +
                     "ORDER BY id DESC";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, studentId);

            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    StudentFile file = new StudentFile(
                            rs.getInt("id"),
                            rs.getInt("student_id"),
                            rs.getString("original_filename"),
                            rs.getString("stored_filename"),
                            rs.getString("file_path"),
                            rs.getString("file_type"),
                            rs.getInt("file_size"),
                            rs.getTimestamp("upload_date")
                    );
                    files.add(file);
                }
            }
        } catch (SQLException e) {
            System.err.println("[ERROR] Failed to fetch files for student ID " + studentId + ": " + e.getMessage());
        }

        return files;
    }

    /**
     * Retrieves a single file record by its file ID.
     *
     * @param fileId Primary key ID of the file
     * @return StudentFile object if found, null otherwise
     */
    public StudentFile getFileById(int fileId) {
        String sql = "SELECT id, student_id, original_filename, stored_filename, file_path, " +
                     "       file_type, file_size, upload_date " +
                     "FROM student_files " +
                     "WHERE id = ? LIMIT 1";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, fileId);

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    return new StudentFile(
                            rs.getInt("id"),
                            rs.getInt("student_id"),
                            rs.getString("original_filename"),
                            rs.getString("stored_filename"),
                            rs.getString("file_path"),
                            rs.getString("file_type"),
                            rs.getInt("file_size"),
                            rs.getTimestamp("upload_date")
                    );
                }
            }
        } catch (SQLException e) {
            System.err.println("[ERROR] Failed to fetch file by ID " + fileId + ": " + e.getMessage());
        }

        return null;
    }
}
