<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="jakarta.tags.core" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><c:out value="${student.name}"/> - Profile | Student Profile Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body>

    <!-- Navigation Header -->
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

    <!-- Main Content Area -->
    <main class="main-wrapper">
        <div class="breadcrumb-bar">
            <a href="${pageContext.request.contextPath}/students">&larr; Student Directory</a> &rsaquo; 
            <span><c:out value="${student.name}"/></span>
        </div>

        <div class="profile-grid-layout">
            
            <!-- Left Sidebar Card: Personal Details & Avatar -->
            <div class="profile-sidebar-col">
                <div class="card profile-card-left">
                    <div class="profile-hero-avatar">
                        <c:choose>
                            <c:when test="${student.hasProfileImage()}">
                                <img 
                                    src="${pageContext.request.contextPath}/file?id=${student.profileImageFileId}" 
                                    alt="<c:out value='${student.name}'/>" 
                                    class="avatar-img"
                                >
                            </c:when>
                            <c:otherwise>
                                <div class="avatar-initials" title="No profile image uploaded">
                                    <c:out value="${student.initials}"/>
                                </div>
                            </c:otherwise>
                        </c:choose>
                    </div>

                    <h1 class="profile-fullname"><c:out value="${student.name}"/></h1>
                    <span class="profile-subroll"><c:out value="${student.rollNumber}"/></span>
                    <span class="course-badge"><c:out value="${student.course}"/> Engineering</span>

                    <div class="profile-fields-list">
                        <div class="profile-field-item">
                            <span class="field-label">Student ID</span>
                            <span class="field-value">#<c:out value="${student.id}"/></span>
                        </div>
                        <div class="profile-field-item">
                            <span class="field-label">Institutional Email</span>
                            <span class="field-value"><c:out value="${student.email}"/></span>
                        </div>
                        <div class="profile-field-item">
                            <span class="field-label">Branch / Department</span>
                            <span class="field-value"><c:out value="${student.course}"/></span>
                        </div>
                        <div class="profile-field-item">
                            <span class="field-label">Enrollment Date</span>
                            <span class="field-value"><c:out value="${student.createdAt}"/></span>
                        </div>
                    </div>

                    <a href="${pageContext.request.contextPath}/students" class="btn-view-profile">
                        &larr; Back to Directory
                    </a>
                </div>
            </div>

            <!-- Right Column: Associated Documents & Verification Media -->
            <div class="profile-main-col">
                <div class="card">
                    <div class="card-header-flex">
                        <div>
                            <h2 class="page-title">Verification Documents &amp; Files</h2>
                            <p class="page-subtitle">Academic transcripts, ID verifications, and certificates streamed from server</p>
                        </div>
                        <span class="course-badge">
                            <c:out value="${files.size()}"/> File(s)
                        </span>
                    </div>

                    <c:choose>
                        <c:when test="${empty files}">
                            <div class="empty-state-box">
                                <span class="empty-state-icon">📁</span>
                                <h3>No Documents Available</h3>
                                <p>There are currently no verification documents or certificates attached to this student record.</p>
                            </div>
                        </c:when>

                        <c:otherwise>
                            <div class="doc-list">
                                <c:forEach var="file" items="${files}">
                                    <div class="doc-item-row">
                                        <div class="doc-item-left">
                                            <div class="doc-icon-badge">
                                                <c:choose>
                                                    <c:when test="${file.isImage()}">🖼️</c:when>
                                                    <c:when test="${file.isPdf()}">📄</c:when>
                                                    <c:otherwise>📎</c:otherwise>
                                                </c:choose>
                                            </div>

                                            <div class="doc-details">
                                                <span class="doc-name" title="<c:out value='${file.originalFilename}'/>">
                                                    <c:out value="${file.originalFilename}"/>
                                                </span>
                                                <span class="doc-submeta">
                                                    <c:out value="${file.extension}"/> &bull; 
                                                    <c:out value="${file.formattedFileSize}"/> &bull; 
                                                    Uploaded on <c:out value="${file.uploadDate}"/>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="doc-actions">
                                            <a 
                                                href="${pageContext.request.contextPath}/file?id=${file.id}&mode=inline" 
                                                target="_blank" 
                                                class="btn-doc"
                                                title="View file in browser tab"
                                            >
                                                👁️ View
                                            </a>
                                            <a 
                                                href="${pageContext.request.contextPath}/file?id=${file.id}&mode=download" 
                                                class="btn-doc"
                                                title="Download to computer"
                                            >
                                                ⬇️ Download
                                            </a>
                                        </div>
                                    </div>
                                </c:forEach>
                            </div>
                        </c:otherwise>
                    </c:choose>

                </div>
            </div>

        </div>
    </main>

    <!-- Application Footer -->
    <footer class="app-footer">
        <p>&copy; 2026 Student Profile Portal &bull; Java Servlets 6.0, JSP 3.1 &amp; JDBC Laboratory Assignment</p>
    </footer>

</body>
</html>
