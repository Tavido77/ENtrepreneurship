<?php
// Database connection script

$host = "localhost";
$db_name = "entrepreneurship_db"; // Replace with your database name
$username = "root";
$password = ""; // Default password for XAMPP is empty

// Create connection
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>