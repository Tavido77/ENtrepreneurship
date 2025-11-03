<?php
// Include the database connection
include 'db_connect.php';

// Initialize variables for error messages
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate form data
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid Email is required.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    }

    // If no errors, check credentials
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                echo "<p>Login successful! Welcome, " . htmlspecialchars($user['full_name']) . "</p>";
            } else {
                $errors['password'] = 'Incorrect password.';
            }
        } else {
            $errors['email'] = 'No account found with this email.';
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
  <title>Login - Glow & Grace Salon</title>
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
      <p>Log in to manage your bookings and appointments</p>
    </div>

    <form method="POST" action="">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
        <?php if (isset($errors['email'])): ?>
          <div class="error-message"><?php echo $errors['email']; ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>
        <?php if (isset($errors['password'])): ?>
          <div class="error-message"><?php echo $errors['password']; ?></div>
        <?php endif; ?>
        <div class="remember-me">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember" style="font-weight: normal;">Remember me on this device</label>
        </div>
      </div>

      <div class="btn-row">
        <button type="submit" class="btn-primary">Login</button>
        <button type="button" class="btn-secondary" onclick="window.location.href='register.php'">Register</button>
      </div>
    </form>

    <div class="auth-footer">
      <p>Forgot your password? <a href="reset-password.php">Reset</a></p>
      <p style="margin-top:8px">By continuing, you agree to our <a href="#">Terms</a> and <a href="#">Privacy</a></p>
    </div>
  </div>
</body>
</html>