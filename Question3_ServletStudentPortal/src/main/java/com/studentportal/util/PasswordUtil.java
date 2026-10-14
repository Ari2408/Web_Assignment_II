package com.studentportal.util;

import java.nio.charset.StandardCharsets;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

/**
 * Cryptographic utility for password hashing and verification.
 * Provides a command-line main method to generate SHA-256 hashes for new portal users.
 */
public class PasswordUtil {

    /**
     * Computes a SHA-256 hexadecimal hash string for a plaintext password.
     *
     * @param password Plaintext password to hash
     * @return 64-character lowercase hexadecimal hash
     */
    public static String hashPassword(String password) {
        if (password == null) {
            throw new IllegalArgumentException("Password cannot be null");
        }
        try {
            MessageDigest digest = MessageDigest.getInstance("SHA-256");
            byte[] hashBytes = digest.digest(password.getBytes(StandardCharsets.UTF_8));
            StringBuilder hexString = new StringBuilder();
            for (byte b : hashBytes) {
                String hex = Integer.toHexString(0xff & b);
                if (hex.length() == 1) {
                    hexString.append('0');
                }
                hexString.append(hex);
            }
            return hexString.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException("SHA-256 algorithm not available in current JVM", e);
        }
    }

    /**
     * Verifies whether a plaintext password matches a stored SHA-256 hash.
     * Uses constant-time comparison to protect against timing attacks.
     *
     * @param plainPassword The password entered by the user
     * @param storedHash    The expected hash from MySQL
     * @return true if valid, false otherwise
     */
    public static boolean verifyPassword(String plainPassword, String storedHash) {
        if (plainPassword == null || storedHash == null) {
            return false;
        }
        String computedHash = hashPassword(plainPassword);
        return MessageDigest.isEqual(
                computedHash.getBytes(StandardCharsets.UTF_8),
                storedHash.getBytes(StandardCharsets.UTF_8)
        );
    }

    /**
     * Command-line tool to generate password hashes for database seeding.
     * Usage: java com.studentportal.util.PasswordUtil [password]
     */
    public static void main(String[] args) {
        String inputPassword = (args.length > 0) ? args[0] : "Admin@123";
        String hash = hashPassword(inputPassword);

        System.out.println("=================================================");
        System.out.println("Student Profile Portal - Password Hash Generator");
        System.out.println("=================================================");
        System.out.println("Plaintext Password : " + inputPassword);
        System.out.println("Algorithm          : SHA-256");
        System.out.println("Generated Hash     : " + hash);
        System.out.println("-------------------------------------------------");
        System.out.println("Sample SQL to insert/update user:");
        System.out.println("INSERT INTO users (username, password_hash, full_name, role) " +
                "VALUES ('myuser', '" + hash + "', 'User Name', 'ADMIN');");
        System.out.println("=================================================");
    }
}
