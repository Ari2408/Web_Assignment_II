# Student Admission Registration Portal

A complete, responsive, and accessible **Student Admission Registration Portal** built with **HTML5, CSS3, and Vanilla JavaScript (ES6+)** for the Web Technology laboratory assignment.

---

## 1. Project Objectives

- **Semantic & Accessible Markup**: Construct a clean, accessible student registration interface using semantic HTML5 elements, explicit `<label>` bindings, and ARIA attributes (`aria-describedby`, `aria-invalid`, `aria-live`, `role="alert"`).
- **Modern & Responsive Styling**: Implement a mobile-first, professional blue-and-white theme with responsive layouts for mobile smartphones, tablets, and desktop displays using CSS Grid and Flexbox.
- **Client-Side Form Validation**: Implement client-side validation using vanilla JavaScript without external libraries or frameworks.
- **Real-Time Interactive Feedback**: Provide real-time field validation, password rule checklists, drag-and-drop file handling, inline error messages, and an applicant details summary upon successful submission.
- **Frontend Demonstration**: Operate entirely on the client side with no backend or mock server dependencies.

---

## 2. Technologies Used

- **HTML5**: Semantic tags (`<header>`, `<main>`, `<footer>`, `<form>`, `<select>`, `<input>`), input validation attributes (`required`, `accept`, `autocomplete`), and accessibility attributes.
- **CSS3**: CSS Custom Variables (design tokens), Flexbox, CSS Grid, media queries, custom focus rings, micro-transitions, and responsive typography.
- **Vanilla JavaScript (ES6+)**: Event listeners, regular expressions (RFC email, name, roll number, password patterns), DOM manipulation, and HTML5 File API.

---

## 3. Project Structure

```text
Question1_StudentRegistration/
├── index.html       # Main HTML document with semantic structure and form fields
├── style.css        # Responsive styling, color variables, layout, and states
├── script.js       # Client-side validation logic, UI handlers, and state management
└── README.md        # Project documentation and running instructions
```

---

## 4. Key Features

### A. Responsive Design & Visual Theme
- **Blue & White Palette**: Primary royal blue (`#2563eb`, `#1e40af`) headers and buttons, crisp white card containers, and light blue-slate backgrounds (`#f0f7ff`).
- **Responsive Layout**: Single-column layout on mobile devices (< 680px), transitioning into a two-column grid layout for desktop and tablet viewports.
- **Focus & Hover Effects**: Accessible outline focus rings (`rgba(37, 99, 235, 0.22)`), smooth button translations, and interactive hover states.

### B. Form Fields Included
1. **Full Name**: Text input with real-time character and minimum length validation.
2. **Roll Number**: Alphanumeric input supporting college roll number schemes (e.g., `23CS0101`).
3. **Email Address**: Standard RFC email format verification.
4. **Course Dropdown**: Select dropdown with options:
   - CSE (Computer Science & Engineering)
   - ECE (Electronics & Communication Engineering)
   - EEE (Electrical & Electronics Engineering)
   - IT (Information Technology)
   - Mechanical Engineering
   - Civil Engineering
5. **Password**: Password input with visibility toggle and a dynamic 5-rule checklist.
6. **Confirm Password**: Password input with visibility toggle and live match verification.
7. **Document / Profile Upload**: File upload drop zone supporting drag-and-drop and standard file browsing.

### C. Client-Side Validation Rules
- **Required Fields**: All fields must be completed before submission is permitted.
- **Full Name**: Must be at least 2 characters long and contain only letters, spaces, hyphens, and apostrophes (`/^[A-Za-z\s'-]{2,50}$/`).
- **Roll Number**: Must not be empty and must match alphanumeric format (`/^[A-Za-z0-9/-]{2,20}$/`).
- **Email**: Must follow a valid email pattern (`/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/`).
- **Password Strength**: Minimum 8 characters with at least:
  - 1 uppercase letter (`[A-Z]`)
  - 1 lowercase letter (`[a-z]`)
  - 1 numeric digit (`[0-9]`)
  - 1 special character (`[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`])
- **Confirm Password**: Must match the password value.
- **Document / Profile Upload**:
  - Accepted file types: **PDF, JPG, JPEG, PNG** (validated by file extension and MIME type).
  - Maximum file size: **2 MB** (`2,097,152 bytes`).
- **Inline Error Messages**: Displayed directly below each invalid input with high contrast red coloring and `aria-live` screen-reader announcements.
- **Form Submission Guard**: `event.preventDefault()` blocks submission if validation fails and automatically shifts focus to the first invalid field.
- **Success Notification**: Displays an applicant details summary card upon successful submission with a reset option.

---

## 5. File Explanation & Architecture

### `index.html`
- Contains semantic HTML5 page structure.
- Incorporates a header with college/portal branding.
- Uses `<form id="registrationForm" novalidate>` to disable native browser tooltip overlays in favor of accessible custom inline messages.
- Organizes inputs into responsive `.form-row` containers.
- Implements an interactive file upload drop area (`#dropArea`) and file preview card (`#filePreview`).
- Contains a `#successBanner` region populated dynamically via JavaScript.

