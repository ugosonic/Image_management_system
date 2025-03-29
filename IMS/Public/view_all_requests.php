<?php
// view_all_requests.php
// For staff or admin to see all requested tests, manage them, etc.

session_start();
if ($_SESSION['usergroup'] !== 'Radiologist' ) {
 header('Location: login.php');
exit();
 }
// 1. Confirm we have a logged-in user (Doctor/Radiologist, etc.) if needed

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

// ----------------------------------------------
// 1) Fetch staff name from staff_registration
//    if user is Radiologist or Doctor
//    We do this once so we can reuse the real staff name
// ----------------------------------------------
$staffId   = $_SESSION['name'] ?? null;   // Radiologist user sets $_SESSION['name'] = staff_id
$staffName = $_SESSION['name'] ?? null;
if ($staffId) {
    // Attempt to fetch the real name from staff_registration
    $stmtStaff = $pdo->prepare("SELECT name FROM staff_registration WHERE staff_id = :sid LIMIT 1");
    $stmtStaff->execute([':sid' => $staffId]);
    $staffRow  = $stmtStaff->fetch(PDO::FETCH_ASSOC);
    if ($staffRow) {
        $staffName = $staffRow['name'];
    }
}

// ----------------------------------------------
// 2) Handle filters & pagination
// ----------------------------------------------
$filterDate  = isset($_GET['date']) ? $_GET['date'] : '';
$filterState = isset($_GET['state']) ? $_GET['state'] : 'Pending'; // 'Pending','Completed','Canceled','All'
$page        = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page        = max(1, $page);
$limit       = 5;
$offset      = ($page - 1) * $limit;

// (A) Base query
$baseQuery = "
  SELECT rr.id,
         rr.patient_id,
         rr.category_id,
         rr.subcategory_id,
         rr.status,
         rr.created_at,
         p.name AS patient_name,
         tc.category_name,
         tc.file_path AS category_file_path, -- we fetch the folder path
         tsc.subcategory_name,
         tsc.price
  FROM radiology_requests rr
  JOIN patients p         ON rr.patient_id = p.patient_id
  JOIN test_categories tc ON rr.category_id = tc.id
  JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
  WHERE rr.status LIKE :status
";

// (B) Additional WHERE if date filter is used
$whereParts = [];
$params = [':status' => ($filterState === 'All' ? '%' : $filterState)];

if (!empty($filterDate)) {
    $whereParts[] = "DATE(rr.created_at) = :cdate";
    $params[':cdate'] = $filterDate;
}
if (count($whereParts) > 0) {
    $baseQuery .= " AND " . implode(" AND ", $whereParts);
}

// (C) Count total matching
$countQuery = "SELECT COUNT(*) AS total FROM (" . $baseQuery . ") sub";
$countStmt  = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalRow   = $countStmt->fetch(PDO::FETCH_ASSOC);
$total      = $totalRow ? (int)$totalRow['total'] : 0;
$totalPages = ceil($total / $limit);

// (D) Final query with limit
$finalQuery = $baseQuery . " ORDER BY rr.created_at DESC LIMIT :offset, :lim";
$stmt = $pdo->prepare($finalQuery);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':lim',    $limit,  PDO::PARAM_INT);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ----------------------------------------------
// 3) Mark test Completed or Canceled
// ----------------------------------------------
if (isset($_GET['complete'])) {
    $reqId = $_GET['complete'];

    // We'll store the staff's real name in completed_by,
    // plus set completed_at = NOW()
    $updateSQL = "
      UPDATE radiology_requests
      SET status='Completed',
          completed_by = :staffName,
          completed_at = NOW()
      WHERE id = :id
    ";
    $updateStmt = $pdo->prepare($updateSQL);
    $updateStmt->execute([
        ':staffName' => $staffName,  // from staff_registration
        ':id'        => $reqId
    ]);

    header('Location: view_all_requests.php?state='.$filterState.'&date='.$filterDate);
    exit();
}

