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
            background-color: #fdf5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            background-color: #fff;
            border-right: 1px solid #eaeaea;
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
            background-color: #fce4ec;
            color: #d81b60;
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
            background-color: #fce4ec;
            color: #d81b60;
            font-weight: bold;
        }

        .step-pill.active {
            background-color: #d81b60;
            color: #fff;
        }

        .booking-section {
            margin-bottom: 20px;
        }

        .booking-section h3 {
            margin-bottom: 10px;
            color: #d81b60;
        }

        .booking-services ul, .stylists-list ul {
            list-style: none;
            padding: 0;
        }

        .booking-services ul li, .stylists-list ul li {
            padding: 10px;
            border: 1px solid #eaeaea;
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
            color: #d81b60;
        }

        .btn.primary {
            background-color: #d81b60;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn.primary:hover {
            background-color: #c2185b;
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
            <div class="booking-inner">
                <div class="booking-left">
                    <div class="steps">
                        <div class="step-pill active">1 Service</div>
                        <div class="step-pill">2 Date</div>
                        <div class="step-pill">3 Stylist</div>
                        <div class="step-pill">4 Confirm</div>
                    </div>
                    <div class="booking-section booking-services">
                        <h2>Step 1 - Select a service</h2>
                        <?php if (!empty($services)): ?>
                            <ul>
                                <?php foreach ($services as $service): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($service['name']); ?></strong> - <?php echo htmlspecialchars($service['duration']); ?> mins - ₵<?php echo number_format($service['price'], 2); ?>
                                        <button class="btn primary">Select</button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No services available at the moment.</p>
                        <?php endif; ?>
                    </div>
                    <div class="booking-section">
                        <h2>Step 2 - Choose date and time</h2>
                        <div class="date-list">
                            <?php if (!empty($dates)): ?>
                                <?php foreach ($dates as $date): ?>
                                    <div class="date-item <?php echo ($date['is_busy']) ? 'busy' : ''; ?>">
                                        <span><?php echo htmlspecialchars($date['date']); ?></span>
                                        <span class="busy-indicator"><?php echo $date['num_bookings']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No available dates at the moment.</p>
                            <?php endif; ?>
                        </div>
                        <div id="time-slots" class="time-slots">
                            <?php if (!empty($times)): ?>
                                <?php foreach ($times as $time): ?>
                                    <div class="time-item <?php echo ($time['is_busy']) ? 'busy' : ''; ?>">
                                        <span><?php echo htmlspecialchars($time['time']); ?></span>
                                        <span class="busy-indicator"><?php echo $time['num_bookings']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No available times at the moment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="booking-section stylists-list">
                        <h2>Step 3 - Stylist (optional)</h2>
                        <?php if (!empty($stylists)): ?>
                            <ul>
                                <?php foreach ($stylists as $stylist): ?>
                                    <li>
                                        <div class="stylist-card">
                                            <img src="images/<?php echo htmlspecialchars(strtolower($stylist['name'])); ?>.jpg" alt="<?php echo htmlspecialchars($stylist['name']); ?>">
                                            <div>
                                                <strong><?php echo htmlspecialchars($stylist['name']); ?></strong>
                                                <p class="meta">Precision cuts</p>
                                            </div>
                                            <button>Select</button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No stylists available at the moment.</p>
                        <?php endif; ?>
                    </div>
                    <div class="booking-section">
                        <h2>Step 4 - Payment</h2>
                        <div class="controls">
                            <button class="chip">Pay at salon</button>
                            <button class="chip">Card on file</button>
                        </div>
                    </div>
                </div>
                <div class="booking-right">
                    <div class="summary-card">
                        <div class="summary-head">
                            <h4>Your booking</h4>
                            <span>Calm</span>
                        </div>
                        <div class="summary-body">
                            <p>Service: —</p>
                            <p>Duration: —</p>
                            <p>Stylist: No preference</p>
                            <p>Date: —</p>
                            <p>Time: —</p>
                        </div>
                        <div class="summary-total">
                            <strong>Total: ₵0.00</strong>
                        </div>
                        <button id="confirm-booking" class="btn primary">Confirm booking</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast" role="status" aria-live="polite" aria-atomic="true"></div>

    <script src="app.js"></script>
</body>
</html>