package com.studentportal.model;

import java.io.Serializable;
import java.sql.Timestamp;

/**
 * Model class representing uploaded document and profile image metadata.
 */
public class StudentFile implements Serializable {
    private static final long serialVersionUID = 1L;

    private int id;
    private int studentId;
    private String originalFilename;
    private String storedFilename;
    private String filePath;
    private String fileType;
    private int fileSize;
    private Timestamp uploadDate;

    public StudentFile() {
    }

    public StudentFile(int id, int studentId, String originalFilename, String storedFilename,
                       String filePath, String fileType, int fileSize, Timestamp uploadDate) {
        this.id = id;
        this.studentId = studentId;
        this.originalFilename = originalFilename;
        this.storedFilename = storedFilename;
        this.filePath = filePath;
        this.fileType = fileType;
        this.fileSize = fileSize;
        this.uploadDate = uploadDate;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public int getStudentId() {
        return studentId;
    }

    public void setStudentId(int studentId) {
        this.studentId = studentId;
    }

    public String getOriginalFilename() {
        return originalFilename;
    }

    public void setOriginalFilename(String originalFilename) {
        this.originalFilename = originalFilename;
    }

    public String getStoredFilename() {
        return storedFilename;
    }

    public void setStoredFilename(String storedFilename) {
        this.storedFilename = storedFilename;
    }

    public String getFilePath() {
        return filePath;
    }

    public void setFilePath(String filePath) {
        this.filePath = filePath;
    }

    public String getFileType() {
        return fileType;
    }

    public void setFileType(String fileType) {
        this.fileType = fileType;
    }

    public int getFileSize() {
        return fileSize;
    }

    public void setFileSize(int fileSize) {
        this.fileSize = fileSize;
    }

    public Timestamp getUploadDate() {
        return uploadDate;
    }

    public void setUploadDate(Timestamp uploadDate) {
        this.uploadDate = uploadDate;
    }

    /**
     * Checks if this file is an image (JPEG, PNG).
     */
    public boolean isImage() {
        return fileType != null && fileType.toLowerCase().startsWith("image/");
    }

    /**
     * Checks if this file is a PDF document.
     */
    public boolean isPdf() {
        return fileType != null && fileType.equalsIgnoreCase("application/pdf");
    }

    /**
     * Formats the raw byte size into KB or MB for display.
     */
    public String getFormattedFileSize() {
        if (fileSize < 1024) {
            return fileSize + " B";
        } else if (fileSize < 1048576) {
            return String.format("%.1f KB", fileSize / 1024.0);
        } else {
            return String.format("%.2f MB", fileSize / (1024.0 * 1024.0));
        }
    }

    /**
     * Extracts uppercase extension without the dot (e.g., "PDF", "JPG").
     */
    public String getExtension() {
        if (originalFilename != null && originalFilename.contains(".")) {
            return originalFilename.substring(originalFilename.lastIndexOf('.') + 1).toUpperCase();
        }
        return "FILE";
    }
}
