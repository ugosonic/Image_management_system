<?php
/**
 * view_uploaded_images.php
 * Displays all images with filter by category, subcategory, date (disable future).
 * Includes real modals for View, Edit (replace image & desc), and Delete.
 */

session_start();
if (!isset($_SESSION['usergroup']) || $_SESSION['usergroup'] !== 'Radiologist') {
   header('Location: login.php');
   exit();
 }

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

// -------------------- 1) Handle AJAX actions (View, Edit, Delete) --------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // (A) VIEW IMAGE (AJAX)
    if ($action === 'view_image' && isset($_POST['image_id'])) {
        $imageId = $_POST['image_id'];

        $sql = "
          SELECT img.id, img.image_path, img.description, img.uploaded_by, img.uploaded_at
          FROM radiology_images img
          WHERE img.id = :imgId
          LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':imgId' => $imageId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                'success' => true,
                'data' => $row
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Image not found'
            ]);
        }
        exit();
    }

    // (B) EDIT IMAGE
    if ($action === 'edit_image' && isset($_POST['image_id'])) {
        $imageId    = $_POST['image_id'];
        $newDesc    = $_POST['description'] ?? '';

        // 1. Fetch old row for old file path
        $oldSql = "SELECT image_path FROM radiology_images WHERE id = :id";
        $oldStmt= $pdo->prepare($oldSql);
        $oldStmt->execute([':id'=>$imageId]);
        $oldRow = $oldStmt->fetch(PDO::FETCH_ASSOC);
        if (!$oldRow) {
            echo json_encode(['success'=>false, 'error'=>'Image record not found']);
            exit();
        }

        $oldPath = $oldRow['image_path'];

        // 2. If new file is uploaded, handle it
        $finalPath = $oldPath; // default to old path
        if (isset($_FILES['new_image']) && is_uploaded_file($_FILES['new_image']['tmp_name'])) {
            // Move new file
            $uploadsDir = __DIR__ . '/../../'; // base path
            $oldAbsPath = $uploadsDir . $oldPath;

            // e.g. "uploads/category_3" from oldPath
            // We'll parse out the directory from old path if we want same folder
            // Or if you store folder in DB, you can re-check from request_id
            $folder = dirname($oldPath); // e.g. "uploads/category_3"

            $filename = basename($_FILES['new_image']['name']);
            $newRelative = $folder . '/' . $filename;
            $newAbsolute = $uploadsDir . $newRelative;

            // Move the new file
            move_uploaded_file($_FILES['new_image']['tmp_name'], $newAbsolute);

            // Optional: remove old file
            if (file_exists($oldAbsPath)) {
                unlink($oldAbsPath);
            }
            $finalPath = $newRelative;
        }

        // 3. Update DB row
        $updSQL = "
          UPDATE radiology_images
          SET image_path = :path,
              description = :desc
          WHERE id = :id
        ";
        $updStmt = $pdo->prepare($updSQL);
        $updStmt->execute([
            ':path' => $finalPath,
            ':desc' => $newDesc,
            ':id'   => $imageId
        ]);

        echo json_encode(['success'=>true]);
        exit();
    }

    // (C) DELETE IMAGE
    if ($action === 'delete_image' && isset($_POST['image_id'])) {
        $imageId = $_POST['image_id'];

        // 1. Fetch row for file path
        $delSql = "SELECT image_path FROM radiology_images WHERE id=:id";
        $delStmt= $pdo->prepare($delSql);
        $delStmt->execute([':id'=>$imageId]);
        $row = $delStmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['success'=>false, 'error'=>'Image record not found']);
            exit();
        }
        $filePath = $row['image_path'];
        $absPath  = __DIR__ . '/../../' . $filePath;

        // 2. Remove from DB
        $delSql2 = "DELETE FROM radiology_images WHERE id=:id";
        $delStmt2= $pdo->prepare($delSql2);
        $delStmt2->execute([':id'=>$imageId]);

        // 3. Unlink physical file
        if (file_exists($absPath)) {
            unlink($absPath);
        }

        echo json_encode(['success'=>true]);
        exit();
    }

    // If none matched
    echo json_encode(['success'=>false, 'error'=>'Invalid action or missing parameters']);
    exit();
}

