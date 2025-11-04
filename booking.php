<?php
// Start the session
session_start();

// Check if the user is an admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Redirect admin users to the dashboard or another page
    header("Location: dashboard.php");
    exit;
}

// Include the database connection
include 'db_connect.php';

// Check if a service is already selected
$selectedServiceId = isset($_GET['service_id']) ? $_GET['service_id'] : null;

// Check if services are already selected
$selectedServiceIds = isset($_GET['service_ids']) ? explode(',', $_GET['service_ids']) : [];

// Fetch services from the database
$services = [];
$result = $conn->query("SELECT id, name, duration, price FROM services ORDER BY name ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Fetch stylists from the database
$stylists = [];
$result = $conn->query("SELECT id, name FROM stylists ORDER BY name ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $stylists[] = $row;
    }
}

// Fetch appointments dynamically based on the current date
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$appointments = [];
$result = $conn->query("SELECT time FROM appointments WHERE date = '$selectedDate'");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $time = $row['time'];
        if (!isset($appointments[$time])) {
            $appointments[$time] = 1;
        } else {
            $appointments[$time]++;
        }
    }
}

// Retrieve booked services from the database
$userId = $_SESSION['user_id']; // Assuming user ID is stored in session
$query = "SELECT services.name, services.duration, services.price, booked_services.booking_date FROM booked_services JOIN services ON booked_services.service_id = services.id WHERE booked_services.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$bookedServices = $result->fetch_all(MYSQLI_ASSOC);

// Determine busyness levels dynamically
function getBusynessLevel($count) {
    if ($count <= 2) return 'available';
    if ($count <= 5) return 'moderate';
    return 'busy';
}

// Helper function to build the updated service IDs query string
function buildServiceIdsQuery($currentIds, $serviceId, $action) {
    if ($action === 'add') {
        $currentIds[] = $serviceId;
    } elseif ($action === 'remove') {
        $currentIds = array_diff($currentIds, [$serviceId]);
    }
    return implode(',', $currentIds);
}

// Calculate total duration and selected services
$totalDuration = 0;
$selectedServices = [];
if (!empty($selectedServiceIds)) {
    foreach ($selectedServiceIds as $serviceId) {
        foreach ($services as $service) {
            if ($service['id'] == $serviceId) {
                $selectedServices[] = $service['name'];
                $totalDuration += $service['duration'];
            }
        }
    }
}

// Remove deselected services dynamically
if (isset($_GET['deselect_service_id'])) {
    $selectedServiceIds = array_diff($selectedServiceIds, [$_GET['deselect_service_id']]);
    header("Location: booking.php?service_ids=" . implode(',', $selectedServiceIds));
    exit;
}

// Filter services to exclude deselected ones
$filteredServices = array_filter($services, function($service) use ($selectedServiceIds) {
    return in_array($service['id'], $selectedServiceIds);
});

