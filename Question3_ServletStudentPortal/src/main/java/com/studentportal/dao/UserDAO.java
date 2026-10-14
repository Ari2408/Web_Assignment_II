package com.studentportal.dao;

import com.studentportal.model.User;
import com.studentportal.util.PasswordUtil;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Data Access Object for User authentication and query operations.
 */
public class UserDAO {

    /**
     * Authenticates a user using their username and plaintext password.
     * Validates against the SHA-256 hash in MySQL.
     *
     * @param username Provided username
     * @param password Provided plaintext password
     * @return User object if credentials are valid, null otherwise
     */
    public User authenticate(String username, String password) {
        if (username == null || password == null || username.trim().isEmpty() || password.isEmpty()) {
            return null;
        }

        String sql = "SELECT id, username, password_hash, full_name, role, created_at FROM users WHERE username = ? LIMIT 1";

        try (Connection conn = DBConnection.getConnection();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, username.trim());

            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    String storedHash = rs.getString("password_hash");

                    // Verify password against SHA-256 hash
                    if (PasswordUtil.verifyPassword(password, storedHash)) {
                        return new User(
                                rs.getInt("id"),
                                rs.getString("username"),
                                storedHash,
                                rs.getString("full_name"),
                                rs.getString("role"),
                                rs.getTimestamp("created_at")
                        );
                    }
                }
            }
        } catch (SQLException e) {
            System.err.println("[ERROR] Database error during authentication: " + e.getMessage());
        }
        return null;
    }
}
