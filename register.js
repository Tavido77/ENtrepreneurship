// Client-side validation and API integration for registration
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('register-form');
  // Disable native browser validation; we'll use custom validators to show friendly messages
  form.setAttribute('novalidate', 'true');
  const inputs = {
    fullName: document.getElementById('fullName'),
    email: document.getElementById('email'),
    phone: document.getElementById('phone'),
    password: document.getElementById('password'),
    confirmPassword: document.getElementById('confirmPassword')
  };

  // Format phone number as user types
  inputs.phone.addEventListener('input', (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 0) {
      if (value.length <= 3) {
        value = `(${value}`;
      } else if (value.length <= 6) {
        value = `(${value.slice(0,3)}) ${value.slice(3)}`;
      } else {
        value = `(${value.slice(0,3)}) ${value.slice(3,6)}-${value.slice(6,10)}`;
      }
    }
    e.target.value = value;
  });

  // Show/hide validation errors
  function showError(field, message) {
    const error = document.getElementById(`${field}-error`);
    error.textContent = message;
    error.style.display = 'block';
    inputs[field].classList.add('error');
  }

  function clearError(field) {
    const error = document.getElementById(`${field}-error`);
    error.style.display = 'none';
    inputs[field].classList.remove('error');
  }

  // Validation rules
  const validators = {
    fullName: (value) => {
      if (value.length < 2) return 'Name must be at least 2 characters';
      if (!/^[a-zA-Z\s]*$/.test(value)) return 'Name can only contain letters and spaces';
      return null;
    },
    email: (value) => {
      value = (value || '').trim();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Please enter a valid email address';
      return null;
    },
    phone: (value) => {
      // Accept either formatted (123) 456-7890 or plain 10 digits
      const digits = (value || '').replace(/\D/g, '');
      if (!(/^\(\d{3}\)\s\d{3}-\d{4}$/.test(value) || /^\d{10}$/.test(digits))) return 'Please enter a valid phone number';
      return null;
    },
    password: (value) => {
      if (value.length < 8) return 'Password must be at least 8 characters';
      if (!/\d/.test(value)) return 'Password must include a number';
      if (!/[!@#$%^&*]/.test(value)) return 'Password must include a symbol';
      return null;
    },
    confirmPassword: (value) => {
      if (value !== inputs.password.value) return 'Passwords do not match';
      return null;
    }
  };

  // Live validation on input
  Object.keys(inputs).forEach(field => {
    inputs[field].addEventListener('input', () => {
      const error = validators[field]?.(inputs[field].value);
      if (error) {
        showError(field, error);
      } else {
        clearError(field);
      }
    });
  });

  // Form submission
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    let hasErrors = false;

    // Validate all fields
    Object.keys(inputs).forEach(field => {
      const error = validators[field]?.(inputs[field].value);
      if (error) {
        showError(field, error);
        hasErrors = true;
      } else {
        clearError(field);
      }
    });

    if (hasErrors) return;

    // Prepare payload
    const payload = {
      fullName: inputs.fullName.value,
      email: inputs.email.value,
      phone: inputs.phone.value.replace(/\D/g, ''),
      password: inputs.password.value
    };

    try {
      const res = await fetch('/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.error || 'Registration failed');
      }

      // Success - store token and redirect
      localStorage.setItem('token', data.token);
      window.location.href = 'index.html';

    } catch (err) {
      showError('email', err.message);
    }
  });
});