<?php
// Simple PHP registration endpoint using mysqli
header('Content-Type: application/json; charset=utf-8');
// Allow CORS for development (adjust in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$fullName = trim($data['fullName'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';

// Basic server-side validation
if ($fullName === '' || $email === '' || $phone === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['error' => 'All fields (fullName, email, phone, password) are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 8 characters']);
    exit;
}

// DB config - common XAMPP defaults. Change as needed in production.
$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'glow_grace';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

// Create database if it doesn't exist (requires privileges)
if (!$mysqli->select_db($dbName)) {
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `" . $mysqli->real_escape_string($dbName) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    if (!$mysqli->query($createDbSql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create database: ' . $mysqli->error]);
        $mysqli->close();
        exit;
    }
    $mysqli->select_db($dbName);
}

// Create users table if it doesn't exist
$createTableSql = "CREATE TABLE IF NOT EXISTS `users` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `fullName` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(32) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$mysqli->query($createTableSql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create users table: ' . $mysqli->error]);
    $mysqli->close();
    exit;
}

// Check duplicate email
$stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Email already registered']);
    $stmt->close();
    $mysqli->close();
    exit;
}
$stmt->close();

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insert = $mysqli->prepare('INSERT INTO users (fullName, email, phone, password_hash) VALUES (?, ?, ?, ?)');
if (!$insert) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $mysqli->error]);
    $mysqli->close();
    exit;
}
$insert->bind_param('ssss', $fullName, $email, $phone, $passwordHash);
if (!$insert->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create user: ' . $insert->error]);
    $insert->close();
    $mysqli->close();
    exit;
}

$userId = $insert->insert_id;
$insert->close();
$mysqli->close();

http_response_code(201);
echo json_encode([
    'message' => 'Registration successful',
    'user' => ['id' => $userId, 'email' => $email, 'fullName' => $fullName]
]);
exit;

?>
