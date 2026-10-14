package com.studentportal.model;

import java.io.Serializable;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

/**
 * Model class representing a Student entity.
 */
public class Student implements Serializable {
    private static final long serialVersionUID = 1L;

    private int id;
    private String name;
    private String rollNumber;
    private String email;
    private String course;
    private Timestamp createdAt;

    // Associated file information
    private Integer profileImageFileId; // ID of the first/primary image file (if exists)
    private List<StudentFile> files = new ArrayList<>();

    public Student() {
    }

    public Student(int id, String name, String rollNumber, String email, String course, Timestamp createdAt) {
        this.id = id;
        this.name = name;
        this.rollNumber = rollNumber;
        this.email = email;
        this.course = course;
        this.createdAt = createdAt;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getRollNumber() {
        return rollNumber;
    }

    public void setRollNumber(String rollNumber) {
        this.rollNumber = rollNumber;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public String getCourse() {
        return course;
    }

    public void setCourse(String course) {
        this.course = course;
    }

    public Timestamp getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(Timestamp createdAt) {
        this.createdAt = createdAt;
    }

    public Integer getProfileImageFileId() {
        return profileImageFileId;
    }

    public void setProfileImageFileId(Integer profileImageFileId) {
        this.profileImageFileId = profileImageFileId;
    }

    public List<StudentFile> getFiles() {
        return files;
    }

    public void setFiles(List<StudentFile> files) {
        this.files = files;
    }

    /**
     * Returns true if this student has an uploaded profile image.
     */
    public boolean hasProfileImage() {
        return profileImageFileId != null && profileImageFileId > 0;
    }

    /**
     * Generates a 2-letter uppercase initials string for avatar display.
     */
    public String getInitials() {
        if (name == null || name.trim().isEmpty()) {
            return "ST";
        }
        String[] parts = name.trim().split("\\s+");
        StringBuilder sb = new StringBuilder();
        for (String part : parts) {
            if (!part.isEmpty()) {
                sb.append(Character.toUpperCase(part.charAt(0)));
                if (sb.length() >= 2) break;
            }
        }
        return sb.length() > 0 ? sb.toString() : "ST";
    }

    @Override
    public String toString() {
        return "Student{" +
                "id=" + id +
                ", name='" + name + '\'' +
                ", rollNumber='" + rollNumber + '\'' +
                ", email='" + email + '\'' +
                ", course='" + course + '\'' +
                '}';
    }
}
