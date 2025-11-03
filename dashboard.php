<?php
// Include the database connection
include 'db_connect.php';

// Fetch statistics from the database
$stats = [
    'total_sales' => 0,
    'bookings_this_week' => 0,
    'avg_order_value' => 0,
    'returning_clients' => 0
];

// Fetch total sales
$result = $conn->query("SELECT SUM(price) AS total_sales FROM bookings");
if ($result && $row = $result->fetch_assoc()) {
    $stats['total_sales'] = $row['total_sales'] ?? 0;
}

// Fetch bookings this week
$result = $conn->query("SELECT COUNT(*) AS bookings_this_week FROM bookings WHERE WEEK(created_at) = WEEK(CURRENT_DATE())");
if ($result && $row = $result->fetch_assoc()) {
    $stats['bookings_this_week'] = $row['bookings_this_week'] ?? 0;
}

// Fetch average order value
$result = $conn->query("SELECT AVG(price) AS avg_order_value FROM bookings");
if ($result && $row = $result->fetch_assoc()) {
    $stats['avg_order_value'] = $row['avg_order_value'] ?? 0;
}

// Fetch returning clients percentage
$result = $conn->query("SELECT (COUNT(DISTINCT user_id) / COUNT(*)) * 100 AS returning_clients FROM bookings");
if ($result && $row = $result->fetch_assoc()) {
    $stats['returning_clients'] = $row['returning_clients'] ?? 0;
}

// Fetch popular services
$popular_services = [];
$result = $conn->query("SELECT services.name, COUNT(bookings.id) AS count FROM bookings JOIN services ON bookings.service_id = services.id GROUP BY services.name ORDER BY count DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $popular_services[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard — Glow & Grace</title>
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
        <li><a href="appointments.php">Appointments</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="dashboard.php" class="active">Dashboard</a></li>
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
      <!-- Dashboard page -->
      <section class="dashboard-page">
        <header class="page-header">
          <h2>Admin Dashboard</h2>
          <p class="muted">Business overview and performance</p>
        </header>

        <div class="stats-row">
          <div class="stat-card">
            <div class="stat-label">Total sales</div>
            <div class="stat-value" id="stat-sales">₵<?php echo number_format($stats['total_sales'], 2); ?></div>
            <div class="stat-delta">+12%</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Bookings this week</div>
            <div class="stat-value" id="stat-bookings"><?php echo $stats['bookings_this_week']; ?></div>
            <div class="stat-delta">+8%</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Avg. order value</div>
            <div class="stat-value" id="stat-average">₵<?php echo number_format($stats['avg_order_value'], 2); ?></div>
            <div class="stat-delta">+3%</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Returning clients</div>
            <div class="stat-value" id="stat-returning"><?php echo number_format($stats['returning_clients'], 2); ?>%</div>
            <div class="stat-delta">+5%</div>
          </div>
        </div>

        <div class="charts-row">
          <div class="chart-large">Line chart placeholder</div>
          <div class="chart-small">Pie chart placeholder</div>
        </div>

        <section class="popular-services">
          <h3>Most popular services</h3>
          <div class="popular-list" id="popular-list">
            <?php if (!empty($popular_services)): ?>
              <ul>
                <?php foreach ($popular_services as $service): ?>
                  <li><?php echo htmlspecialchars($service['name']); ?> (<?php echo $service['count']; ?> bookings)</li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <p>No popular services available.</p>
            <?php endif; ?>
          </div>
        </section>
      </section>
    </main>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script src="app.js" defer></script>
</body>
</html>