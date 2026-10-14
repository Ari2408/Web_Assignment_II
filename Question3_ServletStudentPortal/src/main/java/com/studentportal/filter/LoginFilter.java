package com.studentportal.filter;

import jakarta.servlet.Filter;
import jakarta.servlet.FilterChain;
import jakarta.servlet.FilterConfig;
import jakarta.servlet.ServletException;
import jakarta.servlet.ServletRequest;
import jakarta.servlet.ServletResponse;
import jakarta.servlet.annotation.WebFilter;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

import java.io.IOException;

/**
 * Intercepts requests to enforce session-based authentication across all portal endpoints.
 * Redirects unauthenticated requests to /login.
 */
@WebFilter(filterName = "LoginFilter", urlPatterns = {"/*"})
public class LoginFilter implements Filter {

    @Override
    public void init(FilterConfig filterConfig) throws ServletException {
        // Filter initialization
    }

    @Override
    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {

        HttpServletRequest httpRequest = (HttpServletRequest) request;
        HttpServletResponse httpResponse = (HttpServletResponse) response;

        String contextPath = httpRequest.getContextPath();
        String uri = httpRequest.getRequestURI();

        // Path within current webapp context
        String path = uri.substring(contextPath.length());

        // Whitelist public resources that do not require authentication
        boolean isPublicResource = path.equals("/login") ||
                                   path.equals("/login.jsp") ||
                                   path.equals("/logout") ||
                                   path.startsWith("/css/") ||
                                   path.startsWith("/images/") ||
                                   path.equals("/favicon.ico");

        if (isPublicResource) {
            chain.doFilter(request, response);
            return;
        }

        // Check for active authenticated session
        HttpSession session = httpRequest.getSession(false);
        boolean isAuthenticated = (session != null && session.getAttribute("loggedUser") != null);

        if (isAuthenticated) {
            // Prevent browser back-button caching of sensitive student data
            httpResponse.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
            httpResponse.setHeader("Pragma", "no-cache");
            httpResponse.setDateHeader("Expires", 0);

            chain.doFilter(request, response);
        } else {
            // Unauthenticated user attempting to access protected resource
            httpResponse.sendRedirect(contextPath + "/login");
        }
    }

    @Override
    public void destroy() {
        // Clean up resources if necessary
    }
}
