<?php
// Include the database connection
include 'db_connect.php';

// Fetch contact details from the database
$contactDetails = [];
$result = $conn->query("SELECT address, phone, email, hours FROM contact_details LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $contactDetails = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact — Glow & Grace</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Sidebar (collapses on mobile) -->
  <aside class="sidebar" aria-label="Primary navigation">
    <div class="brand">
      <div class="logo">G&G</div>
      <div class="brand-text">Regal Glit Glam</div>
    </div>
    <nav>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
          <li><a href="booking.php">Booking</a></li>
        <?php endif; ?>
        <li><a href="appointments.php">Appointments</a></li>
        <li><a href="contact.php" class="active">Contact</a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <li><a href="dashboard.php">Dashboard</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <button class="sidebar-cta" aria-label="Open booking">Book Appointment</button>
  </aside>

  <!-- Top hamburger for mobile -->
  <button class="hamburger" aria-label="Toggle navigation" aria-expanded="false">
    ☰
  </button>

  <div class="main-area">
    <header class="topbar">
      <div class="search-wrap">
        <input id="search" type="search" placeholder="Search services, styles, or technicians" aria-label="Search services">
      </div>
      <div class="top-actions">
        <button class="login" aria-label="Login">Login</button>
        <button class="avatar" aria-label="User menu">A</button>
      </div>
    </header>

    <main>
      <!-- Contact page -->
      <section class="contact-page">
        <header class="page-header">
          <h2>Contact Us</h2>
          <p class="muted">Get in touch with our team for any questions or concerns.</p>
        </header>

        <!-- Contact details -->
        <div class="contact-details">
          <?php if (!empty($contactDetails)): ?>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($contactDetails['address']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($contactDetails['phone']); ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($contactDetails['email']); ?>"><?php echo htmlspecialchars($contactDetails['email']); ?></a></p>
            <p><strong>Hours:</strong> <?php echo htmlspecialchars($contactDetails['hours']); ?></p>
          <?php else: ?>
            <p>Contact details are currently unavailable. Please check back later.</p>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script src="app.js" defer></script>
</body>
</html>