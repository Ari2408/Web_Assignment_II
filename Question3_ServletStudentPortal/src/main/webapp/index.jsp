<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%-- Redirect root index directly to the StudentListServlet --%>
<c:redirect url="/students" xmlns:c="jakarta.tags.core"/>
<%
    // Fallback scriptlet redirect if JSTL tag is not evaluated
    response.sendRedirect(request.getContextPath() + "/students");
%>
