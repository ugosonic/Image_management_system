<?php
/**
 * radiologist_dashboard.php
 * - Fetches currency sign from app_settings
 * - Calculates daily transactions
 * - Shows them in a light-blue container
 * - Shows pending requests (calendar + pagination) in light-red container
 * - Shows last 5 images in light-grey container
 * - Uses Bootstrap 5, responsive, with hover effects on containers
 */

session_start();
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] !== 'Radiologist') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

/***************************************************************
 * 1) FETCH CURRENCY SIGN FROM `app_settings`
 ***************************************************************/
$currencySign = '$'; // Default
$settingsStmt = $pdo->query("
    SELECT value 
    FROM app_settings 
    WHERE name='currency_sign' 
    LIMIT 1
");
if ($settingsRow = $settingsStmt->fetch(PDO::FETCH_ASSOC)) {
    $currencySign = $settingsRow['value'];
}

/***************************************************************
 * 2) CALCULATE DAILY TRANSACTIONS (PLACEHOLDER)
 *    For example, if you store them in 'some_transactions_table'
 *    with a numeric 'amount' column plus 'created_at' datetime
 ***************************************************************/
$today = date('Y-m-d');
$transSQL = "
  SELECT COALESCE(SUM( COALESCE(tsc.price, tc.price, 0) ), 0) AS dailyTotal
  FROM radiology_requests rr
  JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
  JOIN test_categories tc     ON rr.category_id    = tc.id
  WHERE rr.status = 'Completed'
    AND DATE(rr.completed_at) = :today
";
$stmtTrans = $pdo->prepare($transSQL);
$stmtTrans->execute([':today' => $today]);
$transRow  = $stmtTrans->fetch(PDO::FETCH_ASSOC);
$dailyTotal= $transRow ? (float)$transRow['dailyTotal'] : 0.0;


/***************************************************************
 * 3) PENDING REQUESTS: CALENDAR FILTER + PAGINATION
 ***************************************************************/
$calendarDate = $_GET['calendar_date'] ?? '';
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset= ($page - 1) * $limit;

// Base query for pending requests
$pendingSQL = "
  SELECT rr.id, rr.patient_id, pat.name AS patient_name,
         rr.created_at, tc.category_name, tsc.subcategory_name
  FROM radiology_requests rr
  JOIN patients pat ON rr.patient_id = pat.patient_id
  JOIN test_categories tc ON rr.category_id = tc.id
  JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
  WHERE rr.status IN ('Pending','Paid')
";

// Calendar filter
$whereParts = [];
$params = [];
if (!empty($calendarDate)) {
    $whereParts[] = "DATE(rr.created_at) = :calDate";
    $params[':calDate'] = $calendarDate;
}
if ($whereParts) {
    $pendingSQL .= " AND " . implode(" AND ", $whereParts);
}

// Step 1: count total
$countSQL= "SELECT COUNT(*) AS total FROM (" . $pendingSQL . ") tmp";
$countStmt= $pdo->prepare($countSQL);
$countStmt->execute($params);
$countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
$totalPending = $countRow ? (int)$countRow['total'] : 0;
$totalPages   = ceil($totalPending / $limit);

// Step 2: final query with limit
$pendingSQL .= " ORDER BY rr.created_at DESC LIMIT :offset, :lim";
$stmtPending= $pdo->prepare($pendingSQL);
foreach ($params as $k => $v) {
    $stmtPending->bindValue($k, $v);
}
$stmtPending->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtPending->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmtPending->execute();
$pendingList = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

/***************************************************************
 * 4) LAST 5 IMAGES
 ***************************************************************/
$imgsSQL = "
  SELECT img.id AS image_id, img.image_path, img.uploaded_at,
         rr.patient_id, pat.name AS patient_name
  FROM radiology_images img
  JOIN radiology_requests rr ON img.request_id = rr.id
  JOIN patients pat         ON rr.patient_id   = pat.patient_id
  ORDER BY img.uploaded_at DESC
  LIMIT 5
";
$stmtImgs= $pdo->query($imgsSQL);
$lastImages = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Radiologist Dashboard</title>
  <!-- Bootstrap 5 CSS -->
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <style>
    body {
      background-color: #f8f9fa;
    }
    .hover-container:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: all 0.3s ease;
    }
    .light-blue {
      background-color: #cce5ff; /* light-blue */
    }
    .light-red {
      background-color: #f8d7da; /* light-red */
    }
    .light-grey {
      background-color: #e2e3e5; /* light-grey */
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

    <!-- MAIN CONTENT -->
    <div class="col">
      <div class="container py-4">
        <h1 class="mb-4">Welcome, Radiologist!</h1>

        <div class="row">
          <!-- Light-blue container: daily transactions with currency sign -->
          <div class="col-md-4 mb-4">
            <div class="p-3 rounded hover-container light-blue shadow rounded p-4 mb-5">
              <h3 class="mb-3">Today's Transactions</h3>
              <p><strong>Total Amount:</strong> 
                 <?php echo htmlspecialchars($currencySign . number_format($dailyTotal, 2)); ?>
              </p>
            </div>
          </div>

          <!-- Light-red container: pending requests with a calendar + pagination -->
          <div class="col-md-8 mb-4">
            <div class="p-3 rounded hover-container light-red shadow rounded p-4 mb-5">
              <h3 class="mb-3">Pending Requests</h3>

              <!-- Calendar filter form -->
              <form method="GET" class="row g-3 mb-3">
                <input type="hidden" name="page" value="1"> 
                <div class="col-auto">
                  <label class="form-label fw-bold" for="calendar_date">Filter by Date</label>
                  <input 
                    type="date"
                    class="form-control"
                    id="calendar_date"
                    name="calendar_date"
                    value="<?php echo htmlspecialchars($calendarDate); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
                  >
                </div>
                <div class="col-auto d-flex align-items-end">
                  <button type="submit" class="btn btn-dark">Filter</button>
                </div>
              </form>

              <?php if ($pendingList): ?>
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Request ID</th>
                      <th>Patient</th>
                      <th>Category</th>
                      <th>Subcategory</th>
                      <th>Requested On</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pendingList as $req): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($req['id']); ?></td>
                        <td>
                          <?php echo htmlspecialchars($req['patient_name']); ?> 
                          (<?php echo htmlspecialchars($req['patient_id']); ?>)
                        </td>
                        <td><?php echo htmlspecialchars($req['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($req['subcategory_name']); ?></td>
                        <td><?php echo htmlspecialchars($req['created_at']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>

                <!-- Pagination for pending requests -->
                <?php if ($totalPages > 1): ?>
                  <nav>
                    <ul class="pagination">
                      <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <li class="page-item <?php if($i==$page) echo 'active'; ?>">
                          <a class="page-link"
                            href="?page=<?php echo $i; ?>&calendar_date=<?php echo urlencode($calendarDate); ?>"
                          >
                            <?php echo $i; ?>
                          </a>
                        </li>
                      <?php endfor; ?>
                    </ul>
                  </nav>
                <?php endif; ?>
              <?php else: ?>
                <p class="text-muted">No pending requests found.</p>
              <?php endif; ?>
            </div>
          </div>
        </div><!-- /row top containers -->

        <!-- Light-grey container below for last 5 images -->
        <div class="row">
          <div class="col">
            <div class="p-3 rounded hover-container light-grey">
              <h3 class="mb-3">Last 5 Images Uploaded</h3>

              <?php if ($lastImages && count($lastImages) > 0): ?>
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th>Image ID</th>
                      <th>Patient</th>
                      <th>Uploaded On</th>
                      <th>Preview</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lastImages as $img): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($img['image_id']); ?></td>
                        <td>
                          <?php echo htmlspecialchars($img['patient_name']); ?>
                          (<?php echo htmlspecialchars($img['patient_id']); ?>)
                        </td>
                        <td><?php echo htmlspecialchars($img['uploaded_at']); ?></td>
                        <td>
                          <?php 
                            $path = '/IMS/' . ($img['image_path'] ?? '');
                          ?>
                          <img 
                            src="<?php echo htmlspecialchars($path); ?>" 
                            alt="preview"
                            style="max-width:80px;"
                          >
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php else: ?>
                <p class="text-muted">No images found.</p>
              <?php endif; ?>
            </div>
          </div>
        </div><!-- /row -->

      </div><!-- /container py-4 -->
    </div><!-- /col -->
  </div><!-- /row -->
</div><!-- /container-fluid -->

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
