<?php
require_once __DIR__ . '/../../src/config/database.php';

class Consultation {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // Create a consultation
    public function createConsultation($patient_id, $doctor_id, $test_category_id, $notes) {
        $query = "INSERT INTO consultations 
                  (patient_id, doctor_id, test_category_id, notes) 
                  VALUES 
                  (:patient_id, :doctor_id, :test_category_id, :notes)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->bindParam(':doctor_id', $doctor_id);
        $stmt->bindParam(':test_category_id', $test_category_id);
        $stmt->bindParam(':notes', $notes);
        return $stmt->execute();
    }

    // Fetch consultations by patient
    public function getConsultationsByPatient($patient_id) {
        $query = "SELECT c.*, tc.category_name
                  FROM consultations c
                  JOIN test_categories tc ON c.test_category_id = tc.id
                  WHERE c.patient_id = :patient_id
                  ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':patient_id', $patient_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // (Optional) fetch by doctor, or fetch by status, etc.
}
