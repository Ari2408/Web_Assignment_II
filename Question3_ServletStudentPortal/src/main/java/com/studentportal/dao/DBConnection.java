package com.studentportal.dao;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Manages JDBC database connectivity and environment configuration.
 * Reads credentials from db.properties with fallback defaults.
 */
public class DBConnection {

    private static String dbUrl = "jdbc:mysql://localhost:3306/student_management_servlet?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=UTC&characterEncoding=UTF-8";
    private static String dbUser = "root";
    private static String dbPassword = "";
    private static String dbDriver = "com.mysql.cj.jdbc.Driver";
    private static String uploadDir = "C:/student_portal_uploads/";

    static {
        // Load properties from classpath resource
        try (InputStream input = DBConnection.class.getClassLoader().getResourceAsStream("db.properties")) {
            if (input != null) {
                Properties prop = new Properties();
                prop.load(input);

                if (prop.getProperty("db.url") != null) dbUrl = prop.getProperty("db.url").trim();
                if (prop.getProperty("db.user") != null) dbUser = prop.getProperty("db.user").trim();
                if (prop.getProperty("db.password") != null) dbPassword = prop.getProperty("db.password").trim();
                if (prop.getProperty("db.driver") != null) dbDriver = prop.getProperty("db.driver").trim();
                if (prop.getProperty("upload.dir") != null) uploadDir = prop.getProperty("upload.dir").trim();
            }
        } catch (IOException e) {
            System.err.println("[WARN] Could not load db.properties, using fallback defaults: " + e.getMessage());
        }

        // Initialize JDBC Driver
        try {
            Class.forName(dbDriver);
        } catch (ClassNotFoundException e) {
            System.err.println("[ERROR] MySQL JDBC Driver not found in classpath: " + e.getMessage());
        }

        // Ensure upload directory exists
        try {
            File dir = new File(uploadDir);
            if (!dir.exists()) {
                dir.mkdirs();
            }
        } catch (Exception e) {
            System.err.println("[WARN] Could not verify/create upload directory: " + e.getMessage());
        }
    }

    /**
     * Establishes and returns a fresh JDBC Connection.
     *
     * @return java.sql.Connection
     * @throws SQLException If connection attempt fails
     */
    public static Connection getConnection() throws SQLException {
        return DriverManager.getConnection(dbUrl, dbUser, dbPassword);
    }

    /**
     * Returns the configured storage directory for uploaded files.
     *
     * @return Path string with trailing separator
     */
    public static String getUploadDir() {
        return uploadDir.endsWith("/") || uploadDir.endsWith("\\") ? uploadDir : uploadDir + File.separator;
    }
}
