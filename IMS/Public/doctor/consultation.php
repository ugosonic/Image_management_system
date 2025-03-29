<?php
// session_start();
// if ($_SESSION['usergroup'] !== 'Doctor') {
//     header('Location: ../login.php');
//     exit();
// }

require_once __DIR__ . '/../../src/models/Patient.php';
require_once __DIR__ . '/../../src/models/TestCategory.php';
require_once __DIR__ . '/../../src/models/Consultation.php';

// We'll assume the doctor navigates to: consultation.php?patient_id=xxxxxxx
if (!isset($_GET['patient_id'])) {
    die("No patient ID provided.");
}

$patient_id = $_GET['patient_id'];

$patientObj = new Patient();
$testCatObj = new TestCategory();
$consultationObj = new Consultation();

// Example: $doctor_id = $_SESSION['staff_id']; // if logged in as Doctor
$doctor_id = '1234567'; // Hard-coded for demonstration

$patient = $patientObj->getPatientById($patient_id);
if (!$patient) {
    die("Invalid patient ID.");
}

$testCategories = $testCatObj->getAllCategories();

// Form submission
$success = false;
$error = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $test_category_id = $_POST['test_category_id'];
    $notes = $_POST['notes'];

    if ($consultationObj->createConsultation($patient_id, $doctor_id, $test_category_id, $notes)) {
        $success = true;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Consultation for <?= htmlspecialchars($patient['name']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <!-- Bootstrap 5 CSS -->
    <link 
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
</head>
<body>
<div class="container mt-5">
    <h1>Consultation for: <?= htmlspecialchars($patient['name']) ?></h1>

    <?php if ($success): ?>
        <div class="alert alert-success">Consultation request sent successfully!</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger">Error sending request. Please try again.</div>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-header">Patient Details</div>
        <div class="card-body">
            <p><strong>Name:</strong> <?= htmlspecialchars($patient['title'] . ' ' . $patient['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($patient['phone']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($patient['address']) ?></p>
            <p><strong>Condition:</strong> <?= htmlspecialchars($patient['condition']) ?></p>
        </div>
    </div>

    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="test_category_id" class="form-label">Select Radiology Test</label>
            <select name="test_category_id" id="test_category_id" class="form-select" required>
                <option value="">-- Choose a Test Category --</option>
                <?php foreach ($testCategories as $cat): ?>
                    <option value="<?= $cat['id'] ?>">
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Doctor's Notes</label>
            <textarea 
              name="notes" 
              id="notes" 
              rows="4" 
              class="form-control" 
              placeholder="Add specific instructions or notes (optional)"
            ></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send Consultation Request</button>
    </form>
</div>

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
