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

    <main>
      <div class="appointments-page">
        <div class="header">
          <input type="text" placeholder="Search appointments" class="search-bar">
          <h1>My Appointments</h1>
          <p>Manage upcoming visits, review past sessions, and make changes easily.</p>
          <div class="filters">
            <button class="filter-btn active">This Month</button>
            <button class="filter-btn">All Services</button>
          </div>
        </div>
        <div class="appointments-inner">
          <div class="appointments-left">
            <h2>Upcoming</h2>
            <?php if (!empty($upcomingAppointments)): ?>
              <?php foreach ($upcomingAppointments as $appointment): ?>
                <div class="appointment-card">
                  <div class="appointment-date"><?php echo htmlspecialchars($appointment['date'] . ', ' . $appointment['time']); ?></div>
                  <div class="appointment-details">
                    <strong><?php echo htmlspecialchars($appointment['service']); ?></strong>
                    <p>with <?php echo htmlspecialchars($appointment['stylist']); ?> • <?php echo htmlspecialchars($appointment['duration']); ?> mins</p>
                  </div>
                  <div class="appointment-actions">
                    <span class="badge confirmed">Confirmed</span>
                    <button class="btn-reschedule">Reschedule</button>
                    <button class="btn-cancel">Cancel</button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No upcoming appointments.</p>
            <?php endif; ?>
          </div>
          <div class="appointments-right">
            <h2>Past</h2>
            <?php if (!empty($pastAppointments)): ?>
              <?php foreach ($pastAppointments as $appointment): ?>
                <div class="appointment-card">
                  <div class="appointment-date"><?php echo htmlspecialchars($appointment['date'] . ', ' . $appointment['time']); ?></div>
                  <div class="appointment-details">
                    <strong><?php echo htmlspecialchars($appointment['service']); ?></strong>
                    <p>with <?php echo htmlspecialchars($appointment['stylist']); ?> • <?php echo htmlspecialchars($appointment['duration']); ?> mins</p>
                  </div>
                  <div class="appointment-status <?php echo htmlspecialchars($appointment['status']); ?>"><?php echo htmlspecialchars($appointment['status']); ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No past appointments.</p>
            <?php endif; ?>
          </div>
        </div>
        <div class="tip">
          Tip: You can reschedule up to 12 hours before your appointment.
        </div>
      </div>
    </main>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script src="app.js" defer></script>
</body>
</html>