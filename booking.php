<?php
// Include the database connection
include 'db_connect.php';

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

// Fetch appointments for the selected date
$selectedDate = '2025-10-28'; // Example date, replace with dynamic date
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

// Determine busyness levels
function getBusynessLevel($count) {
    if ($count <= 2) return 'available';
    if ($count <= 5) return 'moderate';
    return 'busy';
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
                <li><a href="booking.php" class="active">Booking</a></li>
                <li><a href="appointments.php">Appointments</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
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
                    <h2>Step 1 – Select a service</h2>
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <div class="service-card">
                                <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                <p><?php echo htmlspecialchars($service['duration']); ?> min • <?php echo htmlspecialchars($service['name']); ?></p>
                                <div class="price">$<?php echo number_format($service['price'], 2); ?></div>
                                <button class="select-btn">Select</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No services available at the moment.</p>
                    <?php endif; ?>
                </div>
                <div class="step-section">
                    <h2>Step 2 – Choose date and time</h2>
                    <div class="calendar-header">
                        <div class="month">October 2025</div>
                        <hr>
                    </div>
                    <div class="dates">
                        <div class="date-chip">Mon<br><strong>27</strong></div>
                        <div class="date-chip">Tue<br><strong>28</strong></div>
                        <div class="date-chip">Wed<br><strong>29</strong></div>
                        <div class="date-chip">Thu<br><strong>30</strong></div>
                        <div class="date-chip">Fri<br><strong>31</strong></div>
                        <div class="date-chip">Sat<br><strong>1</strong></div>
                        <div class="date-chip">Sun<br><strong>2</strong></div>
                    </div>
                    <div class="legend">
                        <span class="free">🟢 Free</span>
                        <span class="moderate">🟡 Moderate</span>
                        <span class="busy">🔴 Busy</span>
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
                        <!-- Busyness levels -->
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
                <p>Service: —</p>
                <p>Duration: —</p>
                <p>Stylist: No preference</p>
                <p>Date: —</p>
                <p>Time: —</p>
                <p>Total: $0.00</p>
                <span class="status-badge">Calm</span>
                <button class="confirm-btn">Confirm booking</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

    <script src="app.js"></script>
</body>
</html>