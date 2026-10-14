package com.studentportal.servlet;

import com.studentportal.dao.StudentDAO;
import com.studentportal.model.Student;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.util.List;

/**
 * Controller servlet for querying and displaying student directory.
 */
@WebServlet(name = "StudentListServlet", urlPatterns = {"/students"})
public class StudentListServlet extends HttpServlet {
    private static final long serialVersionUID = 1L;

    private StudentDAO studentDAO;

    @Override
    public void init() throws ServletException {
        this.studentDAO = new StudentDAO();
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {

        try {
            // Retrieve all student records from MySQL using JDBC
            List<Student> students = studentDAO.getAllStudents();

            // Place student collection into request scope
            request.setAttribute("students", students);
            request.setAttribute("totalStudents", students.size());

            // Forward to students.jsp for view rendering
            request.getRequestDispatcher("/students.jsp").forward(request, response);

        } catch (Exception e) {
            System.err.println("[ERROR] StudentListServlet failed: " + e.getMessage());
            request.setAttribute("errorMessage", "Error retrieving student records from database: " + e.getMessage());
            request.getRequestDispatcher("/error.jsp").forward(request, response);
        }
    }
}
