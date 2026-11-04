/**
 * Student Registration Form Validation
 * Practical 5: Client-side Validation using Regular Expressions,
 * Password Strength Meter, and User-Friendly Error Handling
 */

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('registration-form');
  if (!form) return;

  const nameInput = document.getElementById('full_name');
  const emailInput = document.getElementById('email');
  const phoneInput = document.getElementById('phone');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const courseSelect = document.getElementById('course');
  const yearSelect = document.getElementById('year');
  const termsCheckbox = document.getElementById('terms');

  const meterBar = document.getElementById('password-meter-bar');
  const meterText = document.getElementById('password-meter-text');

  // Real-time password strength evaluation
  if (passwordInput && meterBar && meterText) {
    passwordInput.addEventListener('input', function () {
      const pwd = passwordInput.value;
      const strength = checkPasswordStrength(pwd);

      if (pwd.length === 0) {
        meterBar.style.width = '0%';
        meterBar.style.backgroundColor = 'var(--danger-color)';
        meterText.textContent = '';
      } else if (strength < 2) {
        meterBar.style.width = '33%';
        meterBar.style.backgroundColor = 'var(--danger-color)';
        meterText.textContent = 'Strength: Weak (add letters, numbers & min 6 chars)';
        meterText.style.color = 'var(--danger-color)';
      } else if (strength === 2 || strength === 3) {
        meterBar.style.width = '66%';
        meterBar.style.backgroundColor = 'var(--warning-color)';
        meterText.textContent = 'Strength: Medium (good, add special characters for strong)';
        meterText.style.color = 'var(--warning-color)';
      } else {
        meterBar.style.width = '100%';
        meterBar.style.backgroundColor = 'var(--success-color)';
        meterText.textContent = 'Strength: Strong password!';
        meterText.style.color = 'var(--success-color)';
      }
    });
  }

  function checkPasswordStrength(pwd) {
    let score = 0;
    if (pwd.length >= 6) score++;
    if (pwd.length >= 10) score++;
    if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
    if (/\d/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    return score;
  }

  // Real-time input listeners
  nameInput?.addEventListener('input', () => validateName(nameInput));
  emailInput?.addEventListener('input', () => validateEmail(emailInput));
  phoneInput?.addEventListener('input', () => validatePhone(phoneInput));
  confirmPasswordInput?.addEventListener('input', () => validateConfirmPassword(passwordInput, confirmPasswordInput));

  // Form submit handler
  form.addEventListener('submit', function (e) {
    let isValid = true;

    if (!validateName(nameInput)) isValid = false;
    if (!validateEmail(emailInput)) isValid = false;
    if (!validatePhone(phoneInput)) isValid = false;
    if (!validatePassword(passwordInput)) isValid = false;
    if (!validateConfirmPassword(passwordInput, confirmPasswordInput)) isValid = false;

    // Course validation
    if (courseSelect && courseSelect.value === '') {
      showError('course-error', 'Please select your course.');
      isValid = false;
    } else {
      hideError('course-error');
    }

    // Year validation
    if (yearSelect && yearSelect.value === '') {
      showError('year-error', 'Please select your academic year.');
      isValid = false;
    } else {
      hideError('year-error');
    }

    // Gender validation
    const genderChecked = document.querySelector('input[name="gender"]:checked');
    if (!genderChecked) {
      showError('gender-error', 'Please select your gender.');
      isValid = false;
    } else {
      hideError('gender-error');
    }

    // Terms checkbox validation
    if (termsCheckbox && !termsCheckbox.checked) {
      showError('terms-error', 'You must agree to the Terms and Conditions.');
      isValid = false;
    } else {
      hideError('terms-error');
    }

    if (!isValid) {
      e.preventDefault(); // Stop form submission if invalid
    }
  });

  function validateName(input) {
    if (!input) return true;
    const val = input.value.trim();
    const nameRegex = /^[A-Za-z\s]{3,50}$/;
    if (!nameRegex.test(val)) {
      showError('name-error', 'Name must contain only letters (at least 3 characters).');
      return false;
    }
    hideError('name-error');
    return true;
  }

  function validateEmail(input) {
    if (!input) return true;
    const val = input.value.trim();
    // Standard RFC-compliant basic email regular expression
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(val)) {
      showError('email-error', 'Please enter a valid email address (e.g., student@domain.com).');
      return false;
    }
    hideError('email-error');
    return true;
  }

  function validatePhone(input) {
    if (!input) return true;
    const val = input.value.trim();
    // 10 digit Indian mobile number starting with 6, 7, 8, or 9
    const phoneRegex = /^[6-9]\d{9}$/;
    if (!phoneRegex.test(val)) {
      showError('phone-error', 'Enter a valid 10-digit mobile number starting with 6-9.');
      return false;
    }
    hideError('phone-error');
    return true;
  }

  function validatePassword(input) {
    if (!input) return true;
    const val = input.value;
    if (val.length < 6) {
      showError('password-error', 'Password must be at least 6 characters long.');
      return false;
    }
    hideError('password-error');
    return true;
  }

  function validateConfirmPassword(passInput, confirmInput) {
    if (!passInput || !confirmInput) return true;
    if (confirmInput.value !== passInput.value || confirmInput.value === '') {
      showError('confirm-error', 'Passwords do not match.');
      return false;
    }
    hideError('confirm-error');
    return true;
  }

  function showError(elementId, message) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
      errorEl.textContent = message;
      errorEl.classList.add('visible');
    }
  }

  function hideError(elementId) {
    const errorEl = document.getElementById(elementId);
    if (errorEl) {
      errorEl.textContent = '';
      errorEl.classList.remove('visible');
    }
  }
});