### `style.css`
- Organized into 12 sections using CSS variables for colors, shadows, border radii, and transitions.
- Employs mobile-first media queries (`min-width: 480px`, `min-width: 560px`, `min-width: 680px`).
- Provides distinct states:
  - Default: Slate borders with soft hover transitions.
  - Focus: Blue focus rings (`--shadow-focus`).
  - Error: Red border, light-red background tint (`.has-error`), and visible error messages.
  - Valid: Green border highlight (`.is-valid`).
  - Drag-over: Highlighted blue drop zone with slight scaling (`.drag-over`).

### `script.js`
- Encapsulates all application logic inside a `DOMContentLoaded` event listener.
- Maintains separation of concerns across helper utilities, validators, UI updaters, and event dispatchers.

---

## 6. Important JavaScript Functions

| Function Name | Description |
| :--- | :--- |
| `setError(inputElement, errorElement, message)` | Adds `.has-error` class to the input's parent `.form-group`, sets `aria-invalid="true"`, and displays the specific error text in the error container. |
| `clearError(inputElement, errorElement)` | Removes `.has-error`, adds `.is-valid`, resets `aria-invalid="false"`, and empties the error message string. |
| `validateFullName()` | Validates that the full name is non-empty, contains at least 2 characters, and uses only valid alphabetical/punctuation characters. |
| `validateRollNumber()` | Validates that the roll number is non-empty and adheres to the alphanumeric format. |
| `validateEmail()` | Validates the email address against an RFC-compliant regex pattern. |
| `validateCourse()` | Verifies that a valid engineering department has been selected from the dropdown list. |
| `updatePasswordChecklist(value)` | Dynamically evaluates password requirements (length, uppercase, lowercase, number, special char) and toggles `.rule-passed` green checkmarks on each checklist item. |
| `validatePassword()` | Enforces the 5 security rules on the password and displays descriptive missing requirement text if any rule fails. |
| `validateConfirmPassword()` | Verifies that the confirmation field is non-empty and strictly equals the password value. |
| `validateDocumentUpload()` | Inspects the selected file's extension (`.pdf`, `.jpg`, `.jpeg`, `.png`), MIME type, and size (`<= 2 MB`). Displays formatted size or specific error. |
| `showFilePreview(file)` | Displays a preview chip showing the file's extension badge, file name, and human-readable size. |
| `formatFileSize(bytes)` | Converts raw byte values into readable units (`KB` or `MB`). |
| `setupPasswordToggle(button, input)` | Toggles input type between `password` and `text` with corresponding eye/eye-off icon changes. |
| `resetWholeForm()` | Clears all input values, validation states, checklist indicators, preview cards, and restores the form to its initial state. |
| `form.addEventListener('submit', ...)` | Prevents browser reload, runs all 7 validators, shifts focus to the first invalid field on failure, or renders the applicant summary on success. |

---

## 7. Instructions to Run and Test the Project

### Option A: Open Directly in a Browser (No Server Required)
1. Navigate to the `Question1_StudentRegistration` folder in your file explorer.
2. Double-click `index.html` (or right-click -> **Open with** -> Google Chrome / Microsoft Edge / Mozilla Firefox).
3. The portal will load with full functionality.

### Option B: Run via a Local Development Server (Recommended)
Open a terminal in the project directory (`Question1_StudentRegistration`) and run any of the following:

- **Using Python 3:**
  ```bash
  python -m http.server 8000
  ```
  Open [http://localhost:8000](http://localhost:8000) in your browser.

- **Using Node.js (`npx serve`):**
  ```bash
  npx serve .
  ```

- **Using VS Code Live Server:**
  Right-click `index.html` in VS Code and select **Open with Live Server**.

---

## 8. Test Cases & Verification Scenarios

| Test Case | Inputs / Action | Expected Result |
| :--- | :--- | :--- |
| **Empty Submission** | Click **Submit Registration** without entering data. | Submission is prevented. All fields show red borders and inline error messages. Focus moves to **Full Name**. |
| **Invalid Name** | Name: `A` or `John123` | Shows "Name must be at least 2 characters long" or "Name can only contain alphabets...". |
| **Invalid Roll No** | Roll No: `?#$@` | Shows "Roll number must be between 2 and 20 alphanumeric characters". |
| **Invalid Email** | Email: `student@`, `student.com`, `@domain.com` | Shows "Please enter a valid email address (e.g. student@college.edu)". |
| **No Course Selected** | Keep dropdown on `-- Select an Engineering Branch --` | Shows "Please select a valid course from the dropdown." |
| **Weak Password** | Password: `password` (no uppercase, number, or special char) | Checklist items remain gray. Shows specific error listing missing requirements. |
| **Password Mismatch** | Password: `Password@123`, Confirm: `Password@456` | Shows "Passwords do not match. Please re-enter." |
| **Invalid File Type** | Upload `.txt`, `.exe`, `.zip`, `.docx` | Shows "Invalid file type... Only PDF, JPG, JPEG, and PNG files are accepted." |
| **Oversized File** | Upload a file larger than 2 MB | Shows "File size exceeds 2 MB limit (X.XX MB). Please choose a file smaller than 2 MB." |
| **Valid Submission** | Valid Name, Roll No, Email, Course, Strong Password matching Confirm Password, and a valid `.pdf` or `.png` <= 2MB | Form transitions to a success card displaying applicant details and a "Register Another Student" reset button. No console errors. |

---

## 9. Academic Integrity & Quality Disclaimer
- This project is a **frontend-only client-side demonstration**.
- No data is submitted or persisted to any remote server or database.
- Adheres to standard web accessibility and responsive design principles.
