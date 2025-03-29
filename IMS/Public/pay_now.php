<?php
/**
 * pay_now.php
 * Show payment method options, then finalize payment.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only patients can pay
if (!isset($_SESSION['patient_id']) || $_SESSION['usergroup'] !== 'Patient') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

$patientId = $_SESSION['patient_id'];

// Step 1: If no POST yet, show the payment method form
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Pay Now</title>
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
            <h1 class="text-2xl font-bold mb-4">Choose Payment Method</h1>
            <form method="POST" class="bg-white p-4 rounded shadow max-w-md">
              <div class="mb-4">
                <label class="block mb-2 font-semibold">Select a Payment Method</label>
                <div>
                  <label class="inline-flex items-center space-x-2 mb-2">
                    <input type="radio" name="payment_method" value="visa" required>
                    <img src="images/visa_logo.png" alt="Visa" class="h-5"> 
                    <span>Visa / MasterCard</span>
                  </label>
                </div>
                <div>
                  <label class="inline-flex items-center space-x-2 mb-2">
                    <input type="radio" name="payment_method" value="paypal">
                    <img src="images/paypal_logo.png" alt="PayPal" class="h-5"> 
                    <span>PayPal</span>
                  </label>
                </div>
                <!-- Add more options if you want -->
              </div>

              <!-- (Optional) If you integrate Stripe or PayPal, you'd do that here -->
              <button 
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500"
              >
                Proceed to Payment
              </button>
            </form>
          </div>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit();
}

// Step 2: If POST, user selected a payment method
$method = $_POST['payment_method'] ?? '';
if (!in_array($method, ['visa','paypal'])) {
    // Invalid method
    header('Location: pay_now.php');
    exit();
}

// Securely handle your payment here, e.g. Stripe/PayPal integration
// We'll assume success for demonstration

// Suppose the payment is successful. We update all "Pending" or "PendingPayment" rows to "Paid"
$updateQuery = "
    UPDATE radiology_requests
    SET status = 'Paid'
    WHERE patient_id = :pid
      AND status IN ('Pending','PendingPayment')
";
$stmt = $pdo->prepare($updateQuery);
$stmt->execute([':pid' => $patientId]);

// Redirect back to dashboard with success message
header('Location: patient_invoice.php?payment=success');
exit();
