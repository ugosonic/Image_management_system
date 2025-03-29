<?php
require_once __DIR__ . '/../../config/database.php';

class Patient {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register($name, $title, $email, $phone, $address, $dob, $condition, $password) {
        // Generate random 7-digit patient_id
        $patient_id = $this->generateRandomId();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert query with escaped `condition`
        $query = "INSERT INTO patients (patient_id, name, title, email, phone, address, date_of_birth, `condition`, password)
                  VALUES (:patient_id, :name, :title, :email, :phone, :address, :dob, :condition, :password)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':condition', $condition);
        $stmt->bindParam(':password', $hashed_password);

        return $stmt->execute();
    }

    private function generateRandomId() {
        return str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT); // Ensure 7 digits
    }
}
?>
