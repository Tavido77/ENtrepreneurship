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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-header">
      <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
        <path d="M20 40c11.046 0 20-8.954 20-20S31.046 0 20 0 0 8.954 0 20s8.954 20 20 20z" fill="#FF9FB7"/>
        <path d="M20 28c4.97 0 9-3.582 9-8s-4.03-8-9-8-9 3.582-9 8 4.03 8 9 8z" fill="#FFF"/>
      </svg>
      <h1>Glow & Grace Salon</h1>
      <p>Create your account or log in to manage bookings</p>
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <label for="fullName">Full Name</label>
        <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
        <?php if (isset($errors['fullName'])): ?>
          <div class="error-message"><?php echo $errors['fullName']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
        <?php if (isset($errors['email'])): ?>
          <div class="error-message"><?php echo $errors['email']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="(xxx) xxx-xxxx" required>
        <?php if (isset($errors['phone'])): ?>
          <div class="error-message"><?php echo $errors['phone']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>
        <?php if (isset($errors['password'])): ?>
          <div class="error-message"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-enter password" required>
        <?php if (isset($errors['confirmPassword'])): ?>
          <div class="error-message"><?php echo $errors['confirmPassword']; ?></div>
        <?php endif; ?>
      </div>

      <div class="password-tip">
        Tip: Use a strong password with at least 8 characters, including a number and a symbol.
      </div>

      <div class="btn-row">
        <button type="submit" class="btn-primary">Register</button>
        <button type="button" class="btn-secondary" onclick="window.location.href='login.php'">Login</button>
      </div>
    </form>

    <div class="auth-footer">
      <p>Forgot your password? <a href="reset-password.php">Reset</a></p>
      <p style="margin-top:8px">By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy</a></p>
    </div>
  </div>
</body>
</html>