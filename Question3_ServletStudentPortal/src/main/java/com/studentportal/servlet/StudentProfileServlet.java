package com.studentportal.servlet;

import com.studentportal.dao.StudentDAO;
import com.studentportal.model.Student;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;

/**
 * Controller servlet for fetching and displaying a specific student's detailed profile and files.
 */
@WebServlet(name = "StudentProfileServlet", urlPatterns = {"/student-profile"})
public class StudentProfileServlet extends HttpServlet {
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

        // Validate that an ID parameter was supplied
        if (idParam == null || idParam.trim().isEmpty()) {
            request.setAttribute("errorMessage", "Missing student ID parameter. Please select a student from the directory.");
            request.getRequestDispatcher("/error.jsp").forward(request, response);
            return;
        }

        int studentId;
        try {
            studentId = Integer.parseInt(idParam.trim());
        } catch (NumberFormatException e) {
            request.setAttribute("errorMessage", "Invalid student ID format: \"" + idParam + "\". An integer ID is required.");
            request.getRequestDispatcher("/error.jsp").forward(request, response);
            return;
        }

        try {
            // Retrieve student record and attached files from MySQL via JDBC
            Student student = studentDAO.getStudentById(studentId);

            if (student == null) {
                request.setAttribute("errorMessage", "Student with ID #" + studentId + " was not found in the database.");
                request.getRequestDispatcher("/error.jsp").forward(request, response);
                return;
            }

            // Expose student to JSP scope
            request.setAttribute("student", student);
            request.setAttribute("files", student.getFiles());

            // Forward to student profile view
            request.getRequestDispatcher("/student-profile.jsp").forward(request, response);

        } catch (Exception e) {
            System.err.println("[ERROR] StudentProfileServlet error: " + e.getMessage());
            request.setAttribute("errorMessage", "Database retrieval failure: " + e.getMessage());
            request.getRequestDispatcher("/error.jsp").forward(request, response);
        }
    }
}
