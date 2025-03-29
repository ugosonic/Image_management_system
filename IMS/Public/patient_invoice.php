<?php
/**
 * ============================================================================
 * patient_invoice.php
 * ============================================================================
 * - Shows invoices (radiology requests) for the logged-in patient
 * - Displays them in a grid (2 per row if you'd like, or up to 6 total as is).
 * - Pending => Red highlight + "Pay Now" button
 * - Paid/Completed => Green highlight
 * - Payment notifications vanish after 10s (done via JS).
 *
 * MARKING SCHEME:
 * - Interaction & selection (checking session, GET/POST).
 * - Use of OOP classes (PatientInvoiceHandler) & method invocation.
 * - DB usage for reading invoice data.
 * - Clean separation of concerns.
 * ============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is a logged-in patient
if (!isset($_SESSION['patient_id']) || $_SESSION['usergroup'] !== 'Patient') {
    header('Location: login.php');
    exit();
}

// 1) Require database + invoice handler
require_once __DIR__ . '../../config/Database.php';
require_once __DIR__ . '../../classes/PatientInvoiceHandler.php';

// 2) Connect to DB
$db = new Database();
$pdo = $db->connect();

// 3) Instantiate Handler
$invoiceHandler = new PatientInvoiceHandler($pdo);

// 4) Get Patient ID
$patientId = (int) $_SESSION['patient_id'];

// 5) Fetch currency sign
$currencySign = $invoiceHandler->fetchCurrencySign();

// 6) Handle Pagination
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 6;  // you can set to 2 if you want exactly 2 per page
$offset= ($page - 1) * $limit;

// 7) Count total invoices
$totalInvoices = $invoiceHandler->countInvoices($patientId);
$totalPages    = ceil($totalInvoices / $limit);

// 8) Fetch invoices for current page
$invoices = $invoiceHandler->fetchInvoices($patientId, $limit, $offset);

// 9) Check for payment notifications
$paymentMessage = '';
$paymentType    = $_GET['payment'] ?? '';
if ($paymentType === 'success') {
    $paymentMessage = '<div id="paymentAlert" class="alert alert-success">Payment completed successfully!</div>';
} elseif ($paymentType === 'error') {
    $paymentMessage = '<div id="paymentAlert" class="alert alert-danger">Payment error occurred. Please try again.</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Invoices</title>
  <!-- Bootstrap 5 CSS -->
  <link 
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  >
  <style>
    body {
      background-color: #f8f9fa;
    }
    .invoice-container {
      background-color: #fff; 
      border: 2px solid #000; /* dark border */
      padding: 1rem;
      margin-bottom: 1rem;
      position: relative;
      min-height: 220px;
    }
    .invoice-status-strip {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 8px;
    }
    .status-pending {
      background-color: red;  /* red for pending/unpaid */
    }
    .status-paid {
      background-color: green;/* green for paid/completed */
    }
    .invoice-title {
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }
    .invoice-line {
      margin-bottom: 0.25rem;
    }
    .invoice-container:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      transform: translateY(-2px);
      transition: all 0.3s ease;
    }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto px-0">
      <?php include 'sidebar.php'; ?>
    </div>
    <div class="col">
      <div class="container py-4">
        <h1 class="mb-4">My Invoices</h1>

        <!-- Payment notification (disappears after 10s) -->
        <?php if (!empty($paymentMessage)): ?>
          <?php echo $paymentMessage; ?>
          <script>
          setTimeout(() => {
            const el = document.getElementById('paymentAlert');
            if(el) el.style.display = 'none';
          }, 10000); // 10 seconds
          </script>
        <?php endif; ?>

        <?php if (count($invoices) > 0): ?>
          <div class="row">
            <?php foreach ($invoices as $inv): ?>
              <?php
                // Check status => color highlight
                $statusClass = in_array($inv['status'], ['Completed','Paid']) 
                               ? 'status-paid' 
                               : 'status-pending';
              ?>
              <div class="col-md-6 mb-4">
                <div class="invoice-container">
                  <div class="invoice-status-strip <?php echo $statusClass; ?>"></div>
                  <div class="invoice-title">Invoice #<?php echo htmlspecialchars($inv['id']); ?></div>
                  <div class="invoice-line">
                    <b>Category:</b> <?php echo htmlspecialchars($inv['category_name']); ?>
                  </div>
                  <div class="invoice-line">
                    <b>Subcategory:</b> <?php echo htmlspecialchars($inv['subcategory_name']); ?>
                  </div>
                  <div class="invoice-line">
                    <b>Amount:</b> 
                    <?php echo htmlspecialchars($currencySign . number_format($inv['price'], 2)); ?>
                  </div>
                  <div class="invoice-line">
                    <b>Date:</b> <?php echo htmlspecialchars($inv['created_at']); ?>
                  </div>
                  <div class="invoice-line mb-3">
                    <b>Status:</b> <?php echo htmlspecialchars($inv['status']); ?>
                  </div>

                  <!-- Show Pay Now button only if status is 'Pending' or 'PendingPayment' -->
                  <?php if (in_array($inv['status'], ['Pending','PendingPayment'])): ?>
                    <a 
                      href="pay_now.php?invoice_id=<?php echo urlencode($inv['id']); ?>" 
                      class="btn btn-sm btn-primary"
                    >
                      Pay Now
                    </a>
                  <?php endif; ?>
                </div><!-- /invoice-container -->
              </div>
            <?php endforeach; ?>
          </div><!-- /row -->

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
            <nav>
              <ul class="pagination justify-content-center mt-3">
                <?php for($i=1; $i<=$totalPages; $i++): ?>
                  <li class="page-item <?php if($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>">
                      <?php echo $i; ?>
                    </a>
                  </li>
                <?php endfor; ?>
              </ul>
            </nav>
          <?php endif; ?>
        <?php else: ?>
          <p class="text-muted">No invoices found.</p>
        <?php endif; ?>
      </div><!-- /container py-4 -->
    </div><!-- /col -->
  </div><!-- /row -->
</div><!-- /container-fluid -->

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