// -------------------- 2) Build filter logic + main query to display images in table --------------------
require_once __DIR__ . '../../config/database.php';

// Filters
$selectedCategory    = $_GET['category']    ?? '';
$selectedSubcategory = $_GET['subcategory'] ?? '';
$selectedDate        = $_GET['filter_date'] ?? '';

// Build dynamic WHERE
$whereParts = [];
$params     = [];

// Filter by category
if ($selectedCategory !== '') {
    $whereParts[]   = 'rr.category_id = :catId';
    $params[':catId'] = $selectedCategory;
}

// Filter by subcategory
if ($selectedSubcategory !== '') {
    $whereParts[]     = 'rr.subcategory_id = :subcatId';
    $params[':subcatId'] = $selectedSubcategory;
}

// Filter by date => `uploaded_at`
if (!empty($selectedDate)) {
    $whereParts[] = 'DATE(img.uploaded_at) = :imgDate';
    $params[':imgDate'] = $selectedDate;
}

// For category/subcategory dropdown
$catStmt = $pdo->query("SELECT id, category_name FROM test_categories ORDER BY category_name");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$subcatStmt = $pdo->query("SELECT id, category_id, subcategory_name FROM test_subcategories ORDER BY subcategory_name");
$subcategories = $subcatStmt->fetchAll(PDO::FETCH_ASSOC);

// Main query
$query = "
  SELECT 
    img.id AS image_id,
    img.image_path,
    img.description,
    img.uploaded_by,
    img.uploaded_at,
    rr.completed_by,
    pat.name AS patient_name,
    COALESCE(tsc.price, tc.price, 0) AS price,
    tc.id AS category_id,
    tc.category_name,
    tsc.id AS subcategory_id,
    tsc.subcategory_name
  FROM radiology_images img
  JOIN radiology_requests rr  ON img.request_id = rr.id
  JOIN patients pat          ON rr.patient_id   = pat.patient_id
  JOIN test_categories tc    ON rr.category_id  = tc.id
  JOIN test_subcategories tsc ON rr.subcategory_id = tsc.id
";

// Add WHERE if needed
if ($whereParts) {
    $query .= " WHERE " . implode(" AND ", $whereParts);
}

// 5) pagination
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset= ($page - 1)*$limit;

// count total
$countSQL= "SELECT COUNT(*) AS total FROM (" . $query . ") tmp";
$countStmt= $pdo->prepare($countSQL);
$countStmt->execute($params);
$countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
$totalRows= $countRow ? (int)$countRow['total'] : 0;
$totalPages= ceil($totalRows/$limit);

// final query
$query .= " ORDER BY img.uploaded_at DESC LIMIT :offset, :limit";

