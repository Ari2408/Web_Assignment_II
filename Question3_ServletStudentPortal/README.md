# Student Profile Portal (Java Servlets, JSP, JDBC, MySQL)

A complete, enterprise-grade **Student Profile Portal** built using **Java Servlets 6.0, JSP 3.1, JDBC, and MySQL**, architected according to the standard **Model-View-Controller (MVC)** and **Data Access Object (DAO)** patterns for the Web Technology laboratory assignment.

---

## 1. Technology Stack & Compatibility Identification

According to enterprise Java specifications, Servlet namespace changes took place in recent versions. This project uses the modern, current Long Term Support (LTS) stack:

| Component | Selected Version | Specification / Namespace |
| :--- | :--- | :--- |
| **Java Development Kit (JDK)** | **JDK 17 LTS / JDK 21 LTS** | Standard Java SE (verified with Java 21) |
| **Servlet Specification** | **Servlet 6.0 / 5.0** | **`jakarta.servlet.*`** consistently across all classes |
| **JavaServer Pages (JSP)** | **Jakarta Pages 3.1** | `jakarta.servlet.jsp.*` / JSTL `jakarta.tags.core` |
| **Application Server** | **Apache Tomcat 10.1.x / 11.x** | Fully compliant with Jakarta EE 10 / Servlet 6.0 |
| **Database Server** | **MySQL 5.7+ / 8.0+ / MariaDB** | MySQL Connector/J 8.3.0+ |
| **Build & Packaging Tool** | **Apache Maven** | Standard Java Web Archive (`WAR`) packaging |

> **Namespace Consistency Notice**: This project exclusively uses the **`jakarta.servlet.*`** namespace across all Servlets, Filters, and JSPs (no deprecated `javax.servlet` references). It is natively compatible with **Apache Tomcat 10.1.x** and **Tomcat 11.x**.

---

## 2. Project Architecture & Directory Structure

```text
Question3_ServletStudentPortal/
├── pom.xml                                   # Maven build configuration & dependencies
├── database.sql                              # MySQL schema script with sample records
├── README.md                                 # Complete documentation, setup, and test guide
└── src/
    └── main/
        ├── java/
        │   └── com/
        │       └── studentportal/
        │           ├── model/
        │           │   ├── Student.java      # Student domain model
        │           │   ├── StudentFile.java  # Uploaded document/photo metadata model
        │           │   └── User.java         # Authenticated portal user model
        │           ├── dao/
        │           │   ├── DBConnection.java # Thread-safe JDBC connection manager
        │           │   ├── StudentDAO.java   # Student & file JDBC queries (PreparedStatements)
        │           │   └── UserDAO.java      # User authentication queries (PreparedStatements)
        │           ├── servlet/
        │           │   ├── LoginServlet.java # Authentication controller (/login)
        │           │   ├── LogoutServlet.java# Session invalidation controller (/logout)
        │           │   ├── StudentListServlet.java    # Directory listing controller (/students)
        │           │   ├── StudentProfileServlet.java# Individual profile controller (/student-profile)
        │           │   └── FileServlet.java  # Anti-traversal secure file streamer (/file)
        │           ├── filter/
        │           │   └── LoginFilter.java  # Centralized session guard filter (/*)
        │           └── util/
        │               └── PasswordUtil.java # SHA-256 password hashing & CLI generator
        ├── resources/
        │   └── db.properties                 # Externalized JDBC and upload path configuration
        └── webapp/
            ├── index.jsp                     # Root landing redirector -> /students
            ├── login.jsp                     # Responsive sign-in form with demo hints
            ├── students.jsp                  # Student cards grid with dynamic avatars
            ├── student-profile.jsp           # Detailed student view with document list
            ├── error.jsp                     # Friendly user error & 404/500 handler
            ├── css/
            │   └── style.css                 # Master responsive CSS3 stylesheet
            └── WEB-INF/
                └── web.xml                   # Jakarta EE 10 web deployment descriptor
```

---

## 3. Database Schema Design (`database.sql`)

The application connects to a MySQL database named `student_management_servlet`:

### A. `users` Table
Stores portal login credentials. Passwords are saved strictly as **SHA-256 cryptographic hashes**, never in plaintext.
- `id`: INT AUTO_INCREMENT PRIMARY KEY
- `username`: VARCHAR(50) UNIQUE NOT NULL
- `password_hash`: VARCHAR(64) NOT NULL (SHA-256 hex digest)
- `full_name`: VARCHAR(100) NOT NULL
- `role`: VARCHAR(20) DEFAULT 'ADMIN'
- `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### B. `students` Table
Stores student academic credentials.
- `id`: INT AUTO_INCREMENT PRIMARY KEY
- `name`: VARCHAR(100) NOT NULL
- `roll_number`: VARCHAR(50) UNIQUE NOT NULL
- `email`: VARCHAR(150) NOT NULL
- `course`: VARCHAR(100) NOT NULL
- `created_at`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### C. `student_files` Table
Stores metadata for uploaded student photos and documents.
- `id`: INT AUTO_INCREMENT PRIMARY KEY
- `student_id`: INT NOT NULL (Foreign key -> `students(id)` ON DELETE CASCADE)
- `original_filename`: VARCHAR(255) NOT NULL
- `stored_filename`: VARCHAR(255) NOT NULL
- `file_path`: VARCHAR(255) NOT NULL
- `file_type`: VARCHAR(50) NOT NULL
- `file_size`: INT NOT NULL
- `upload_date`: TIMESTAMP DEFAULT CURRENT_TIMESTAMP

---

## 4. Key Component & Workflow Explanations

### 1. `LoginFilter.java` & Session Security
- Intercepts all incoming requests (`/*`).
- Whitelists public paths (`/login`, `/logout`, `/css/*`, `/favicon.ico`).
- For protected resources (`/students`, `/student-profile`, `/file`), verifies `session != null && session.getAttribute("loggedUser") != null`.
- Automatically redirects unauthenticated requests to `${pageContext.request.contextPath}/login`.
- Injects HTTP headers (`Cache-Control: no-cache, no-store, must-revalidate`) preventing browser back-button caching of student data after logout.

### 2. `PasswordUtil.java` & Secure Account Creation
- Implements `hashPassword(String)` using standard Java `MessageDigest.getInstance("SHA-256")`.
- Implements `verifyPassword(String plain, String hash)` using constant-time `MessageDigest.isEqual()` to prevent timing attacks.
- Contains a standalone `main()` method allowing administrators and evaluators to generate hashes from the command line:
  ```bash
  java -cp target/classes com.studentportal.util.PasswordUtil "MySecretPass"
  ```

### 3. `StudentListServlet.java` & `students.jsp`
- Queries all students and resolves each student's profile image file ID:
  ```sql
  SELECT s.*, (SELECT f.id FROM student_files f WHERE f.student_id = s.id AND f.file_type LIKE 'image/%' LIMIT 1) AS profile_image_id FROM students s
  ```
- In `students.jsp`, renders student cards in a responsive CSS Grid using `<c:forEach>`.
- Employs `<c:choose>` conditional logic:
  - If a student has an image, renders:
    `<img src="${pageContext.request.contextPath}/file?id=${student.profileImageFileId}" class="avatar-img" />`
  - Otherwise, renders a clean CSS avatar with student initials:
    `<div class="avatar-initials"><c:out value="${student.initials}" /></div>`
- All dynamic fields use `<c:out>` for output escaping.

### 4. `StudentProfileServlet.java` & `student-profile.jsp`
- Receives student `id`, validates integer format, and fetches the student record plus all associated files from `student_files`.
- If an invalid ID or missing record is requested, gracefully forwards to `error.jsp`.
- Displays profile hero header, academic attributes, and document list with **View** (`mode=inline`) and **Download** (`mode=download`) actions pointing to `/file`.

### 5. `FileServlet.java` (Anti-Traversal File Streaming)
- Receives file `id` parameter.
- Queries `StudentFile` record from MySQL via `StudentDAO`.
- Resolves the stored file on server storage using `DBConnection.getUploadDir()`.
- **Directory Traversal Protection**: Computes the canonical path of the base folder and the target file:
  ```java
  File baseDir = new File(DBConnection.getUploadDir()).getCanonicalFile();
  File targetFile = new File(baseDir, fileRecord.getStoredFilename()).getCanonicalFile();
  if (!targetFile.getPath().startsWith(baseDir.getPath())) {
      response.sendError(HttpServletResponse.SC_FORBIDDEN);
      return;
  }
  ```
- Sends exact MIME headers (`image/jpeg`, `application/pdf`, etc.), Content-Length, and dynamic `Content-Disposition: inline` or `attachment`.
- If a profile image is missing on disk, streams an inline SVG avatar placeholder so the UI never breaks.

---

## 5. Step-by-Step Configuration and Execution Guide

### Step 1: Install and Configure JDK
1. Download and install **JDK 17 LTS** or **JDK 21 LTS** (e.g. Eclipse Temurin or Oracle JDK).
2. Set the `JAVA_HOME` environment variable to your JDK path (e.g. `C:\Program Files\Eclipse Adoptium\jdk-21.0.12.1-hotspot`).
3. Add `%JAVA_HOME%\bin` to your system `PATH`.
4. Verify by running in PowerShell/Terminal:
   ```powershell
   java -version
   javac -version
   ```

### Step 2: Install Apache Tomcat 10.1.x
1. Download **Apache Tomcat 10.1.x** (Core zip or installer) from [https://tomcat.apache.org/download-10.cgi](https://tomcat.apache.org/download-10.cgi).
2. Extract to a directory such as `C:\apache-tomcat-10.1.x`.

### Step 3: Install MySQL and Import `database.sql`
1. Start MySQL using XAMPP or native MySQL Server on port `3306`.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`) or MySQL CLI.
3. Import `database.sql` located at:
   `Question3_ServletStudentPortal/database.sql`
4. This creates `student_management_servlet` with tables `users`, `students`, `student_files`, and pre-configured accounts.

### Step 4: Configure `db.properties`
Open `src/main/resources/db.properties` and verify your MySQL and upload directory settings:
```properties
db.driver=com.mysql.cj.jdbc.Driver
db.url=jdbc:mysql://localhost:3306/student_management_servlet?useSSL=false&allowPublicKeyRetrieval=true&serverTimezone=UTC&characterEncoding=UTF-8
db.user=root
db.password=

# Upload directory (outside web root)
upload.dir=C:/student_portal_uploads/
```

### Step 5: Configure the Upload Storage Directory
Create the folder `C:\student_portal_uploads\` on your machine:
```powershell
New-Item -ItemType Directory -Path "C:\student_portal_uploads" -Force
```
*(Optional: Copy sample PDFs or images from Question 2 or sample files into this folder for testing).*

### Step 6: Build the Application Using Maven
In the `Question3_ServletStudentPortal` directory, run:
```bash
mvn clean package
```
This compiles all Java classes, packages the JSP files, and creates the file:
```text
target/studentportal.war
```

### Step 7: Deploy WAR to Apache Tomcat
1. Copy `target/studentportal.war` into your Tomcat webapps directory:
   ```text
   C:\apache-tomcat-10.1.x\webapps\studentportal.war
   ```
2. Also copy the MySQL JDBC driver jar (`mysql-connector-j-8.3.0.jar` or similar) to `C:\apache-tomcat-10.1.x\lib\` if not bundled in the WAR.

### Step 8: Start Tomcat & Open the Application
1. In `C:\apache-tomcat-10.1.x\bin\`, run:
   ```powershell
   .\startup.bat
   ```
   *(On Linux/macOS: `./startup.sh`)*
2. In your browser, open:
   ```text
   http://localhost:8080/studentportal/
   ```
   *(Or `http://localhost:8080/studentportal/login`)*

---

## 6. Testing & Verification Walkthrough

| Test Flow | Action | Expected Output |
| :--- | :--- | :--- |
| **1. Unauthenticated Guard** | Navigate to `http://localhost:8080/studentportal/students` without logging in. | `LoginFilter` intercepts the request and redirects to `/login`. |
| **2. Invalid Login** | Enter `admin` with wrong password `WrongPassword`. | Displays red alert: *"Invalid username or password. Please try again."* |
| **3. Valid Login** | Username: `admin` & Password: `Admin@123`. | Successfully logs in, establishes session, and redirects to `/students`. |
| **4. Student Directory** | View `/students` directory page. | Displays 6 student cards with names, roll numbers, emails, branch badges, and dynamic profile avatars. |
| **5. Avatar Streaming** | Observe image tags on cards (`/file?id=1`). | `FileServlet` streams the student image or fallback SVG avatar with `Content-Type: image/jpeg` or `image/svg+xml`. |
| **6. View Student Profile** | Click **View Full Profile** on Aarav Sharma. | Loads `/student-profile?id=1` with full student information and associated document records. |
| **7. Inline Document View** | Click **View** on `admission_form.pdf`. | Opens PDF inline in a new browser tab streamed via `FileServlet?id=2&mode=inline`. |
| **8. Download Document** | Click **Download** on `admission_form.pdf`. | Triggers file download with `Content-Disposition: attachment`. |
| **9. Invalid Student ID** | Navigate to `/student-profile?id=9999` or `/student-profile?id=abc`. | Redirects to `error.jsp` with descriptive message: *"Student with ID #9999 was not found in the database."* |
| **10. Directory Traversal Attack Test** | Attempt to request arbitrary paths. | `FileServlet` checks canonical paths against `upload.dir` and rejects traversal attempts with HTTP 403 Forbidden. |
| **11. Logout** | Click **Sign Out** button in navbar. | Invalids session, prevents back-button caching, and redirects to `/login?logout=success` with confirmation alert. |

---

## 7. Assignment Conclusion

> *This Student Profile Portal demonstrates the complete architecture of enterprise Java web development by integrating Jakarta Servlets 6.0, JavaServer Pages (JSP 3.1), JDBC, and MySQL within the Model-View-Controller paradigm. By leveraging prepared statements for database operations, implementing a centralized Servlet Filter for session-based access control, hashing credentials with SHA-256, and securing file streaming against directory traversal attacks, the application delivers a resilient, secure, and production-ready academic administrative solution.*
