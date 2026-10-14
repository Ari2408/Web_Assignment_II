<%@ page contentType="text/html;charset=UTF-8" language="java" isErrorPage="true" %>
<%@ taglib prefix="c" uri="jakarta.tags.core" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Encountered | Student Profile Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>

    <!-- Header -->
    <header class="navbar">
        <div class="nav-container">
            <a href="${pageContext.request.contextPath}/students" class="nav-brand">
                <span class="brand-icon">🎓</span>
                <span class="brand-text">
                    <strong>EduPortal</strong>
                    <span class="brand-tag">Student Profile Portal</span>
                </span>
            </a>
            <div class="nav-actions">
                <a href="${pageContext.request.contextPath}/students" class="user-badge" style="text-decoration:none;">
                    Directory
                </a>
            </div>
        </div>
    </header>

    <main class="main-wrapper">
        <div class="card" style="max-width: 600px; margin: 3rem auto; text-align: center;">
            <div style="font-size: 3.5rem; margin-bottom: 0.75rem;">⚠️</div>
            <h1 class="page-title" style="margin-bottom: 0.5rem; color: var(--color-error-600);">Request Error</h1>
            
            <p style="color: var(--color-slate-600); font-size: 1rem; margin-bottom: 1.5rem;">
                <c:choose>
                    <c:when test="${not empty errorMessage}">
                        <c:out value="${errorMessage}"/>
                    </c:when>
                    <c:when test="${not empty requestScope['jakarta.servlet.error.message']}">
                        <c:out value="${requestScope['jakarta.servlet.error.message']}"/>
                    </c:when>
                    <c:otherwise>
                        An unexpected error occurred while processing your request. Please verify the URL or student ID and try again.
                    </c:otherwise>
                </c:choose>
            </p>

            <a href="${pageContext.request.contextPath}/students" class="btn-primary-block" style="display: inline-block; text-decoration: none;">
                &larr; Return to Student Directory
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <p>&copy; 2026 Student Profile Portal &bull; Web Technology Assignment</p>
    </footer>

</body>
</html>