// If service_ids are passed in the query, update the selected services
if (isset($_GET['service_ids'])) {
    $selectedServiceIds = explode(',', $_GET['service_ids']);
    $filteredServices = array_filter($services, function($service) use ($selectedServiceIds) {
        return in_array($service['id'], $selectedServiceIds);
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f2f2;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            background-color: #fff;
            border-right: 1px solid #e1e1e1;
            padding: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #333;
            padding: 10px;
            display: block;
            border-radius: 5px;
        }

        .sidebar ul li a.active {
            background-color: #fbecec;
            color: #c2185b;
        }

        .main-area {
            padding: 20px;
        }

        .booking-page {
            display: flex;
            gap: 20px;
        }

        .booking-left {
            flex: 2;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .booking-right {
            flex: 1;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .steps {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .step-pill {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            background-color: #fbecec;
            color: #c2185b;
            font-weight: bold;
        }

        .step-pill.active {
            background-color: #c2185b;
            color: #fff;
        }

        .booking-section {
            margin-bottom: 20px;
        }

        .booking-section h3 {
            margin-bottom: 10px;
            color: #c2185b;
        }

        .booking-services ul, .stylists-list ul {
            list-style: none;
            padding: 0;
        }

        .booking-services ul li, .stylists-list ul li {
            padding: 10px;
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .summary-card h4 {
            margin-bottom: 10px;
            color: #c2185b;
        }

        .btn.primary {
            background-color: #c2185b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn.primary:hover {
            background-color: #a8124b;
        }

        .time-btn.available {
            background: #d4edda; /* Light green */
        }

        .time-btn.moderate {
            background: #fff3cd; /* Light yellow */
        }

        .time-btn.busy {
            background: #f8d7da; /* Light red */
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .calendar-header .month {
            font-weight: bold;
            font-size: 1.2em;
        }

        .calendar-header button {
            background: none;
            border: none;
            font-size: 1.2em;
            cursor: pointer;
            color: #c2185b;
        }

        .calendar-header button:hover {
            color: #a8124b;
        }

        .dates {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }

        .date-chip {
            text-align: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            transition: background 0.3s;
        }

        .date-chip:hover {
            opacity: 0.9;
        }

        .service-card {
            border: 1px solid #e1e1e1;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .select-btn {
            background-color: #c2185b;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .select-btn:hover {
            background-color: #a8124b;
        }

        .deselect-btn {
            background-color: #e1e1e1;
            color: #333;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .deselect-btn:hover {
            background-color: #c1c1c1;
        }

        .busyness-bar {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e1e1e1;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .busyness-bar h3 {
            margin-bottom: 15px;
            color: #c2185b;
            font-size: 1.2em;
            text-align: center;
        }

        .legend {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px;
            border-radius: 5px;
            background: #f9f9f9;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .legend-color {
            width: 25px;
            height: 25px;
            border-radius: 50%;
        }

        .step-section {
            margin-bottom: 100px; /* Increased space between sections */
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">
            <div class="logo">G&G</div>
            <div class="brand-text">Glow & Grace</div>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="services.php">Services</a></li>
                <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                  <li><a href="booking.php" class="active">Booking</a></li>
                <?php endif; ?>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <li><a href="dashboard.php">Dashboard</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <button class="sidebar-cta" aria-label="Open booking">Book Appointment</button>
    </div>
    <div class="main-area">
        <div class="topbar">
            <div class="search-wrap">
                <input id="search" type="search" placeholder="Search services, styles, or technicians" aria-label="Search services">
            </div>
            <div class="top-actions">
                <button class="login" aria-label="Login">Login</button>
                <button class="avatar" aria-label="User menu">A</button>
            </div>
        </div>
        <div class="booking-page">
            <div class="content-card">
                <div class="steps-navigation">
                    <div class="step active">1️⃣ Service</div>
                    <div class="step">2️⃣ Date</div>
                    <div class="step">3️⃣ Stylist</div>
                    <div class="step">4️⃣ Confirm</div>
                </div>
                <h1>Book an Appointment</h1>
                <p>Simple steps to reserve your time</p>
                <div class="step-section">
                    <h2>Step 1 – Selected services</h2>
                    <?php if (!empty($bookedServices)): ?>
                        <?php foreach ($bookedServices as $service): ?>
                            <div class="service-card">
                                <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                <p><?php echo htmlspecialchars($service['duration']); ?> min • $<?php echo number_format($service['price'], 2); ?></p>
                                <p>Booked on: <?php echo htmlspecialchars($service['booking_date']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No services booked.</p>
                    <?php endif; ?>
                </div>
                <div class="step-section">
                    <h2>Step 2 – Choose date and time</h2>
                    <div class="calendar-header">
                        <button class="prev-days">&lt;</button>
                        <div class="month">Current Week</div>
                        <button class="next-days">&gt;</button>
                        <hr>
                    </div>
                    <div class="dates">
                        <!-- Dates will be dynamically generated here -->
                    </div>
                    <div class="times">
                        <?php foreach ($appointments as $time => $count): ?>
                            <button class="time-btn <?php echo getBusynessLevel($count); ?>">
                                <?php echo $time; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="step-section">
                    <h2>Step 2A – Busyness</h2>
                    <div class="busyness-bar">
                        <h3>Busyness Levels</h3>
                        <div class="legend">
                          <div class="legend-item">
                            <span class="legend-color" style="background: #d4f1f9;"></span>
                            <span>Least Busy</span>
                          </div>
                          <div class="legend-item">
                            <span class="legend-color" style="background: #6ecae4;"></span>
                            <span>Moderately Busy</span>
                          </div>
                          <div class="legend-item">
                            <span class="legend-color" style="background: #003d4d;"></span>
                            <span>Most Busy</span>
                          </div>
                        </div>
                    </div>
                    <p>This week</p>
                </div>
                <div class="step-section">
                    <h2>Step 3 – Stylist (optional)</h2>
                    <?php if (!empty($stylists)): ?>
                        <?php foreach ($stylists as $stylist): ?>
                            <div class="stylist-card">
                                <img src="images/<?php echo htmlspecialchars(strtolower($stylist['name'])); ?>.jpg" alt="<?php echo htmlspecialchars($stylist['name']); ?>">
                                <strong><?php echo htmlspecialchars($stylist['name']); ?></strong>
                                <p>Precision cuts</p>
                                <button class="select-btn">Select</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No stylists available at the moment.</p>
                    <?php endif; ?>
                </div>
                <div class="step-section">
                    <h2>Step 4 – Payment</h2>
                    <button class="payment-option">Pay at salon</button>
                    <button class="payment-option">Card on file</button>
                </div>
            </div>
            <div class="booking-summary">
                <h4>Your booking</h4>
                <p>Services: <?php echo !empty($selectedServices) ? implode(', ', $selectedServices) : '—'; ?></p>
                <p>Duration: <?php echo $totalDuration > 0 ? $totalDuration . ' min' : '—'; ?></p>
                <p>Stylist: <?php echo isset($_GET['stylist']) ? htmlspecialchars($_GET['stylist']) : 'No preference'; ?></p>
                <p>Date: <?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '—'; ?></p>
                <p>Time: <?php echo isset($_GET['time']) ? htmlspecialchars($_GET['time']) : '—'; ?></p>
                <p>Total: $<?php echo number_format($totalDuration * 1.5, 2); ?></p>
                <span class="status-badge">Calm</span>
                <button class="confirm-btn">Confirm booking</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

    <script src="app.js"></script>
    <script>
      // Function to dynamically update the calendar with a 7-day view and navigation
      let currentStartDate = new Date();

      function update7DayCalendar() {
        const datesContainer = document.querySelector('.dates');
        const monthHeader = document.querySelector('.calendar-header .month');

        // Set the current month in the header
        const monthName = currentStartDate.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        monthHeader.textContent = monthName;

        // Clear previous dates
        datesContainer.innerHTML = '';

        // Generate 7 days starting from the current start date
        for (let i = 0; i < 7; i++) {
          const date = new Date(currentStartDate);
          date.setDate(currentStartDate.getDate() + i);

          const dayName = date.toLocaleDateString(undefined, { weekday: 'short' });
          const dayNumber = date.getDate();

          // Simulate busyness level for demonstration (replace with real data)
          const busynessLevel = Math.floor(Math.random() * 10); // Random value between 0 and 9

          const dateChip = document.createElement('div');
          dateChip.className = 'date-chip';
          dateChip.innerHTML = `${dayName}<br><strong>${dayNumber}</strong>`;
          dateChip.style.background = getBusynessColor(busynessLevel);
          dateChip.onclick = function() {
            window.location.href = `booking.php?date=${date.toISOString().split('T')[0]}`;
          };

          datesContainer.appendChild(dateChip);
        }
      }

      // Function to determine the color gradient based on busyness level
      function getBusynessColor(level) {
        const colors = [
          '#d4f1f9', // Cold (least busy)
          '#b2e4f2',
          '#90d7eb',
          '#6ecae4',
          '#4bbedd',
          '#29b1d6',
          '#1e91b0',
          '#146f89',
          '#0a4e63',
          '#003d4d'  // Warm (most busy)
        ];
        return colors[Math.min(level, colors.length - 1)];
      }

      // Add navigation for previous and next 7-day periods
      function navigateDays(direction) {
        currentStartDate.setDate(currentStartDate.getDate() + direction * 7);
        update7DayCalendar();
      }

      // Initial calendar setup
      document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('.prev-days').onclick = () => navigateDays(-1);
        document.querySelector('.next-days').onclick = () => navigateDays(1);
        update7DayCalendar();
      });
    </script>
</body>
</html>