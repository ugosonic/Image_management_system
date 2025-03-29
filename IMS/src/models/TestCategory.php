<?php
require_once __DIR__ . './config/database.php';

class TestCategory {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Create a new test category
    public function createCategory($category_name) {
        $query = "INSERT INTO test_categories (category_name) VALUES (:category_name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_name', $category_name);
        return $stmt->execute();
    }

    // Get all test categories
    public function getAllCategories() {
        $query = "SELECT * FROM test_categories";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
