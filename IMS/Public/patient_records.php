<?php
/**
 * patient_records.php
 * Display a paginated list of all patients, with options to
 * view detailed records or delete a patient.
 */
// Database connection
require_once __DIR__ . '../../config/database.php';

// (A) CONNECT TO DB
$database = new Database();
$pdo = $database->connect();

// (B) Handle DELETE action
if (isset($_GET['delete'])) {
    $patientIdToDelete = $_GET['delete'];

    // Delete from the 'patients' table using patient_id (7-digit)
    $delStmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = :pid");
    $delStmt->bindParam(':pid', $patientIdToDelete);
    $delStmt->execute();

    // Redirect to avoid re-triggering delete on refresh
    header('Location: patient_records.php?deleted=1');
    exit();
}

// (C) Pagination Setup
$limit = 5;                           // Number of records per page
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = max($page, 1);               // Ensure $page is at least 1
$offset = ($page - 1) * $limit;

// (D) Fetch Total Patients
$countQuery = "SELECT COUNT(*) AS total FROM patients";
$countStmt  = $pdo->query($countQuery);
$totalRow   = $countStmt->fetch(PDO::FETCH_ASSOC);
$total      = (int)$totalRow['total'];  // total number of patient records

// (E) Calculate total pages
$totalPages = ceil($total / $limit);

// (F) Fetch Patients for Current Page
// We'll select some basic fields: patient_id, title, name, date_of_birth
$query = "SELECT patient_id, title, name, date_of_birth 
          FROM patients
          ORDER BY created_at DESC
          LIMIT :offset, :limit";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit',  $limit,  PDO::PARAM_INT);
$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// (G) Optional: Check if something was just deleted
$deletedMessage = isset($_GET['deleted']) ? 'Patient record deleted successfully.' : '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Patient Records</title>
    <!-- Bootstrap 5 CSS -->
    <link 
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    >
    <!-- (Optional) Bootstrap Icons -->
    <link 
      rel="stylesheet" 
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
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
    <h1 class="mb-4">All Patient Records</h1>

    <?php if ($deletedMessage): ?>
        <div id="deleteAlert" class="alert alert-success text-center">
            <?php echo htmlspecialchars($deletedMessage); ?>
        </div>
        <script>
            // Hide delete alert after 5 seconds
            setTimeout(() => {
                const alertElem = document.getElementById('deleteAlert');
                if (alertElem) {
                    alertElem.style.display = 'none';
                }
            }, 5000);
        </script>
    <?php endif; ?>

    <?php if ($total > 0): ?>
        <table class="table table-bordered table-hover align-middle bg-white">
            <thead class="table-light">
                <tr>
                    <th>Patient ID</th>
                    <th>Title</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th width="25%">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($patient['title']); ?></td>
                    <td><?php echo htmlspecialchars($patient['name']); ?></td>
                    <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
                    <td>
                        <!-- View Link (e.g. 'view_patient.php?pid=xxxxxxx') -->
                        <a 
                          href="view_patient.php?patient_id=<?php echo urlencode($patient['patient_id']); ?>" 
                          class="btn btn-primary btn-sm me-2"
                        >
                            <i class="bi bi-eye"></i> View
                        </a>
                        
                        <!-- Delete Link -->
                        <a 
                          href="?delete=<?php echo urlencode($patient['patient_id']); ?>" 
                          class="btn btn-danger btn-sm"
                          onclick="return confirm('Are you sure you want to delete this patient?');"
                        >
                            <i class="bi bi-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination Controls -->
        <nav>
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                    <a class="page-link" 
                       href="?page=<?php echo max(1, $page - 1); ?>"
                       aria-label="Previous"
                    >
                       <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" 
                       href="?page=<?php echo min($totalPages, $page + 1); ?>"
                       aria-label="Next"
                    >
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>

    <?php else: ?>
        <div class="alert alert-info">No patient records found.</div>
    <?php endif; ?>
</div>

<!-- Bootstrap 5 JS -->
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
