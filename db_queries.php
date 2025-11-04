<?php
// Database Queries Handler
require_once 'db_connect.php';

class DBQueries {
    private $conn;

    public function __construct($connection) {
        $this->conn = $connection;
    }

    // Fetch all services
    public function getAllServices() {
        $query = "SELECT * FROM services";
        $result = $this->conn->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Insert a booked service
    public function bookService($serviceId, $userId) {
        $stmt = $this->conn->prepare("INSERT INTO booked_services (service_id, user_id, booking_date) VALUES (?, ?, NOW())");
        $stmt->bind_param("ii", $serviceId, $userId);
        return $stmt->execute();
    }

    // Fetch booked services for a user
    public function getBookedServices($userId) {
        $query = "SELECT services.name, services.duration, services.price, booked_services.booking_date FROM booked_services JOIN services ON booked_services.service_id = services.id WHERE booked_services.user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Usage example:
// $dbQueries = new DBQueries($conn);
// $services = $dbQueries->getAllServices();
?>