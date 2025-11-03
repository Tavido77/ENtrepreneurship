<?php
// Include the database connection
include 'db_connect.php';

// Fetch testimonials from the database
$testimonials = [];
$result = $conn->query("SELECT name, feedback FROM testimonials ORDER BY id DESC LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

// Fetch services for the hero section
$services = [];
$result = $conn->query("SELECT id, name, description FROM services ORDER BY id ASC LIMIT 4");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home — Glow & Grace</title>
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
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="services.php">Services</a></li>
        <li><a href="booking.php">Booking</a></li>
        <li><a href="appointments.php">Appointments</a></li>
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
      <!-- Home (landing) -->
      <section id="home" class="hero-banner">
        <div class="hero-image" role="img" aria-label="Styling in salon">
          <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=1600&q=60" alt="Salon styling">
          <div class="hero-overlay"></div>
          <div class="hero-content">
            <span class="hero-pill">Luxury hair care for women</span>
            <h2 class="hero-title">Shine Brighter with Regal Glit Glam</h2>
            <p class="hero-lead">Experience premium, feminine styles with expert stylists. Book your service, book your stylist, and enjoy exclusive promos & events.</p>
            <div class="hero-actions">
              <button class="btn primary" id="hero-book" data-action="book">Book Appointment</button>
              <button class="btn outline" id="hero-view" data-action="view-services">View Services</button>
              <button class="btn ghost" id="hero-login" data-action="login">Login</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Features -->
      <section class="features">
        <h3>Why Choose Regal Glit Glam</h3>
        <p class="muted">Effortless booking, expert stylists, and premium treatments in a serene, welcoming space.</p>
        <div class="feature-list">
          <div class="feature-card">
            <div class="icon">✂️</div>
            <h4>Expert Stylists</h4>
            <p class="muted">From precision cuts to balayage, we have handcrafted styles for you.</p>
          </div>
          <div class="feature-card">
            <div class="icon">📅</div>
            <h4>Easy Booking</h4>
            <p class="muted">Choose your stylist, date, and time in seconds.</p>
          </div>
          <div class="feature-card">
            <div class="icon">💝</div>
            <h4>Exclusive Offers</h4>
            <p class="muted">Members enjoy seasonal packages and new-client perks.</p>
          </div>
        </div>
      </section>

      <!-- Testimonials -->
      <section class="testimonials" aria-label="Client testimonials">
        <h3>What Our Clients Say</h3>
        <div class="testimonial-track" id="testimonial-track">
          <?php if (!empty($testimonials)): ?>
            <?php foreach ($testimonials as $testimonial): ?>
              <blockquote class="t-card">
                <strong><?php echo htmlspecialchars($testimonial['name']); ?></strong>
                <p class="muted"><?php echo htmlspecialchars($testimonial['feedback']); ?></p>
              </blockquote>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No testimonials available at the moment.</p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Follow -->
      <section class="follow">
        <h3>Follow Our Glam</h3>
        <p class="muted">See styles, tutorials, and offers on your favorite platforms.</p>
        <div class="socials">
          <a href="#" aria-label="Facebook" class="social">Facebook</a>
          <a href="#" aria-label="Instagram" class="social">Instagram</a>
          <a href="#" aria-label="TikTok" class="social">TikTok</a>
        </div>
      </section>

      <!-- Bottom CTA -->
      <section class="bottom-cta">
        <div class="cta-inner">
          <div>
            <h4>Ready to glow?</h4>
            <p class="muted">Reserve your spot with our top stylists today.</p>
          </div>
          <div class="cta-actions">
            <button class="btn primary" id="bottom-book" data-action="book">Book Appointment</button>
            <button class="btn outline" id="bottom-view" data-action="view-services">View Services</button>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Toast -->
  <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

  <script src="app.js" defer></script>
</body>
</html>