if (isset($_GET['cancel'])) {
    $reqId = $_GET['cancel'];
    $updateStmt = $pdo->prepare("UPDATE radiology_requests SET status='Canceled' WHERE id=:id");
    $updateStmt->execute([':id' => $reqId]);
    header('Location: view_all_requests.php?state='.$filterState.'&date='.$filterDate);
    exit();
}

// ----------------------------------------------
// 4) Upload images (POST)
// ----------------------------------------------
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'upload_images'
) {
    $reqId = $_POST['request_id'];

    // Find matching request in $requests to get category_file_path
    $matchedRequest = null;
    foreach ($requests as $r) {
        if ((int)$r['id'] === (int)$reqId) {
            $matchedRequest = $r;
            break;
        }
    }

    if (!$matchedRequest) {
        header('Location: view_all_requests.php?upload=notfound&state='.$filterState.'&date='.$filterDate);
        exit();
    }

    $categoryPath = $matchedRequest['category_file_path']; // e.g. "uploads/category_3"
    if (empty($categoryPath)) {
        header('Location: view_all_requests.php?upload=nopath&state='.$filterState.'&date='.$filterDate);
        exit();
    }

    // Make sure directory exists
    $absoluteCategoryDir = __DIR__ . '../../' . $categoryPath;
    if (!is_dir($absoluteCategoryDir)) {
        mkdir($absoluteCategoryDir, 0777, true);
    }

    // Move uploaded files
    $uploadedCount = 0;
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpPath) {
        // skip if no file
        if (!is_uploaded_file($tmpPath)) {
            continue;
        }

        $filename = basename($_FILES['images']['name'][$index]);
        $desc     = $_POST['descriptions'][$index] ?? '';

        $destPath     = $categoryPath . '/' . $filename;  // For DB
        $absoluteFile = $absoluteCategoryDir . '/' . $filename;

        if (move_uploaded_file($tmpPath, $absoluteFile)) {
            // Insert into radiology_images
            // We store the staff's real name in `uploaded_by`
            $stmtImg = $pdo->prepare("
                INSERT INTO radiology_images (request_id, image_path, description, uploaded_by)
                VALUES (:rid, :ipath, :desc, :staffName)
            ");
            $stmtImg->execute([
                ':rid'       => $reqId,
                ':ipath'     => $destPath,
                ':desc'      => $desc,
                ':staffName' => $staffName  // from staff_registration
            ]);
            $uploadedCount++;
        }
    }

    // If at least one file was uploaded, success; else error
    if ($uploadedCount > 0) {
        header('Location: view_all_requests.php?upload=success&state='.$filterState.'&date='.$filterDate);
    } else {
        header('Location: view_all_requests.php?upload=none&state='.$filterState.'&date='.$filterDate);
    }
    exit();
}

