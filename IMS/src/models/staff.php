<?php
require_once __DIR__ . '/../../config/database.php';

class Staff {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function register($name, $title, $email, $phone, $usergroup, $password) {
        // Generate random 7-digit staff_id
        $staff_id = $this->generateRandomId();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $query = "INSERT INTO staff_registration (staff_id, name, title, email, phone, usergroup, password)
                  VALUES (:staff_id, :name, :title, :email, :phone, :usergroup, :password)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':staff_id', $staff_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':usergroup', $usergroup);
        $stmt->bindParam(':password', $hashed_password);

        return $stmt->execute();
    }

    private function generateRandomId() {
        return str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT); // Ensure 7 digits
    }
}
?>
