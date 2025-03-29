<?php
// settings.php
// Page to set the website currency sign, stored in an `app_settings` table

// session_start(); // Might only allow admin or certain role
require_once __DIR__ . '../../config/database.php';
$db = new Database();
$pdo = $db->connect();

$successMsg = '';
$errorMsg   = '';

// Handle saving currency sign
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['currency_sign'])) {
    $sign = $_POST['currency_sign'];
    try {
        // Upsert into the `app_settings` table
        $stmt = $pdo->prepare("
            INSERT INTO app_settings (name, value) VALUES ('currency_sign', :val)
            ON DUPLICATE KEY UPDATE value = :val
        ");
        $stmt->execute([':val' => $sign]);
        $successMsg = 'Currency sign updated successfully!';
    } catch (Exception $ex) {
        $errorMsg = 'Error updating: ' . $ex->getMessage();
    }
}

// Fetch current sign
$currentSign = '$';
$sStmt = $pdo->query("SELECT value FROM app_settings WHERE name='currency_sign' LIMIT 1");
if ($sRow = $sStmt->fetch(PDO::FETCH_ASSOC)) {
    $currentSign = $sRow['value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings</title>
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
      <!-- MAIN CONTENT -->
      <div class="p-4">
        <h1 class="text-2xl font-bold mb-4">Settings</h1>
        
        <?php if ($successMsg): ?>
          <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            <?php echo htmlspecialchars($successMsg); ?>
          </div>
        <?php elseif ($errorMsg): ?>
          <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?php echo htmlspecialchars($errorMsg); ?>
          </div>
        <?php endif; ?>

        <div class="bg-white shadow rounded p-4">
          <h2 class="text-xl font-semibold mb-2">Website Currency Sign</h2>
          <form method="POST">
            <label class="block mb-2">Current Sign: <strong><?php echo htmlspecialchars($currentSign); ?></strong></label>
            <input 
              type="text"
              name="currency_sign"
              value="<?php echo htmlspecialchars($currentSign); ?>"
              class="border border-gray-300 rounded px-3 py-1 w-64 mb-4"
              required
            >
            <br>
            <button 
              type="submit"
              class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-500"
            >
              Save
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
