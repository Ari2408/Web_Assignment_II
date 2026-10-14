/**
 * ==============================================================================
 * Student Admission Registration Portal - Client-Side Validation Logic
 * Script: script.js
 * Technology: Vanilla JavaScript (ES6+)
 * Description: Handles accessible, real-time client-side form validation,
 *              interactive UI feedback, password strength verification, file drag-and-drop,
 *              and submission handling without backend dependency.
 * ==============================================================================
 */

// Execute logic once DOM is completely loaded
document.addEventListener('DOMContentLoaded', () => {

  // ----------------------------------------------------------------------------
  // 1. DOM Element References
  // ----------------------------------------------------------------------------
  const form = document.getElementById('registrationForm');
  const formHeader = document.getElementById('formHeader');
  const successBanner = document.getElementById('successBanner');
  const registerAnotherBtn = document.getElementById('registerAnotherBtn');
  const resetBtn = document.getElementById('resetBtn');

  // Input Fields
  const fullNameInput = document.getElementById('fullName');
  const rollNumberInput = document.getElementById('rollNumber');
  const emailInput = document.getElementById('email');
  const courseSelect = document.getElementById('course');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirmPassword');
  const documentUploadInput = document.getElementById('documentUpload');

  // Error Containers
  const fullNameError = document.getElementById('fullNameError');
  const rollNumberError = document.getElementById('rollNumberError');
  const emailError = document.getElementById('emailError');
  const courseError = document.getElementById('courseError');
  const passwordError = document.getElementById('passwordError');
  const confirmPasswordError = document.getElementById('confirmPasswordError');
  const documentError = document.getElementById('documentError');

  // Password Checklist Rules Elements
  const ruleLength = document.getElementById('ruleLength');
  const ruleUpper = document.getElementById('ruleUpper');
  const ruleLower = document.getElementById('ruleLower');
  const ruleNumber = document.getElementById('ruleNumber');
  const ruleSpecial = document.getElementById('ruleSpecial');

  // Password Visibility Toggles
  const togglePasswordBtn = document.getElementById('togglePassword');
  const toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');

  // File Upload Elements
  const dropArea = document.getElementById('dropArea');
  const dropContent = document.getElementById('dropContent');
  const filePreview = document.getElementById('filePreview');
  const fileNameDisplay = document.getElementById('fileNameDisplay');
  const fileSizeDisplay = document.getElementById('fileSizeDisplay');
  const fileTypeBadge = document.getElementById('fileTypeBadge');
  const removeFileBtn = document.getElementById('removeFileBtn');

  // Submitted Data Summary Elements (Displayed on Success)
  const summaryFullName = document.getElementById('summaryFullName');
  const summaryRollNumber = document.getElementById('summaryRollNumber');
  const summaryEmail = document.getElementById('summaryEmail');
  const summaryCourse = document.getElementById('summaryCourse');
  const summaryDocument = document.getElementById('summaryDocument');

  // ----------------------------------------------------------------------------
  // 2. Constants & Validation Specifications
  // ----------------------------------------------------------------------------
  // Maximum allowed file size: 2 Megabytes (2 * 1024 * 1024 bytes)
  const MAX_FILE_SIZE_BYTES = 2 * 1024 * 1024; // 2,097,152 bytes

  // Allowed file extensions
  const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

  // Allowed MIME types
  const ALLOWED_MIME_TYPES = [
    'application/pdf',
    'image/jpeg',
    'image/jpg',
    'image/pjpeg',
    'image/png'
  ];

  // Allowed courses list
  const ALLOWED_COURSES = ['CSE', 'ECE', 'EEE', 'IT', 'Mechanical', 'Civil'];

  // Standard RFC 5322 compliant regular expression for email validation
  const EMAIL_REGEX = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;

  // Name regex: Allows alphabetic letters, spaces, hyphens, and apostrophes (2 to 50 chars)
  const NAME_REGEX = /^[A-Za-z\s'-]{2,50}$/;

  // Roll number regex: Alphanumeric characters, slashes and hyphens (2 to 20 chars)
  const ROLL_NO_REGEX = /^[A-Za-z0-9/-]{2,20}$/;

  // Password individual rule regexes
  const PWD_UPPER_REGEX = /[A-Z]/;
  const PWD_LOWER_REGEX = /[a-z]/;
  const PWD_NUM_REGEX = /[0-9]/;
  const PWD_SPECIAL_REGEX = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/;

  // Track if form has been submitted at least once (controls eager vs lazy validation)
  let formSubmitted = false;

  // ----------------------------------------------------------------------------
  // 3. Helper Functions: Error Display & State Management
  // ----------------------------------------------------------------------------

  /**
   * Sets an error state on a form control with accessible attributes and inline text.
   * @param {HTMLElement} inputElement - The input or select element.
   * @param {HTMLElement} errorElement - The span element displaying the error message.
   * @param {string} message - The validation error message to display.
   */
  function setError(inputElement, errorElement, message) {
    const formGroup = inputElement.closest('.form-group');
    if (formGroup) {
      formGroup.classList.add('has-error');
      formGroup.classList.remove('is-valid');
    }
    inputElement.setAttribute('aria-invalid', 'true');
    errorElement.textContent = message;
  }

  /**
   * Clears the error state on a form control.
   * @param {HTMLElement} inputElement - The input or select element.
   * @param {HTMLElement} errorElement - The span element displaying the error message.
   */
  function clearError(inputElement, errorElement) {
    const formGroup = inputElement.closest('.form-group');
    if (formGroup) {
      formGroup.classList.remove('has-error');
      formGroup.classList.add('is-valid');
    }
    inputElement.setAttribute('aria-invalid', 'false');
    errorElement.textContent = '';
  }

  /**
   * Formats file size in bytes to a human-readable string (KB or MB).
   * @param {number} bytes - Size in bytes.
   * @returns {string} Formatted size (e.g., "1.45 MB" or "320 KB").
   */
  function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    const kb = bytes / 1024;
    if (kb < 1024) return kb.toFixed(1) + ' KB';
    const mb = kb / 1024;
    return mb.toFixed(2) + ' MB';
  }

  // ----------------------------------------------------------------------------
  // 4. Individual Field Validation Functions
  // ----------------------------------------------------------------------------

  /**
   * Validates the Full Name field:
   * - Must not be empty.
   * - Must be at least 2 characters.
   * - Must only contain letters, spaces, hyphens, and apostrophes.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validateFullName() {
    const value = fullNameInput.value.trim();

    if (!value) {
      setError(fullNameInput, fullNameError, 'Full name is required.');
      return false;
    }
    if (value.length < 2) {
      setError(fullNameInput, fullNameError, 'Name must be at least 2 characters long.');
      return false;
    }
    if (!NAME_REGEX.test(value)) {
      setError(fullNameInput, fullNameError, 'Name can only contain alphabets, spaces, hyphens, and apostrophes.');
      return false;
    }

    clearError(fullNameInput, fullNameError);
    return true;
  }

  /**
   * Validates the Roll Number field:
   * - Must not be empty.
   * - Must contain valid alphanumeric format.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validateRollNumber() {
    const value = rollNumberInput.value.trim();

    if (!value) {
      setError(rollNumberInput, rollNumberError, 'Roll number is required.');
      return false;
    }
    if (!ROLL_NO_REGEX.test(value)) {
      setError(rollNumberInput, rollNumberError, 'Roll number must be between 2 and 20 alphanumeric characters (e.g. 23CS0101).');
      return false;
    }

    clearError(rollNumberInput, rollNumberError);
    return true;
  }

  /**
   * Validates the Email Address field:
   * - Must not be empty.
   * - Must follow a standard valid email format.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validateEmail() {
    const value = emailInput.value.trim();

    if (!value) {
      setError(emailInput, emailError, 'Email address is required.');
      return false;
    }
    if (!EMAIL_REGEX.test(value)) {
      setError(emailInput, emailError, 'Please enter a valid email address (e.g. student@college.edu).');
      return false;
    }

    clearError(emailInput, emailError);
    return true;
  }

  /**
   * Validates the Course selection dropdown:
   * - Must select one of the allowed engineering programs.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validateCourse() {
    const value = courseSelect.value;

    if (!value || !ALLOWED_COURSES.includes(value)) {
      setError(courseSelect, courseError, 'Please select a valid course from the dropdown.');
      return false;
    }

    clearError(courseSelect, courseError);
    return true;
  }

  /**
   * Updates the password checklist visual indicators in real-time.
   * @param {string} value - The current password string.
   */
  function updatePasswordChecklist(value) {
    // 1. Length check (>= 8 chars)
    if (value.length >= 8) {
      ruleLength.classList.add('rule-passed');
    } else {
      ruleLength.classList.remove('rule-passed');
    }

    // 2. Uppercase check
    if (PWD_UPPER_REGEX.test(value)) {
      ruleUpper.classList.add('rule-passed');
    } else {
      ruleUpper.classList.remove('rule-passed');
    }

    // 3. Lowercase check
    if (PWD_LOWER_REGEX.test(value)) {
      ruleLower.classList.add('rule-passed');
    } else {
      ruleLower.classList.remove('rule-passed');
    }

    // 4. Number check
    if (PWD_NUM_REGEX.test(value)) {
      ruleNumber.classList.add('rule-passed');
    } else {
      ruleNumber.classList.remove('rule-passed');
    }

    // 5. Special character check
    if (PWD_SPECIAL_REGEX.test(value)) {
      ruleSpecial.classList.add('rule-passed');
    } else {
      ruleSpecial.classList.remove('rule-passed');
    }
  }

  /**
   * Validates the Password field:
   * - Must contain at least 8 characters.
   * - Must include at least 1 uppercase letter.
   * - Must include at least 1 lowercase letter.
   * - Must include at least 1 number.
   * - Must include at least 1 special character.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validatePassword() {
    const value = passwordInput.value;
    updatePasswordChecklist(value);

    if (!value) {
      setError(passwordInput, passwordError, 'Password is required.');
      return false;
    }

    const missingRules = [];
    if (value.length < 8) missingRules.push('at least 8 characters');
    if (!PWD_UPPER_REGEX.test(value)) missingRules.push('one uppercase letter (A-Z)');
    if (!PWD_LOWER_REGEX.test(value)) missingRules.push('one lowercase letter (a-z)');
    if (!PWD_NUM_REGEX.test(value)) missingRules.push('one number (0-9)');
    if (!PWD_SPECIAL_REGEX.test(value)) missingRules.push('one special character (@$!%*?&#...)');

    if (missingRules.length > 0) {
      setError(passwordInput, passwordError, `Password must include: ${missingRules.join(', ')}.`);
      return false;
    }

    clearError(passwordInput, passwordError);

    // If confirm password already has a value, re-validate it to ensure synchronization
    if (confirmPasswordInput.value.length > 0) {
      validateConfirmPassword();
    }

    return true;
  }

  /**
   * Validates the Confirm Password field:
   * - Must not be empty.
   * - Must strictly match the password field value.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validateConfirmPassword() {
    const passwordVal = passwordInput.value;
    const confirmVal = confirmPasswordInput.value;

    if (!confirmVal) {
      setError(confirmPasswordInput, confirmPasswordError, 'Please confirm your password.');
      return false;
    }
    if (confirmVal !== passwordVal) {
      setError(confirmPasswordInput, confirmPasswordError, 'Passwords do not match. Please re-enter.');
      return false;
    }

    clearError(confirmPasswordInput, confirmPasswordError);
    return true;
  }

  /**
   * Validates the Document/Image File Upload field:
   * - Must have a file selected.
   * - Must match allowed extension (.pdf, .jpg, .jpeg, .png).
   * - Must match allowed MIME types.
   * - Must not exceed 2 MB.
   * @returns {boolean} True if valid, false otherwise.
   */
  function validateDocumentUpload() {
    const files = documentUploadInput.files;

    if (!files || files.length === 0) {
      setError(documentUploadInput, documentError, 'Please upload a profile photo or document (PDF, JPG, PNG).');
      hideFilePreview();
      return false;
    }

    const file = files[0];
    const fileName = file.name;
    const fileExtension = fileName.split('.').pop().toLowerCase();
    const fileSize = file.size;
    const fileMime = file.type.toLowerCase();

    // 1. Extension check
    if (!ALLOWED_EXTENSIONS.includes(fileExtension)) {
      setError(
        documentUploadInput,
        documentError,
        `Invalid file type ".${fileExtension}". Only PDF, JPG, JPEG, and PNG files are accepted.`
      );
      hideFilePreview();
      return false;
    }

    // 2. MIME type check (when browser provides it)
    if (fileMime && !ALLOWED_MIME_TYPES.includes(fileMime)) {
      setError(
        documentUploadInput,
        documentError,
        'Invalid file format. Supported file formats are PDF, JPG, JPEG, and PNG.'
      );
      hideFilePreview();
      return false;
    }

    // 3. File size limit check (Max 2 MB)
    if (fileSize > MAX_FILE_SIZE_BYTES) {
      const formattedSize = formatFileSize(fileSize);
      setError(
        documentUploadInput,
        documentError,
        `File size exceeds 2 MB limit (${formattedSize}). Please choose a file smaller than 2 MB.`
      );
      hideFilePreview();
      return false;
    }

    // All file checks passed: display preview card and clear errors
    clearError(documentUploadInput, documentError);
    showFilePreview(file);
    return true;
  }

  // ----------------------------------------------------------------------------
  // 5. File Preview & Drop Zone UI Helpers
  // ----------------------------------------------------------------------------

  /**
   * Displays the selected file's name, size, and extension badge in the UI.
   * @param {File} file - The uploaded file object.
   */
  function showFilePreview(file) {
    const ext = file.name.split('.').pop().toUpperCase();
    fileTypeBadge.textContent = ext || 'FILE';
    fileNameDisplay.textContent = file.name;
    fileSizeDisplay.textContent = formatFileSize(file.size);

    dropContent.style.display = 'none';
    filePreview.style.display = 'flex';
  }

  /**
   * Hides the file preview card and resets the drop zone contents.
   */
  function hideFilePreview() {
    dropContent.style.display = 'block';
    filePreview.style.display = 'none';
    fileNameDisplay.textContent = '';
    fileSizeDisplay.textContent = '';
  }

  /**
   * Clears the currently selected file.
   */
  function removeSelectedFile() {
    documentUploadInput.value = '';
    hideFilePreview();
    const formGroup = documentUploadInput.closest('.form-group');
    if (formGroup) {
      formGroup.classList.remove('has-error', 'is-valid');
    }
    documentError.textContent = '';
    documentUploadInput.setAttribute('aria-invalid', 'false');
  }

  // ----------------------------------------------------------------------------
  // 6. Password Visibility Toggle Handlers
  // ----------------------------------------------------------------------------
  function setupPasswordToggle(button, input) {
    if (!button || !input) return;

    button.addEventListener('click', (e) => {
      e.preventDefault();
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      const openIcon = button.querySelector('.eye-open');
      const closedIcon = button.querySelector('.eye-closed');

      if (openIcon && closedIcon) {
        openIcon.style.display = isPassword ? 'none' : 'block';
        closedIcon.style.display = isPassword ? 'block' : 'none';
      }

      button.setAttribute(
        'aria-label',
        isPassword ? 'Hide password' : 'Show password'
      );
    });
  }

  setupPasswordToggle(togglePasswordBtn, passwordInput);
  setupPasswordToggle(toggleConfirmPasswordBtn, confirmPasswordInput);

  // ----------------------------------------------------------------------------
  // 7. Drag and Drop File Handlers
  // ----------------------------------------------------------------------------
  ['dragenter', 'dragover'].forEach(eventName => {
    dropArea.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropArea.classList.add('drag-over');
    }, false);
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropArea.classList.remove('drag-over');
    }, false);
  });

  dropArea.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    if (dt && dt.files && dt.files.length > 0) {
      documentUploadInput.files = dt.files;
      validateDocumentUpload();
    }
  });

  // Handle standard file selection via dialog
  documentUploadInput.addEventListener('change', () => {
    validateDocumentUpload();
  });

  // Handle file remove button click
  removeFileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    removeSelectedFile();
  });

  // ----------------------------------------------------------------------------
  // 8. Event Listeners for Live / Real-Time Validation
  // ----------------------------------------------------------------------------

  // Blur validation: Validate when the user leaves a field
  fullNameInput.addEventListener('blur', validateFullName);
  rollNumberInput.addEventListener('blur', validateRollNumber);
  emailInput.addEventListener('blur', validateEmail);
  courseSelect.addEventListener('blur', validateCourse);
  passwordInput.addEventListener('blur', validatePassword);
  confirmPasswordInput.addEventListener('blur', validateConfirmPassword);

  // Input validation: Provide immediate positive feedback once the user interacts
  fullNameInput.addEventListener('input', () => {
    if (formSubmitted || fullNameInput.closest('.form-group').classList.contains('has-error')) {
      validateFullName();
    }
  });

  rollNumberInput.addEventListener('input', () => {
    if (formSubmitted || rollNumberInput.closest('.form-group').classList.contains('has-error')) {
      validateRollNumber();
    }
  });

  emailInput.addEventListener('input', () => {
    if (formSubmitted || emailInput.closest('.form-group').classList.contains('has-error')) {
      validateEmail();
    }
  });

  courseSelect.addEventListener('change', () => {
    validateCourse();
  });

  passwordInput.addEventListener('input', () => {
    updatePasswordChecklist(passwordInput.value);
    if (formSubmitted || passwordInput.closest('.form-group').classList.contains('has-error')) {
      validatePassword();
    }
  });

  confirmPasswordInput.addEventListener('input', () => {
    if (formSubmitted || confirmPasswordInput.closest('.form-group').classList.contains('has-error')) {
      validateConfirmPassword();
    }
  });

  // ----------------------------------------------------------------------------
  // 9. Master Form Submission Handler
  // ----------------------------------------------------------------------------
  form.addEventListener('submit', (event) => {
    // Prevent the default browser page reload / HTTP form submission
    event.preventDefault();
    formSubmitted = true;

    // Execute all validation routines
    const isFullNameValid = validateFullName();
    const isRollNumberValid = validateRollNumber();
    const isEmailValid = validateEmail();
    const isCourseValid = validateCourse();
    const isPasswordValid = validatePassword();
    const isConfirmPasswordValid = validateConfirmPassword();
    const isDocumentValid = validateDocumentUpload();

    const isFormValid =
      isFullNameValid &&
      isRollNumberValid &&
      isEmailValid &&
      isCourseValid &&
      isPasswordValid &&
      isConfirmPasswordValid &&
      isDocumentValid;

    if (!isFormValid) {
      // Find and focus the first invalid field for optimal accessibility
      const firstInvalidField = form.querySelector('.form-group.has-error input, .form-group.has-error select');
      if (firstInvalidField) {
        firstInvalidField.focus();
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
      return;
    }

    // --------------------------------------------------------------------------
    // Successful Validation: Populate summary and display confirmation
    // --------------------------------------------------------------------------
    const uploadedFile = documentUploadInput.files[0];

    summaryFullName.textContent = fullNameInput.value.trim();
    summaryRollNumber.textContent = rollNumberInput.value.trim().toUpperCase();
    summaryEmail.textContent = emailInput.value.trim();
    summaryCourse.textContent = courseSelect.options[courseSelect.selectedIndex].text;
    summaryDocument.textContent = `${uploadedFile.name} (${formatFileSize(uploadedFile.size)})`;

    // Hide the input form and present the success message banner
    form.style.display = 'none';
    formHeader.style.display = 'none';
    successBanner.style.display = 'block';

    // Smooth scroll to top of registration card for visibility
    successBanner.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });

  // ----------------------------------------------------------------------------
  // 10. Form Reset & "Register Another Student" Handlers
  // ----------------------------------------------------------------------------
  function resetWholeForm() {
    form.reset();
    formSubmitted = false;

    // Reset password checklist rules
    [ruleLength, ruleUpper, ruleLower, ruleNumber, ruleSpecial].forEach(el => {
      if (el) el.classList.remove('rule-passed');
    });

    // Reset file preview
    hideFilePreview();

    // Clear all error & valid classes from all form-groups
    const allGroups = form.querySelectorAll('.form-group');
    allGroups.forEach(group => {
      group.classList.remove('has-error', 'is-valid');
    });

    // Clear error message texts
    const allErrors = form.querySelectorAll('.error-message');
    allErrors.forEach(err => {
      err.textContent = '';
    });

    // Clear aria-invalid attributes
    const allInputs = form.querySelectorAll('input, select');
    allInputs.forEach(input => {
      input.removeAttribute('aria-invalid');
    });

    // Reset password visibility fields back to type password
    passwordInput.type = 'password';
    confirmPasswordInput.type = 'password';
    [togglePasswordBtn, toggleConfirmPasswordBtn].forEach(btn => {
      if (btn) {
        const openIcon = btn.querySelector('.eye-open');
        const closedIcon = btn.querySelector('.eye-closed');
        if (openIcon) openIcon.style.display = 'block';
        if (closedIcon) closedIcon.style.display = 'none';
      }
    });

    // Show form, hide success banner
    form.style.display = 'flex';
    formHeader.style.display = 'block';
    successBanner.style.display = 'none';

    // Focus first input
    fullNameInput.focus();
  }

  resetBtn.addEventListener('click', resetWholeForm);
  registerAnotherBtn.addEventListener('click', resetWholeForm);

});
