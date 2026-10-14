<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="jakarta.tags.core" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Sign In | Student Profile Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
</head>
<body class="login-page-body">

    <div class="login-card">
        <div class="login-header">
            <span class="login-logo" aria-hidden="true">🎓</span>
            <h1 class="login-title">Student Portal Sign In</h1>
            <p class="login-subtitle">Enter your institutional credentials to access student profiles</p>
        </div>

        <%-- Error Alert Banner --%>
        <c:if test="${not empty errorMessage}">
            <div class="alert alert-danger" role="alert">
                <span>⚠️</span>
                <span><c:out value="${errorMessage}"/></span>
            </div>
        </c:if>

        <%-- Logout Notice --%>
        <c:if test="${param.logout eq 'success'}">
            <div class="alert alert-success" role="alert">
                <span>✅</span>
                <span>You have been successfully signed out of the portal.</span>
            </div>
        </c:if>

        <!-- Login Form -->
        <form method="POST" action="${pageContext.request.contextPath}/login" novalidate>
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-input" 
                    placeholder="Enter your username"
                    value="<c:out value='${enteredUsername}'/>"
                    required 
                    autofocus
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-primary-block">
                Sign In to Portal
            </button>
        </form>

        <!-- Test Account Notice -->
        <div class="login-demo-box">
            <strong>Sample Testing Credentials:</strong><br>
            &bull; Admin: <code>admin</code> / <code>Admin@123</code><br>
            &bull; Faculty: <code>faculty</code> / <code>Faculty@2026</code><br>
            <em>Passwords are verified against SHA-256 hashes in MySQL.</em>
        </div>
    </div>

</body>
</html>