// do it
$stmt = $pdo->prepare($query);
foreach ($params as $k=>$v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

// random color function
function getRandomColor($catId) {
    static $colorMap = [];
    static $palette  = [
        '#f94144','#f3722c','#f8961e','#f9844a','#f9c74f',
        '#90be6d','#43aa8b','#577590','#277da1','#4d908e'
    ];
    if (!isset($colorMap[$catId])) {
        $colorMap[$catId] = $palette[array_rand($palette)];
    }
    return $colorMap[$catId];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Uploaded Images</title>
  <link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <style>
    body {
      background-color: #f8f9fa;
    }
    .category-heading {
      color: #fff;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      margin-bottom: 0.25rem;
      font-weight: 600;
      text-align: center;
    }
    .subcategory-heading {
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      text-align: center;
      font-weight: 500;
      color: #333;
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
    <div class="col">
<div class="container my-4">
  <h1 class="mb-4">All Uploaded Images</h1>

  <!-- Filters -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label for="category" class="form-label fw-bold">Category</label>
      <select class="form-select" id="category" name="category">
        <option value="">-- All Categories --</option>
        <?php foreach ($categories as $cat): ?>
          <option 
            value="<?php echo $cat['id']; ?>"
            <?php if ($selectedCategory == $cat['id']) echo 'selected'; ?>
          >
            <?php echo htmlspecialchars($cat['category_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label for="subcategory" class="form-label fw-bold">Subcategory</label>
      <select class="form-select" id="subcategory" name="subcategory">
        <option value="">-- All Subcategories --</option>
        <?php foreach ($subcategories as $sc): ?>
          <option 
            value="<?php echo $sc['id']; ?>"
            <?php if ($selectedSubcategory == $sc['id']) echo 'selected'; ?>
          >
            <?php echo htmlspecialchars($sc['subcategory_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label for="filter_date" class="form-label fw-bold">Uploaded Date</label>
      <input 
        type="date"
        class="form-control"
        id="filter_date"
        name="filter_date"
        value="<?php echo htmlspecialchars($selectedDate); ?>"
        max="<?php echo date('Y-m-d'); ?>"
      >
    </div>
    <div class="col-md-1 d-flex align-items-end">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <?php if ($images): ?>
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Image ID</th>
          <th>Patient</th>
          <th style="width:200px;">Category &amp; Subcategory</th>
          <th>Description</th>
          <th>Price</th>
          <th>Uploaded By</th>
          <th>Completed By</th>
          <th>Uploaded On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($images as $img): ?>
        <?php
          $catColor = getRandomColor($img['category_id']);
          $catName  = htmlspecialchars($img['category_name']);
          $subName  = htmlspecialchars($img['subcategory_name']);
          $price    = $img['price'] ?? 0;
        ?>
        <tr>
          <td><?php echo htmlspecialchars($img['image_id']); ?></td>
          <td><?php echo htmlspecialchars($img['patient_name']); ?></td>
          <td>
            <div 
              class="category-heading"
              style="background-color: <?php echo $catColor; ?>;"
            >
              <?php echo $catName; ?>
            </div>
            <div class="subcategory-heading">
              <?php echo $subName; ?>
            </div>
          </td>
          <td><?php echo nl2br(htmlspecialchars($img['description'])); ?></td>
          <td><?php echo number_format($price, 2); ?></td>
          <td><?php echo htmlspecialchars($img['uploaded_by'] ?: '--'); ?></td>
          <td><?php echo htmlspecialchars($img['completed_by'] ?: '--'); ?></td>
          <td><?php echo htmlspecialchars($img['uploaded_at']); ?></td>
          <td>
            <button 
              class="btn btn-info btn-sm mb-1"
              onclick="viewImage(<?php echo $img['image_id']; ?>)"
            >
              View
            </button>
            <button 
              class="btn btn-warning btn-sm mb-1 text-white"
              onclick="openEditModal(<?php echo $img['image_id']; ?>)"
            >
              Edit
            </button>
            <button 
              class="btn btn-danger btn-sm"
              onclick="deleteImage(<?php echo $img['image_id']; ?>)"
            >
              Delete
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
      <nav class="mt-3">
        <ul class="pagination justify-content-center">
          <?php for($i=1; $i<=$totalPages; $i++): ?>
            <li class="page-item <?php if($i==$page) echo 'active'; ?>">
              <a class="page-link" 
                 href="?page=<?php echo $i; ?>&category=<?php echo urlencode($selectedCategory); ?>&subcategory=<?php echo urlencode($selectedSubcategory); ?>&filter_date=<?php echo urlencode($selectedDate); ?>"
              >
                <?php echo $i; ?>
              </a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php else: ?>
    <div class="alert alert-info">No images found for the current filter.</div>
  <?php endif; ?>
</div>
</div>
        </div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">View Image</h5>
        <button 
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>
      </div>
      <div class="modal-body" id="viewModalBody">
      </div>
      <div class="modal-footer">
        <button 
          type="button"
          class="btn btn-secondary"
          data-bs-dismiss="modal"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit_image">
        <input type="hidden" name="image_id" id="edit_image_id">

        <div class="modal-header">
          <h5 class="modal-title">Edit Image</h5>
          <button 
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body">
          <div id="editFormContent">
            <!-- We'll load the existing desc + an <img> preview + file field for new_image -->
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
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Bootstrap 5 JS (bundle) -->
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
<script>
/** 
 * 1) VIEW (AJAX) 
 */
function viewImage(imageId) {
  const formData = new FormData();
  formData.append('action','view_image');
  formData.append('image_id', imageId);

  fetch(location.href, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(resp => {
    if(!resp.success) {
      alert(resp.error || 'Failed to fetch image details');
      return;
    }
    // Show in modal
    const data = resp.data;
    const container = document.getElementById('viewModalBody');
    const path = '/IMS/' + (data.image_path || '');
    container.innerHTML = `
      <div class="mb-3">
        <img src="${path}" class="img-fluid border" alt="Preview">
      </div>
      <p><strong>Description:</strong> ${data.description ? data.description : ''}</p>
      <p><strong>Uploaded By:</strong> ${data.uploaded_by ? data.uploaded_by : '--'}</p>
      <p><strong>Uploaded At:</strong> ${data.uploaded_at ? data.uploaded_at : '--'}</p>
    `;
    const modal = new bootstrap.Modal(document.getElementById('viewModal'));
    modal.show();
  })
  .catch(err => {
    console.error(err);
    alert('Error fetching image details');
  });
}

/** 
 * 2) EDIT 
 */
function openEditModal(imageId) {
  // Step 1: fetch existing data
  const formData = new FormData();
  formData.append('action','view_image'); // reuse the same view call
  formData.append('image_id', imageId);

  fetch(location.href, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(resp => {
    if(!resp.success) {
      alert(resp.error || 'Failed to fetch image details for edit');
      return;
    }
    const data = resp.data;
    const path = '/IMS/' + (data.image_path || '');
    document.getElementById('edit_image_id').value = imageId;

    const ctn = document.getElementById('editFormContent');
    ctn.innerHTML = `
      <div class="mb-3 text-center">
        <img src="${path}" alt="Preview" class="img-fluid mb-2 border">
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Description</label>
        <textarea class="form-control" rows="3" name="description">${data.description ? data.description : ''}</textarea>
      </div>
      <div class="mb-3">
        <label class="form-label fw-bold">Replace Image</label>
        <input type="file" name="new_image" class="form-control" accept="image/*">
      </div>
    `;
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editModal'));
    modal.show();
  })
  .catch(err => {
    console.error(err);
    alert('Error fetching image for edit');
  });
}

// Step 2: Submit edit
document.getElementById('editForm').addEventListener('submit', function(e){
  e.preventDefault();
  const formData = new FormData(this);
  // POST to same page with action=edit_image
  fetch(location.href, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(resp => {
    if(!resp.success) {
      alert(resp.error || 'Failed to edit image');
      return;
    }
    alert('Image updated successfully!');
    // reload page
    location.reload();
  })
  .catch(err => {
    console.error(err);
    alert('Error editing image');
  });
});

/** 
 * 3) DELETE 
 */
function deleteImage(imageId) {
  if(!confirm(`Are you sure you want to delete image #${imageId}?`)) return;

  const formData = new FormData();
  formData.append('action','delete_image');
  formData.append('image_id', imageId);

  fetch(location.href, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(resp => {
    if(!resp.success) {
      alert(resp.error || 'Failed to delete image');
      return;
    }
    alert('Image deleted successfully!');
    location.reload();
  })
  .catch(err => {
    console.error(err);
    alert('Error deleting image');
  });
}
</script>
</body>
</html>
