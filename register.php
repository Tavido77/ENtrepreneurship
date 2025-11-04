<?php
// Include the database connection
include 'db_connect.php';

// Initialize variables for error messages
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate form data
    if (empty($fullName)) {
        $errors['fullName'] = 'Full Name is required.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid Email is required.';
    }
    if (empty($phone)) {
        $errors['phone'] = 'Phone Number is required.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Passwords do not match.';
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullName, $email, $phone, $hashedPassword);

        if ($stmt->execute()) {
            echo "<p>Registration successful! <a href='login.php'>Login here</a>.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Glow & Grace Salon</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(180deg, #FFF6E9, #FCECEC);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      overflow: hidden;
    }

    .card {
      background: #FFFFFF;
      border-radius: 24px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
      padding: 32px;
      width: 100%;
      max-width: 400px;
      text-align: center;
      box-sizing: border-box;
    }

    .logo {
      width: 48px;
      height: 48px;
      margin: 0 auto;
      background: #FDE8EC;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 24px;
      color: #F6A6B5;
    }

    h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      color: #333333;
      margin: 16px 0 8px;
    }

    p.subtitle {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      color: #8C8C8C;
      margin-bottom: 24px;
    }

    .tabs {
      display: flex;
      justify-content: center;
      margin-bottom: 24px;
    }

    .tab {
      padding: 8px 16px;
      border-radius: 24px;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      cursor: pointer;
      margin: 0 8px;
    }

    .tab.active {
      background: #FDE8EC;
      color: #333333;
    }

    .tab.inactive {
      border: 1px solid #EAEAEA;
      color: #8C8C8C;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .form-group {
      position: relative;
    }

    .form-group input {
      width: calc(100% - 32px);
      height: 50px;
      border: 1px solid #E8E8E8;
      border-radius: 50px;
      padding: 0 16px 0 40px;
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      color: #333333;
      box-sizing: border-box;
    }

    .form-group input::placeholder {
      color: #A3A3A3;
    }

    .form-group .icon {
      position: absolute;
      top: 50%;
      left: 16px;
      transform: translateY(-50%);
      color: #A3A3A3;
    }

    .btn-primary {
      width: 100%;
      height: 48px;
      border-radius: 50px;
      background: #F6C6CB;
      color: #333333;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      border: none;
      cursor: pointer;
    }

    .btn-primary:hover {
      background: #F4B6C0;
    }

    .btn-secondary {
      width: 100%;
      height: 48px;
      border-radius: 50px;
      background: #FCE7D6;
      color: #333333;
      font-family: 'Poppins', sans-serif;
      font-weight: 500;
      border: none;
      cursor: pointer;
    }

    .tip {
      background: #FDE9E4;
      border-radius: 16px;
      padding: 16px;
      font-size: 13px;
      color: #7C6E6E;
      margin-top: 16px;
    }

    .footer {
      margin-top: 16px;
      font-size: 13px;
      color: #8C8C8C;
    }

    .footer a {
      color: #F6A6B5;
      text-decoration: none;
    }

    .footer a:hover {
      text-decoration: underline;
    }

    @media (max-width: 768px) {
      .card {
        padding: 24px;
        max-width: 90%;
      }

      h1 {
        font-size: 20px;
      }

      .form-group input {
        height: 45px;
        font-size: 14px;
      }

      .btn-primary, .btn-secondary {
        height: 45px;
        font-size: 14px;
      }
    }

    @media (max-width: 480px) {
      h1 {
        font-size: 18px;
      }

      .form-group input {
        height: 40px;
        font-size: 12px;
      }

      .btn-primary, .btn-secondary {
        height: 40px;
        font-size: 12px;
      }
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo">✂️</div>
    <h1>Glow & Grace Salon</h1>
    <p class="subtitle">Create your account or log in to manage bookings.</p>

    <div class="tabs">
      <div class="tab active" onclick="window.location.href='register.php'">Register</div>
      <div class="tab inactive" onclick="window.location.href='login.php'">Login</div>
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <span class="icon">👤</span>
        <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
        <?php if (isset($errors['fullName'])): ?>
          <div class="error-message"><?php echo $errors['fullName']; ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <span class="icon">✉️</span>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
        <?php if (isset($errors['email'])): ?>
          <div class="error-message"><?php echo $errors['email']; ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <span class="icon">📞</span>
        <input type="tel" id="phone" name="phone" placeholder="(xxx) xxx-xxxx" required>
        <?php if (isset($errors['phone'])): ?>
          <div class="error-message"><?php echo $errors['phone']; ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <span class="icon">🔒</span>
        <input type="password" id="password" name="password" placeholder="Enter password" required>
        <?php if (isset($errors['password'])): ?>
          <div class="error-message"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <span class="icon">🔒</span>
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
        <?php if (isset($errors['confirmPassword'])): ?>
          <div class="error-message"><?php echo $errors['confirmPassword']; ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn-primary">Register</button>
    </form>

    <div class="tip">
      Tip: Use a strong password with at least 8 characters, including a number and a symbol.
    </div>

    <div class="footer">
      By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy</a>.
    </div>
  </div>
</body>
</html>