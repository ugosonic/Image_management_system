<?php


    require_once __DIR__ . '../../config/database.php';
$database = new Database();
$pdo = $database->connect();

$successMessage = '';
$errorMessage = '';


/**
 * A class responsible for handling the test request logic.
 * It interacts with the Database class (storage), processes requests, 
 * and provides the necessary data for the frontend (categories/subcategories).
 */
class TestRequestHandler
{
    private PDO $pdo;
    public string $successMessage = '';
    public string $errorMessage = '';

    /**
     * Constructor: Receives a PDO instance for database operations.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Handle test request form submission.
     * 
     * @param int $patientId  The patient's ID passed via GET parameter.
     * @return void
     */
    public function handleFormSubmission(int $patientId): void
    {
        // Ensure that we are dealing with the correct form submission action
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_test') {
            // Retrieve form data
            $cat_id = $_POST['category_id'] ?? null;
            $sub_id = $_POST['subcategory_id'] ?? null;

            // Insert into radiology_requests
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO radiology_requests (patient_id, category_id, subcategory_id)
                    VALUES (:pid, :cid, :sid)
                ");
                $stmt->execute([
                    ':pid' => $patientId,
                    ':cid' => $cat_id,
                    ':sid' => $sub_id
                ]);

                $this->successMessage = "Radiology test requested successfully!";
            } catch (Exception $ex) {
                $this->errorMessage = "Error requesting test: " . $ex->getMessage();
            }
        }
    }

    /**
     * Fetch all categories from test_categories table.
     *
     * @return array  An associative array of categories (id, category_name).
     */
    public function fetchCategories(): array
    {
        $catStmt = $this->pdo->query("SELECT id, category_name FROM test_categories ORDER BY category_name ASC");
        return $catStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all subcategories from test_subcategories table.
     * 
     * @return array  An associative array of subcategories (id, category_id, subcategory_name, price).
     */
    public function fetchSubcategories(): array
    {
        $subStmt = $this->pdo->query("SELECT id, category_id, subcategory_name, price FROM test_subcategories ORDER BY subcategory_name ASC");
        return $subStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// 1. Confirm we have a patient_id
if (!isset($_GET['patient_id'])) {
    die("No patient specified for consultation.");
}
$patientId = (int) $_GET['patient_id'];

// 2. Create and connect to the database (Storage component)
$database = new Database();
$pdo = $database->connect();

// 3. Instantiate our TestRequestHandler (Service/Component)
$handler = new TestRequestHandler($pdo);

// 4. Handle form submission if it occurs
$handler->handleFormSubmission($patientId);

// 5. Fetch categories and subcategories (Components/Services)
$categories  = $handler->fetchCategories();
$subcats     = $handler->fetchSubcategories();

// 6. Retrieve possible success or error messages
$successMessage = $handler->successMessage;
$errorMessage   = $handler->errorMessage;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Consultation for Patient #<?php echo htmlspecialchars($patientId); ?></title>
    <!-- Bootstrap 5 -->
    <link 
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
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

<div class="container my-5">
    <h1>Request Radiology Test for Patient #<?php echo htmlspecialchars($patientId); ?></h1>

    <!-- Notification -->
    <?php if (!empty($successMessage)): ?>
        <div id="successAlert" class="alert alert-success">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
        <script>
            setTimeout(() => {
                const alertElem = document.getElementById('successAlert');
                if (alertElem) alertElem.style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>
    <?php if (!empty($errorMessage)): ?>
        <div id="errorAlert" class="alert alert-danger">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <script>
            setTimeout(() => {
                const alertElem = document.getElementById('errorAlert');
                if (alertElem) alertElem.style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>

    <!-- Form to request test -->
    <form method="POST" class="card p-3">
        <input type="hidden" name="action" value="request_test">

        <!-- Category Dropdown -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Subcategory Dropdown -->
        <div class="mb-3">
            <label for="subcategory_id" class="form-label">Subcategory</label>
            <select name="subcategory_id" id="subcategory_id" class="form-select" required>
                <option value="">-- Select Subcategory --</option>
                <!-- Populated by JS based on category -->
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Request Test</button>
    </form>
</div>

<!-- Hidden data for subcategories (we'll filter with JS) -->
<script>
    const allSubcats = <?php echo json_encode($subcats); ?>; 
    // e.g. [ {id:1, category_id:1, subcategory_name:'MRI Brain', price:100}, ...]

    const categorySelect = document.getElementById('category_id');
    const subcatSelect   = document.getElementById('subcategory_id');

    categorySelect.addEventListener('change', function() {
        const selectedCatId = parseInt(this.value, 10);
        
        // Clear the subcategory dropdown
        subcatSelect.innerHTML = '<option value="">-- Select Subcategory --</option>';

        // Filter the subcategories for the selected category
        const filtered = allSubcats.filter(sc => sc.category_id === selectedCatId);

        // Populate the subcategory dropdown
        filtered.forEach(sc => {
            const opt = document.createElement('option');
            opt.value = sc.id; // subcategory_id
            opt.textContent = sc.subcategory_name;
            subcatSelect.appendChild(opt);
        });
    });
</script>

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
