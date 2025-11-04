<?php
// Include the database connection
include 'db_connect.php';
require_once 'db_queries.php';

$dbQueries = new DBQueries($conn);

// Fetch services from the database
$services = [];
$sql = "SELECT id, name, description, price, category, duration FROM services ORDER BY price ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Handle service booking
if (isset($_POST['book_service_id'])) {
    $serviceId = $_POST['book_service_id'];

    // Assuming $userId is the logged-in user's ID
    $userId = $_SESSION['user_id'] ?? null;

    if ($userId) {
        if ($dbQueries->bookService($serviceId, $userId)) {
            header("Location: booking.php");
            exit;
        } else {
            echo "Error: Unable to book the service.";
        }
    } else {
        echo "Error: User not logged in.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Services — Glow & Grace</title>
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
        <li><a href="services.php" class="active">Services</a></li>
        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
          <li><a href="booking.php">Booking</a></li>
        <?php endif; ?>
        <li><a href="appointments.php">Appointments</a></li>
        <li><a href="contact.php">Contact</a></li>
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
      <!-- Services listing -->
      <section class="controls" aria-label="Filters and sorting">
        <header class="page-header">
          <h2>Services</h2>
          <p class="muted">Discover our most-loved styles and treatments.</p>
        </header>
        <div class="chips" role="list">
          <button class="chip" data-tag="braiding">Braiding</button>
          <button class="chip" data-tag="wig">Wig Install</button>
          <button class="chip" data-tag="color">Coloring</button>
          <button class="chip" data-tag="manicure">Manicure</button>
          <button class="chip clear" id="clear-filters">Clear filters</button>
        </div>

        <div class="controls-right">
          <label class="select">
            <select id="category-select" aria-label="Category select">
              <option>All types</option>
              <option>Braids</option>
              <option>Wigs</option>
              <option>Color</option>
            </select>
          </label>

          <label class="select">
            <select id="sort-select" aria-label="Sort services">
              <option value="asc">Price: Low to High</option>
              <option value="desc">Price: High to Low</option>
            </select>
          </label>
        </div>
      </section>

      <section class="grid" id="services-grid" aria-live="polite">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $service): ?>
            <div class="card">
              <h3><?php echo htmlspecialchars($service['name']); ?></h3>
              <p><?php echo htmlspecialchars($service['description']); ?></p>
              <p><strong>Price:</strong> $<?php echo number_format($service['price'], 2); ?></p>
              <p><strong>Category:</strong> <?php echo htmlspecialchars($service['category']); ?></p>
              <p><strong>Duration:</strong> <?php echo htmlspecialchars($service['duration']); ?> min</p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No services available at the moment.</p>
        <?php endif; ?>
      </section>

      <div class="services-list">
        <?php foreach ($services as $service): ?>
          <div class="service-card">
            <strong><?php echo htmlspecialchars($service['name']); ?></strong>
            <p><?php echo htmlspecialchars($service['duration']); ?> min • $<?php echo number_format($service['price'], 2); ?></p>
            <form method="POST" action="">
              <input type="hidden" name="book_service_id" value="<?php echo $service['id']; ?>">
              <button type="submit" class="book-now-btn">Book Now</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script src="app.js" defer></script>
</body>
</html>