// Check if already logged in
if (localStorage.getItem('token')) {
  window.location.href = 'appointments.html';
}

document.getElementById('login-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  // Reset error messages
  document.querySelectorAll('.error-message').forEach(el => {
    el.style.display = 'none';
    el.textContent = '';
  });

  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  const remember = document.getElementById('remember').checked;

  try {
    const response = await fetch('http://localhost:3000/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email,
        password,
        remember
      }),
    });

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Invalid email or password');
    }

    // Store the token and user data
    localStorage.setItem('token', data.token);
    localStorage.setItem('userData', JSON.stringify({
      email: data.email,
      fullName: data.fullName,
      role: data.role || 'customer' // Default to customer if no role specified
    }));
    
    // Redirect based on role
    if (data.role === 'admin') {
      window.location.href = 'dashboard.html';
    } else {
      window.location.href = 'appointments.html';
    }
  } catch (error) {
    // Show error in password field as it's a login error
    const errorEl = document.getElementById('password-error');
    errorEl.textContent = error.message;
    errorEl.style.display = 'block';
  }
});