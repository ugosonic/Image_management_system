<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();

        if ($this->conn === null) {
            error_log("Database connection failed in User class.");
        }
    }

    public function authenticate($email, $password) {
        try {
            $query = "
                SELECT id, usergroup, name, password
                FROM staff_registration 
                WHERE email = :email
                UNION
                SELECT patient_id AS id, 'Patient' AS usergroup, NULL AS name, password 
                FROM patients 
                WHERE email = :email
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Database error in authenticate: " . $e->getMessage());
            return false;
        }
    }
}
