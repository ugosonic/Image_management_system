<?php
/**
 * =============================================================================
 * classes/PatientDashboardHandler.php
 * =============================================================================
 * This class handles all functionality needed on the patient dashboard:
 *  1. Fetching currency sign from `app_settings`.
 *  2. Calculating outstanding amount for a given patient.
 *  3. Fetching all radiology test requests.
 *
 * (OOP, Method Invocation, Storage, Interaction):
 *  - i use a constructor that accepts a PDO object.
 *  - i have dedicated methods for each database operation.
 *  - i do selection/queries (interaction with DB).
 *  - i store/retrieve data from the DB (storage).
 * =============================================================================
 */

class PatientDashboardHandler
{
    private PDO $pdo;

    /**
     * Constructor accepts a PDO instance.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Fetch the currency sign from app_settings table.
     * Default to '$' if not found.
     *
     * @return string
     */
    public function fetchCurrencySign(): string
    {
        $defaultSign = '$';
        $settingsStmt = $this->pdo->query("
            SELECT value 
            FROM app_settings 
            WHERE name='currency_sign' 
            LIMIT 1
        ");
        if ($settingsRow = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
            return $settingsRow['value'];
        }
        return $defaultSign;
    }

    /**
     * Calculate total outstanding (unpaid) amount for a given patient.
     *
     * @param int $patientId
     * @return float
     */
    public function calculateOutstanding(int $patientId): float
    {
        $query = "
            SELECT SUM(tsc.price) AS total_cost
            FROM radiology_requests rr
            JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
            WHERE rr.patient_id = :pid
              AND rr.status IN ('Pending','PendingPayment')
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':pid' => $patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($row && $row['total_cost']) ? (float)$row['total_cost'] : 0.0;
    }

    /**
     * Fetch all radiology test requests for a given patient.
     *
     * @param int $patientId
     * @return array
     */
    public function fetchTestRequests(int $patientId): array
    {
        $query = "
            SELECT rr.id, rr.status, rr.created_at,
                   tc.category_name,
                   tsc.subcategory_name,
                   tsc.price
            FROM radiology_requests rr
            JOIN test_categories tc ON rr.category_id = tc.id
            JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
            WHERE rr.patient_id = :pid
            ORDER BY rr.created_at DESC
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([':pid' => $patientId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
