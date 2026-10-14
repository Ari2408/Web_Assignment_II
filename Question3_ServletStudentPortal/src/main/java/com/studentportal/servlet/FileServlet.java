package com.studentportal.servlet;

import com.studentportal.dao.DBConnection;
import com.studentportal.dao.StudentDAO;
import com.studentportal.model.StudentFile;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.OutputStream;
import java.nio.charset.StandardCharsets;

/**
 * Streams stored images and documents securely to the client.
 * Enforces database record verification, canonical path checking (anti-directory traversal),
 * and dynamic Content-Disposition header negotiation (inline vs attachment).
 */
@WebServlet(name = "FileServlet", urlPatterns = {"/file"})
public class FileServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    private StudentDAO studentDAO;

    @Override
    public void init() throws ServletException {
        this.studentDAO = new StudentDAO();
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        String idParam = request.getParameter("id");
        String modeParam = request.getParameter("mode"); // "inline" or "download"

        // 1. Validate File ID Parameter
        if (idParam == null || idParam.trim().isEmpty()) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Missing required file 'id' parameter.");
            return;
        }

        // Support default avatar fallback request
        if ("default".equalsIgnoreCase(idParam.trim())) {
            streamDefaultAvatarSvg(response);
            return;
        }

        int fileId;
        try {
            fileId = Integer.parseInt(idParam.trim());
        } catch (NumberFormatException e) {
            response.sendError(HttpServletResponse.SC_BAD_REQUEST, "Invalid file ID parameter: must be integer.");
            return;
        }

        // 2. Fetch File Metadata from Database
        StudentFile fileRecord = studentDAO.getFileById(fileId);
        if (fileRecord == null) {
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "File record #" + fileId + " not found in database.");
            return;
        }

        // 3. Resolve Stored File Securely from Configured Upload Directory
        String uploadBasePath = DBConnection.getUploadDir();
        File baseDir = new File(uploadBasePath).getCanonicalFile();

        // Target file using storedFilename from DB, NEVER from user request
        File targetFile = new File(baseDir, fileRecord.getStoredFilename()).getCanonicalFile();

        // 4. Directory Traversal Security Guard: Ensure target is within uploadBasePath
        if (!targetFile.getPath().startsWith(baseDir.getPath())) {
            System.err.println("[SECURITY ALERT] Directory traversal attempted: " + targetFile.getPath());
            response.sendError(HttpServletResponse.SC_FORBIDDEN, "Access to the requested file path is forbidden.");
            return;
        }

        // 5. Verify Target File Exists on Disk
        if (!targetFile.exists() || !targetFile.isFile()) {
            // If it's an image, stream default avatar instead of breaking the UI
            if (fileRecord.isImage()) {
                streamDefaultAvatarSvg(response);
                return;
            }
            response.sendError(HttpServletResponse.SC_NOT_FOUND, "Stored file could not be found on server disk.");
            return;
        }

        // 6. Configure Content-Type and Content-Disposition Headers
        String mimeType = fileRecord.getFileType();
        if (mimeType == null || mimeType.trim().isEmpty()) {
            mimeType = "application/octet-stream";
        }

        String dispositionType = "download".equalsIgnoreCase(modeParam) ? "attachment" : "inline";

        // Sanitize original filename for HTTP header
        String safeOriginalName = fileRecord.getOriginalFilename().replaceAll("[\r\n\"]", "");

        response.setContentType(mimeType);
        response.setContentLengthLong(targetFile.length());
        response.setHeader("Content-Disposition", dispositionType + "; filename=\"" + safeOriginalName + "\"");
        response.setHeader("X-Content-Type-Options", "nosniff");
        response.setHeader("Cache-Control", "private, max-age=3600");

        // 7. Stream File Bytes to Response Output Stream
        byte[] buffer = new byte[8192];
        try (FileInputStream fis = new FileInputStream(targetFile);
             OutputStream os = response.getOutputStream()) {

            int bytesRead;
            while ((bytesRead = fis.read(buffer)) != -1) {
                os.write(buffer, 0, bytesRead);
            }
            os.flush();
        }
    }

    /**
     * Streams a professional fallback SVG avatar when a profile picture is missing or unavailable on disk.
     */
    private void streamDefaultAvatarSvg(HttpServletResponse response) throws IOException {
        String svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"200\" height=\"200\" viewBox=\"0 0 200 200\">" +
                     "<rect width=\"200\" height=\"200\" fill=\"#dbeafe\"/>" +
                     "<circle cx=\"100\" cy=\"75\" r=\"40\" fill=\"#3b82f6\"/>" +
                     "<path d=\"M30 180 c0 -45 35 -70 70 -70 c35 0 70 25 70 70 Z\" fill=\"#1e40af\"/>" +
                     "</svg>";
        byte[] bytes = svg.getBytes(StandardCharsets.UTF_8);

        response.setContentType("image/svg+xml");
        response.setContentLengthLong(bytes.length);
        response.setHeader("Content-Disposition", "inline; filename=\"default-avatar.svg\"");
        response.setHeader("Cache-Control", "public, max-age=86400");

        try (OutputStream os = response.getOutputStream()) {
            os.write(bytes);
            os.flush();
        }
    }
}
