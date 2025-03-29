<?php
/**
 * patient_dashboard.php
 */

 if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Ensure user is a logged-in patient
if (!isset($_SESSION['patient_id']) || $_SESSION['usergroup'] !== 'Patient') {
  header('Location: login.php');
  exit();
}

// Require DB and the handler class
require_once __DIR__ . '../../config/Database.php';
require_once __DIR__ . '../../classes/PatientDashboardHandler.php';

// Create our DB instance + connect
$db = new Database();        // Our custom class implementing IDatabase
$pdo = $db->connect();

$patientId = $_SESSION['patient_id'];

// Instantiate the dashboard handler
$dashboardHandler = new PatientDashboardHandler($pdo);

// 1) Fetch currency sign
$currencySign = $dashboardHandler->fetchCurrencySign();

// 2) Calculate outstanding payment
$outstandingAmount = $dashboardHandler->calculateOutstanding($patientId);

// 3) Fetch all test requests for the table
$testResults = $dashboardHandler->fetchTestRequests($patientId);

// Check if there's a success message from pay_now flow
$paymentSuccess = (isset($_GET['payment']) && $_GET['payment'] === 'success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Patient Dashboard</title>
  <!-- Tailwind CSS via CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

<?php include 'navbar.php'; ?>
<div class="container-fluid">
  <div class="row">
    <div class="col-auto px-0">
      <?php include 'sidebar.php'; ?>
    </div>
    <div class="col">
      <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Welcome to Your Dashboard</h1>

        <!-- Payment success notification -->
        <?php if ($paymentSuccess): ?>
          <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            Payment was processed successfully!
          </div>
        <?php endif; ?>

        <!-- Outstanding Payment Card -->
        <div class="bg-white shadow rounded p-4 mb-5">
          <h2 class="text-xl font-semibold mb-2">Outstanding Payment</h2>
          <p class="mb-4">
            You currently owe 
            <span class="font-bold">
              <?php echo htmlspecialchars($currencySign . number_format($outstandingAmount, 2)); ?>
            </span>
            for pending radiology tests.
          </p>
          <?php if ($outstandingAmount > 0): ?>
            <!-- Only show 'Pay Now' button if there is a balance -->
            <a 
              href="pay_now.php" 
              class="inline-block bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-500"
            >
              Pay Now
            </a>
          <?php else: ?>
            <p class="text-gray-500">No outstanding balance at the moment.</p>
          <?php endif; ?>
        </div>

        <!-- Requested Tests Table -->
        <div class="bg-white shadow rounded p-4">
          <h2 class="text-xl font-semibold mb-2">Your Radiology Tests</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full text-left">
              <thead class="bg-gray-50 border-b">
                <tr>
                  <th class="px-4 py-2">Request ID</th>
                  <th class="px-4 py-2">Category</th>
                  <th class="px-4 py-2">Subcategory</th>
                  <th class="px-4 py-2">Price</th>
                  <th class="px-4 py-2">Requested On</th>
                  <th class="px-4 py-2">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($testResults) > 0): ?>
                  <?php foreach ($testResults as $r): ?>
                    <tr class="border-b">
                      <td class="px-4 py-2"><?php echo htmlspecialchars($r['id']); ?></td>
                      <td class="px-4 py-2"><?php echo htmlspecialchars($r['category_name']); ?></td>
                      <td class="px-4 py-2"><?php echo htmlspecialchars($r['subcategory_name']); ?></td>
                      <td class="px-4 py-2">
                        <?php echo htmlspecialchars($currencySign . number_format($r['price'], 2)); ?>
                      </td>
                      <td class="px-4 py-2"><?php echo htmlspecialchars($r['created_at']); ?></td>
                      <td class="px-4 py-2">
                        <?php if ($r['status'] === 'Completed'): ?>
                          <span class="text-green-600 font-bold">Completed</span>
                        <?php elseif ($r['status'] === 'Canceled'): ?>
                          <span class="text-gray-500 font-bold">Canceled</span>
                        <?php elseif ($r['status'] === 'Paid'): ?>
                          <span class="text-blue-500 font-bold">Paid</span>
                        <?php else: ?>
                          <span class="text-red-500 font-bold"><?php echo htmlspecialchars($r['status']); ?></span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="px-4 py-2 text-center text-gray-500">
                      No tests found.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <!-- Example of simple pagination UI (no actual logic here) -->
          <div class="mt-4 flex justify-center space-x-2">
            <a href="?page=1" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">1</a>
            <a href="?page=2" class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded">2</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
