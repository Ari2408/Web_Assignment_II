<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="jakarta.tags.core" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Directory | Student Profile Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>

    <!-- Top Navigation Header -->
    <header class="navbar">
        <div class="nav-container">
            <a href="${pageContext.request.contextPath}/students" class="nav-brand">
                <span class="brand-icon">🎓</span>
                <span class="brand-text">
                    <strong>EduPortal</strong>
                    <span class="brand-tag">Servlet &bull; JDBC &bull; JSP</span>
                </span>
            </a>

            <div class="nav-actions">
                <div class="user-badge">
                    <span>👤</span>
                    <span><c:out value="${sessionScope.loggedUser.fullName}"/></span>
                </div>
                <a href="${pageContext.request.contextPath}/logout" class="btn-logout">
                    <span>🚪</span> Sign Out
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="main-wrapper">
        <div class="breadcrumb-bar">
            <span>Student Directory</span>
        </div>

        <div class="card">
            <div class="card-header-flex">
                <div>
                    <h1 class="page-title">Registered Students Directory</h1>
                    <p class="page-subtitle">Showing active student profiles fetched dynamically from MySQL via JDBC</p>
                </div>
                <div>
                    <span class="course-badge">
                        Total: <strong><c:out value="${totalStudents}"/></strong> Students
                    </span>
                </div>
            </div>

            <!-- Student Profile Cards Grid -->
            <c:choose>
                <c:when test="${empty students}">
                    <div class="empty-state-box">
                        <span class="empty-state-icon">📂</span>
                        <h3>No Student Records Found</h3>
                        <p>There are currently no students registered in the MySQL database.</p>
                    </div>
                </c:when>

                <c:otherwise>
                    <div class="students-grid">
                        <c:forEach var="student" items="${students}">
                            <div class="student-card">
                                <div class="student-card-top">
                                    <div class="avatar-wrapper">
                                        <c:choose>
                                            <c:when test="${student.hasProfileImage()}">
                                                <img 
                                                    src="${pageContext.request.contextPath}/file?id=${student.profileImageFileId}" 
                                                    alt="<c:out value='${student.name}'/>" 
                                                    class="avatar-img"
                                                    loading="lazy"
                                                >
                                            </c:when>
                                            <c:otherwise>
                                                <div class="avatar-initials" title="No profile image uploaded">
                                                    <c:out value="${student.initials}"/>
                                                </div>
                                            </c:otherwise>
                                        </c:choose>
                                    </div>

                                    <div class="student-header-meta">
                                        <h2 class="student-name"><c:out value="${student.name}"/></h2>
                                        <span class="student-roll"><c:out value="${student.rollNumber}"/></span>
                                    </div>
                                </div>

                                <div class="student-card-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Roll Number:</span>
                                        <span class="detail-value"><c:out value="${student.rollNumber}"/></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value"><c:out value="${student.email}"/></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Branch:</span>
                                        <span class="course-badge"><c:out value="${student.course}"/></span>
                                    </div>
                                </div>

                                <div class="student-card-footer">
                                    <a href="${pageContext.request.contextPath}/student-profile?id=${student.id}" class="btn-view-profile">
                                        View Full Profile &rarr;
                                    </a>
                                </div>
                            </div>
                        </c:forEach>
                    </div>
                </c:otherwise>
            </c:choose>

        </div>
    </main>

    <!-- Application Footer -->
    <footer class="app-footer">
        <p>&copy; 2026 Student Profile Portal &bull; Java Servlets 6.0, JSP 3.1 &amp; JDBC Laboratory Assignment</p>
    </footer>

</body>
</html>
