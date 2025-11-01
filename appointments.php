<?php
// Include the database connection
include 'db_connect.php';

// Fetch upcoming appointments from the database
$upcomingAppointments = [];
$result = $conn->query("SELECT a.date, a.time, s.name AS service, st.name AS stylist, a.duration FROM appointments a JOIN services s ON a.service_id = s.id JOIN stylists st ON a.stylist_id = st.id WHERE a.date >= CURRENT_DATE() ORDER BY a.date, a.time");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $upcomingAppointments[] = $row;
    }
}

// Fetch past appointments from the database
$pastAppointments = [];
$result = $conn->query("SELECT a.date, a.time, s.name AS service, st.name AS stylist, a.duration, a.status FROM appointments a JOIN services s ON a.service_id = s.id JOIN stylists st ON a.stylist_id = st.id WHERE a.date < CURRENT_DATE() ORDER BY a.date DESC, a.time DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pastAppointments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Appointments — Glow & Grace</title>
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
        <li><a href="booking.php">Booking</a></li>
        <li><a href="appointments.php" class="active">Appointments</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="dashboard.php">Dashboard</a></li>
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
      <!-- Appointments page -->
      <div class="appointments-header">
        <div>
          <h1>My Appointments</h1>
          <p class="text-muted">Manage upcoming visits, review past sessions, and make changes easily.</p>
        </div>
        <div class="header-actions">
          <button class="btn-secondary">Profile</button>
          <button class="btn-primary">Appoint</button>
        </div>
      </div>

      <div class="filter-bar">
        <button class="filter-button">
          <span class="icon">📅</span>
          This month
        </button>
        <button class="filter-button">
          <span class="icon">✨</span>
          All services
        </button>
      </div>

      <p class="tip-text">Tip: You can reschedule up to 12 hours before your appointment</p>

      <div class="appointments-grid">
        <!-- Upcoming appointments -->
        <section class="appointments-section">
          <h2>Upcoming</h2>
          <?php if (!empty($upcomingAppointments)): ?>
            <?php foreach ($upcomingAppointments as $appointment): ?>
              <div class="appointment-card">
                <div class="appointment-info">
                  <div class="appointment-date"><?php echo htmlspecialchars($appointment['date'] . ', ' . $appointment['time']); ?></div>
                  <div class="appointment-service"><?php echo htmlspecialchars($appointment['service']); ?></div>
                  <div class="appointment-stylist">with <?php echo htmlspecialchars($appointment['stylist']); ?></div>
                  <div class="appointment-duration"><?php echo htmlspecialchars($appointment['duration']); ?> mins</div>
                </div>
                <div class="appointment-actions">
                  <button class="btn-confirmed">Confirmed</button>
                  <button class="btn-reschedule">Reschedule</button>
                  <button class="btn-cancel">Cancel</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No upcoming appointments.</p>
          <?php endif; ?>
        </section>

        <!-- Past appointments -->
        <section class="appointments-section">
          <h2>Past</h2>
          <?php if (!empty($pastAppointments)): ?>
            <?php foreach ($pastAppointments as $appointment): ?>
              <div class="appointment-card">
                <div class="appointment-info">
                  <div class="appointment-date"><?php echo htmlspecialchars($appointment['date'] . ', ' . $appointment['time']); ?></div>
                  <div class="appointment-service"><?php echo htmlspecialchars($appointment['service']); ?></div>
                  <div class="appointment-stylist">with <?php echo htmlspecialchars($appointment['stylist']); ?></div>
                  <div class="appointment-duration"><?php echo htmlspecialchars($appointment['duration']); ?> mins</div>
                </div>
                <div class="appointment-actions">
                  <?php if ($appointment['status'] === 'completed'): ?>
                    <span class="status-completed">Completed</span>
                  <?php elseif ($appointment['status'] === 'cancelled'): ?>
                    <span class="status-cancelled">Cancelled</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No past appointments.</p>
          <?php endif; ?>
        </section>
      </div>

      <p class="text-muted text-center mt-4">Looking for older records? View more in Settings → History</p>
    </main>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script src="app.js" defer></script>
</body>
</html>