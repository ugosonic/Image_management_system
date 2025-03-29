<?php
// delete_image.php
session_start();

// If needed, verify user is authorized to delete images (e.g., Radiologist or Doctor)
if (!isset($_SESSION['user_id']) && !isset($_SESSION['patient_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

// Expecting `image_id` in the request
if (!isset($_POST['image_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No image ID specified']);
    exit();
}

$imageId = $_POST['image_id'];

// 1. Fetch the image row (optional, to confirm existence or check who uploaded it)
$stmt = $pdo->prepare("SELECT image_path FROM radiology_images WHERE id = :id");
$stmt->execute([':id' => $imageId]);
$imageRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$imageRow) {
    http_response_code(404);
    echo json_encode(['error' => 'Image not found']);
    exit();
}

// 2. Delete from the database
$deleteStmt = $pdo->prepare("DELETE FROM radiology_images WHERE id = :id");
$deleteStmt->execute([':id' => $imageId]);

// 3. Optionally delete the physical file
$imagePath = $imageRow['image_path']; // e.g. "uploads/category_3/skull.jpg"
$absolutePath = __DIR__ . '/../../' . $imagePath;
if (file_exists($absolutePath)) {
    unlink($absolutePath);
}

echo json_encode(['success' => true]);
exit();