// ----------------------------------------------
// 5) Check if there's a GET param for upload=success/error
// ----------------------------------------------
if (isset($_GET['upload'])) {
    switch ($_GET['upload']) {
        case 'success':
            $uploadMessage = '<div class="alert alert-success">Image(s) uploaded successfully!</div>';
            break;
        case 'none':
            $uploadMessage = '<div class="alert alert-warning">No images were selected or uploaded!</div>';
            break;
        case 'notfound':
            $uploadMessage = '<div class="alert alert-danger">Request not found!</div>';
            break;
        case 'nopath':
            $uploadMessage = '<div class="alert alert-danger">Category path not set!</div>';
            break;
        default:
            $uploadMessage = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Requested Tests</title>
  <!-- Bootstrap 5 CSS -->
  <link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <style>
    body {
      background-color: #f8f9fa;
    }
    .page-title {
      margin: 20px 0;
    }
    .pagination a {
      text-decoration: none;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-auto px-0">
      <?php include 'sidebar.php'; ?>
    </div>

    <div class="col p-4">
      <h1 class="page-title h2">All Requested Tests</h1>

      <!-- Show upload messages if any -->
      <?php echo $uploadMessage; ?>

      <!-- Filter Form -->
      <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-auto">
          <label for="dateFilter" class="form-label">Date</label>
          <input 
            type="date"
            id="dateFilter"
            name="date"
            class="form-control"
            value="<?php echo htmlspecialchars($filterDate); ?>"
          >
        </div>
        <div class="col-auto">
          <label for="stateSelect" class="form-label">Status</label>
          <select 
            id="stateSelect"
            name="state"
            class="form-select"
          >
            <option value="Pending"   <?php if($filterState==='Pending')   echo 'selected';?>>Pending</option>
            <option value="Completed" <?php if($filterState==='Completed') echo 'selected';?>>Completed</option>
            <option value="Canceled"  <?php if($filterState==='Canceled')  echo 'selected';?>>Canceled</option>
            <option value="All"       <?php if($filterState==='All')       echo 'selected';?>>All</option>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">
            Filter
          </button>
        </div>
      </form>

      <!-- Table of Requests -->
      <div class="card">
        <div class="card-body">
          <?php if (count($requests) > 0): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Req ID</th>
                    <th>Patient</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $r): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($r['id']); ?></td>
                    <td>
                      <?php echo htmlspecialchars($r['patient_name']); ?>
                      (<?php echo htmlspecialchars($r['patient_id']); ?>)
                    </td>
                    <td><?php echo htmlspecialchars($r['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($r['subcategory_name']); ?></td>
                    <td>
                      <?php if ($r['status'] === 'Completed'): ?>
                        <span class="text-success fw-bold">Completed</span>
                      <?php elseif ($r['status'] === 'Canceled'): ?>
                        <span class="text-secondary fw-bold">Canceled</span>
                      <?php else: ?>
                        <span class="text-danger fw-bold">Pending</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <!-- Complete/Cancel if status is Pending -->
                      <?php if ($r['status'] === 'Pending'): ?>
                        <a 
                          href="?complete=<?php echo $r['id']; ?>&state=<?php echo urlencode($filterState); ?>&date=<?php echo urlencode($filterDate); ?>"
                          class="btn btn-success btn-sm"
                        >
                          Complete
                        </a>
                        <a 
                          href="?cancel=<?php echo $r['id']; ?>&state=<?php echo urlencode($filterState); ?>&date=<?php echo urlencode($filterDate); ?>"
                          class="btn btn-warning btn-sm text-white"
                        >
                          Cancel
                        </a>
                      <?php endif; ?>

                      <!-- Upload Images Button triggers Bootstrap modal -->
                      <button 
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#uploadModal"
                        data-request-id="<?php echo $r['id']; ?>"
                      >
                        Upload Images
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav class="mt-3">
              <ul class="pagination justify-content-center">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                  <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                    <a 
                      class="page-link"
                      href="?page=<?php echo $i; ?>&state=<?php echo urlencode($filterState); ?>&date=<?php echo urlencode($filterDate); ?>"
                    >
                      <?php echo $i; ?>
                    </a>
                  </li>
                <?php endfor; ?>
              </ul>
            </nav>
          <?php else: ?>
            <p class="text-muted">No records found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Upload Modal (Bootstrap) -->
<div 
  class="modal fade"
  id="uploadModal"
  tabindex="-1"
  aria-labelledby="uploadModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload_images">
        <input type="hidden" name="request_id" id="request_id_input">

        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel">Upload Radiology Images</h5>
          <button 
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Select Images</label>
            <input
              type="file"
              name="images[]"
              multiple
              class="form-control"
              accept="image/*"
            >
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Description(s)</label>
            <textarea
              name="descriptions[]"
              rows="3"
              class="form-control"
              placeholder="Put text for each image on separate lines or in separate modals"
            ></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button 
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
          <button 
            type="submit"
            class="btn btn-primary"
          >
            Upload
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap 5 bundle (includes Popper for modals) -->
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>

<script>
  // Show which request ID we’re uploading images for
  const uploadModal = document.getElementById('uploadModal');
  uploadModal.addEventListener('show.bs.modal', event => {
    const button = event.relatedTarget;
    const reqId  = button.getAttribute('data-request-id');
    document.getElementById('request_id_input').value = reqId;
  });
</script>
</body>
</html>
