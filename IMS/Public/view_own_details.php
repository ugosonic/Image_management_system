<?php
/**
 * view_own_details.php
 * For a patient to view their own info
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['patient_id']) || $_SESSION['usergroup'] !== 'Patient') {
    header('Location: login.php');
    exit();
}

$patientId = $_SESSION['patient_id'];

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

// Fetch patient row
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :pid LIMIT 1");
$stmt->execute([':pid' => $patientId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found in DB.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<?php include 'navbar.php'; ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto px-0">
      <?php include 'sidebar.php'; ?>
    </div>
    <div class="col p-4">
      <h1 class="text-2xl font-bold mb-4">My Details</h1>
      <div class="bg-white p-4 rounded shadow">
        <p><strong>Patient ID:</strong> <?php echo htmlspecialchars($patient['patient_id']); ?></p>
        <p><strong>Title:</strong> <?php echo htmlspecialchars($patient['title']); ?></p>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
        <p><strong>Condition:</strong> <?php echo htmlspecialchars($patient['condition']); ?></p>
      </div>
    </div>
  </div>
</div>
</body>
</html>
