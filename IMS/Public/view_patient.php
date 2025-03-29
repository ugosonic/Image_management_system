<?php

if (!isset($_GET['patient_id'])) {
    die("No patient specified.");
}

// Include your DB config
require_once __DIR__ . '../../config/database.php';

// (A) CONNECT TO DB
$database = new Database();
$pdo = $database->connect();

// (B) FETCH PATIENT
$patientId = $_GET['patient_id'];
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = :pid");
$stmt->bindParam(':pid', $patientId);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Patient Record</title>
    <!-- Bootstrap 5 -->
    <link 
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
</head>
<body class="bg-light">

<?php include 'navbar.php'; ?>
  <div class="container-fluid">
    <div class="row">
      <div class="col-auto px-0">
        <?php include 'sidebar.php'; ?>
      </div>
      <div class="col">
<div class="container my-4">
    <!-- Links to Consultation & Radiology Results -->
    <div class="d-flex justify-content-end mb-3">
        <!-- Link to request a consultation for this patient -->
        <a 
          href="consultation.php?patient_id=<?php echo urlencode($patientId); ?>" 
          class="btn btn-primary me-2"
        >
            Request Consultation
        </a>
        <!-- Link to view patient's radiology results -->
        <a 
          href="view_radiology_result.php?patient_id=<?php echo urlencode($patientId); ?>" 
          class="btn btn-secondary"
        >
            View Radiology Results
        </a>
    </div>

    <h1 class="mb-4">Patient Record Details</h1>
    <div class="card">
        <div class="card-header">
            Patient ID: <?php echo htmlspecialchars($patient['patient_id']); ?>
        </div>
        <div class="card-body">
            <p><strong>Title:</strong> <?php echo htmlspecialchars($patient['title']); ?></p>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($patient['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($patient['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($patient['phone']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($patient['address']); ?></p>
            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['date_of_birth']); ?></p>
            <p><strong>Condition:</strong> <?php echo htmlspecialchars($patient['condition']); ?></p>
        </div>
    </div>

    <a href="patient_records.php" class="btn btn-outline-dark mt-3">Back to Records</a>
</div>

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
