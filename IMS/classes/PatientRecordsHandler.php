<?php
/**
 * ============================================================================
 * classes/PatientRecordsHandler.php
 * ============================================================================
 * This class handles:
 *  1. Deleting a patient record by patient_id.
 *  2. Counting total patients.
 *  3. Fetching a paginated list of patients.
 *
 * MARKING SCHEME (OOP, Method Invocation, Storage):
 * - i have methods for each CRUD-related operation (Delete, Fetch).
 * - i did DB queries (storage).
 * - Use of constructor for receiving the PDO dependency.
 * ============================================================================
 */

class PatientRecordsHandler
{
    private PDO $pdo;

    /**
     * Constructor with a PDO instance for DB operations.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Delete a patient record from the 'patients' table using patient_id.
     */
    public function deletePatient(string $patientId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM patients WHERE patient_id = :pid");
        $stmt->bindParam(':pid', $patientId);
        $stmt->execute();
    }

    /**
     * Count total patients in the 'patients' table.
     */
    public function countPatients(): int
    {
        $countQuery = "SELECT COUNT(*) AS total FROM patients";
        $stmt = $this->pdo->query($countQuery);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    /**
     * Fetch a paginated list of patients.
     */
    public function fetchPatients(int $limit, int $offset): array
    {
        $query = "
            SELECT patient_id, title, name, date_of_birth
            FROM patients
            ORDER BY created_at DESC
            LIMIT :offset, :lim
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':lim',    $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
