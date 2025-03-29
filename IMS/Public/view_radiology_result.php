<?php
/**
 * view_radiology_result.php
 * Displays all radiology requests for a given patient, using Bootstrap,
 * shows "Completed By" staff, provides a modal with images,
 * and a "Delete" button for each image.
 */

if (!isset($_GET['patient_id'])) {
    die("No patient specified.");
}

require_once __DIR__ . '../../config/database.php';
$database = new Database();
$pdo = $database->connect();

$patientId = $_GET['patient_id'];
$patientName = $_GET['name'];



// 1) Query to fetch requests (with staff name if completed_by set)
$query = "
    SELECT rr.id,
       rr.created_at,
       rr.status,
       rr.completed_by, 
       tc.category_name,
       tsc.subcategory_name,
       tsc.price
FROM radiology_requests rr
JOIN test_categories tc       ON rr.category_id = tc.id
JOIN test_subcategories tsc   ON rr.subcategory_id = tsc.id
WHERE rr.patient_id = :pid
ORDER BY rr.created_at DESC;


";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':pid', $patientId, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) For each "Completed" request, fetch images from `radiology_images`
$imagesByRequest = [];
foreach ($results as $r) {
    if ($r['status'] === 'Completed') {
        $reqId = $r['id'];
        $imgStmt = $pdo->prepare("
            SELECT id, image_path, description, uploaded_by, uploaded_at
            FROM radiology_images
            WHERE request_id = :rid
        ");
        $imgStmt->execute([':rid' => $reqId]);
        $imagesByRequest[$reqId] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Radiology Results</title>
  <!-- Bootstrap 5 CSS -->
  <link 
    rel="stylesheet" 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <style>
    body {
      background-color: #f8f9fa;
    }
    .resizable-image {
      max-width: 100%;
      height: auto;
      transition: width 0.2s ease-in-out;
    }
  </style>
</head>
<body>

<!-- Example Navbar -->
<?php include 'navbar.php'; ?>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-auto px-0">
      <?php include 'sidebar.php'; ?>
    </div>
    <div class="col">
      <div class="container py-4">
        <h1 class="h2 mb-4">
          Patient #<?php echo htmlspecialchars($patientId); ?> - Radiology Requests
        </h1>

        <?php if (count($results) > 0): ?>
          <div class="table-responsive bg-white p-3 rounded shadow-sm">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>Request ID</th>
                  <th>Category</th>
                  <th>Subcategory</th>
                  <th>Price</th>
                  <th>Requested On</th>
                  <th>Status</th>
                  <th>Completed By</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($results as $row): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['subcategory_name']); ?></td>
                    <td><?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    <td>
                      <?php if ($row['status'] === 'Completed'): ?>
                        <span class="text-success fw-bold">Completed</span>
                        <!-- Button to open the modal -->
                        <button
                          class="btn btn-primary btn-sm ms-2"
                          onclick="openResultModal('<?php echo $row['id']; ?>')"
                        >
                          View Result
                        </button>
                      <?php elseif ($row['status'] === 'Canceled'): ?>
                        <span class="text-secondary fw-bold">Canceled</span>
                      <?php else: ?>
                        <span class="text-danger fw-bold">Waiting</span>
                      <?php endif; ?>
                    </td>
                    <td>
  <?php
    if ($row['status'] === 'Completed' && $row['completed_by']) {
      echo htmlspecialchars($row['completed_by']); // Directly output the completed_by value
    } else {
      echo '<span class="text-muted">--</span>';
    }
  ?>
</td>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info mt-4">
            No radiology requests found for this patient.
          </div>
        <?php endif; ?>

        <a 
          href="view_patient.php?patient_id=<?php echo urlencode($patientId); ?>" 
          class="btn btn-secondary mt-3"
        >
          Back to Patient
        </a>
      </div>
    </div>
  </div>
</div>

<!-- RESULT MODAL -->
<div
  class="modal fade"
  id="resultModal"
  tabindex="-1"
  aria-labelledby="resultModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resultModalLabel">Radiology Result Details</h5>
        <button 
          type="button"
          class="btn-close"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>
      </div>
      <div class="modal-body">
        <!-- Slider for resizing images -->
        <label for="imageSizeRange" class="form-label fw-bold">Image Size</label>
        <input 
          type="range"
          class="form-range mb-3"
          min="50"
          max="200"
          step="10"
          id="imageSizeRange"
          value="100"
        >
        <div id="resultContent"></div>
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

<script>
// imagesData: { request_id: [ {id, image_path, description, uploaded_by, uploaded_at}, ...], ... }
const imagesData = <?php echo json_encode($imagesByRequest); ?>;

/**
 * openResultModal: show images in the Bootstrap modal, with a Delete button for each
 */
function openResultModal(reqId) {
  const container = document.getElementById('resultContent');
  container.innerHTML = '';

  if (imagesData[reqId]) {
    imagesData[reqId].forEach(img => {
      const imageId  = img.id;
      const path     = '/IMS/' + (img.image_path || ''); // Ensure "/IMS/uploads/..."
      const desc     = img.description  || '';
      const staff    = img.uploaded_by  || 'Unknown Staff';
      const upTime   = img.uploaded_at  || '';

      const itemDiv = document.createElement('div');
      itemDiv.classList.add('mb-4', 'pb-3', 'border-bottom');

      itemDiv.innerHTML = `
        <p class="fw-semibold mb-1">Description:</br></p> <p class="text-muted mb-2">${desc} </p>
        <p class="fw-semibold mb-1">
          Uploaded by: ${staff} ${upTime ? 'on ' + upTime : ''}
        </p>
        <img 
          src="${path}"
          alt="Radiology Image"
          class="resizable-image border mb-2 d-block"
        >
        <button 
          class="btn btn-danger btn-sm"
          onclick="deleteImage(${imageId}, '${reqId}')"
        >
          Delete
        </button>
      `;
      container.appendChild(itemDiv);
    });
  } else {
    container.innerHTML = '<p class="text-muted">No images found for this request.</p>';
  }

  // Show the modal
  const modalEl = document.getElementById('resultModal');
  const bootstrapModal = new bootstrap.Modal(modalEl);
  bootstrapModal.show();
}

// Delete image via AJAX
function deleteImage(imageId, reqId) {
  if (!confirm('Are you sure you want to delete this image?')) {
    return;
  }
  fetch('/ims/public/delete_image.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ image_id: imageId })
  })
  .then(resp => resp.json())
  .then(data => {
    if (data.success) {
      // Remove this image from imagesData so it disappears from modal
      imagesData[reqId] = imagesData[reqId].filter(img => img.id !== imageId);

      // Re-render the modal content
      openResultModal(reqId);
    } else {
      alert(data.error || 'Failed to delete image');
    }
  })
  .catch(err => {
    console.error(err);
    alert('An error occurred deleting the image');
  });
}

// Resize images based on slider
document.getElementById('imageSizeRange').addEventListener('input', e => {
  const scale = e.target.value;
  const allImgs = document.querySelectorAll('#resultContent .resizable-image');
  allImgs.forEach(img => {
    img.style.width = scale + '%';
  });
});
</script>

<!-- Bootstrap 5 JS -->
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>
</body>
</html>
