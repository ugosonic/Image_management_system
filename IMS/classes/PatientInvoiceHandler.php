<?php
/**

 * classes/PatientInvoiceHandler.php

 * This class handles:
 *  1. Fetching the currency sign from app_settings.
 *  2. Counting the total invoices (radiology_requests) for a patient.
 *  3. Fetching a paginated list of invoices.
 *
 *(OOP, Method Invocation, Storage):
 * - i used a constructor that accepts a PDO object.
 * - Dedicated methods to handle invoice logic.
 * - i did DB queries (storage) and return results.

 */

class PatientInvoiceHandler
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
     * Fetch the currency sign from app_settings table.
     * Returns '$' by default if not found.
     */
    public function fetchCurrencySign(): string
    {
        $defaultSign = '$';
        $stmt = $this->pdo->query("
            SELECT value
            FROM app_settings
            WHERE name='currency_sign'
            LIMIT 1
        ");
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['value'] ?? $defaultSign;
        }
        return $defaultSign;
    }

    /**
     * Count total invoices (radiology_requests) for a given patient.
     */
    public function countInvoices(int $patientId): int
    {
        $baseInvoiceSQL = "
            SELECT rr.id
            FROM radiology_requests rr
            JOIN test_categories tc     ON rr.category_id    = tc.id
            JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
            WHERE rr.patient_id = :pid
        ";
        $countSQL = "SELECT COUNT(*) AS total FROM ({$baseInvoiceSQL}) tmp";

        $stmt = $this->pdo->prepare($countSQL);
        $stmt->execute([':pid' => $patientId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Fetch a paginated list of invoices for a given patient.
     *
     * @param int $patientId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function fetchInvoices(int $patientId, int $limit, int $offset): array
    {
        $invoiceSQL = "
            SELECT rr.id,
                   rr.status,
                   rr.created_at,
                   COALESCE(tsc.price, tc.price, 0) AS price,
                   tc.category_name,
                   tsc.subcategory_name
            FROM radiology_requests rr
            JOIN test_categories tc   ON rr.category_id    = tc.id
            JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
            WHERE rr.patient_id = :pid
            ORDER BY rr.created_at DESC
            LIMIT :offset, :lim
        ";
        $stmt = $this->pdo->prepare($invoiceSQL);
        $stmt->bindValue(':pid',    $patientId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,    PDO::PARAM_INT);
        $stmt->bindValue(':lim',    $limit,     PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
