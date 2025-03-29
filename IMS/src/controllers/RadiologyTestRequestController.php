<?php
$database = new Database();
$pdo = $database->connect();

$requestHandler = new RadiologyRequestHandler($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_test') {
    try {
        $requestData = [
            'patient_id' => $patientId,
            'category_id' => $_POST['category_id'],
            'subcategory_id' => $_POST['subcategory_id']
        ];

        if ($requestHandler->handleRequest($requestData)) {
            $successMessage = "Radiology test requested successfully!";
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

$categories = $requestHandler->fetchCategories();
$subcategories = $requestHandler->fetchSubcategories();
?>
