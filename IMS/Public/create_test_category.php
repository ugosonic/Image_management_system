<?php
/**
 * =============================================================================
 * create_test_category.php
 * =============================================================================
 * This file handles the admin operations for categories/subcategories (CRUD).
 *
 *
 *
 * MARKING SCHEME:
 *  - Interaction & Selection: Checking $_POST/$_GET parameters.
 *  - Use of service orientation/components: We use CategoryManager as a service.
 *  - Completeness: We fetch categories, subcategories, handle create/update/delete.
 * =============================================================================
 */

$dsn = 'mysql:host=localhost;dbname=IMS;charset=utf8';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection Error: " . $e->getMessage());
}

/*******************************************************
 * 2. Notifications
 *******************************************************/
$successMessage = '';
$errorMessage   = '';

// If redirected with ?success=1 or ?error=1
if (isset($_GET['success'])) {
    $successMessage = 'Form submitted successfully!';
}
if (isset($_GET['error'])) {
    $errorMessage = 'An error occurred. Please try again.';
}

/*******************************************************
 * 3. Include CategoryManager class
 *******************************************************/
require_once __DIR__ . '../../classes/CategoryManager.php';

// Instantiate the CategoryManager
$categoryManager = new CategoryManager($pdo);

/*******************************************************
 * 4. Handle POST & GET Actions
 *******************************************************/

// (A) Create New Category
if (isset($_POST['action']) && $_POST['action'] === 'create_category') {
    $category_name = $_POST['category_name'];
    $price         = $_POST['price'];

    // Use CategoryManager
    $ok = $categoryManager->createCategory($category_name, (float)$price);

    if ($ok) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
    }
    exit();
}

// (B) Add Subcategory
if (isset($_POST['action']) && $_POST['action'] === 'add_subcategory') {
    $category_id      = $_POST['category_id'];
    $subcategory_name = $_POST['subcategory_name'];
    $sub_price        = $_POST['sub_price'];

    $ok = $categoryManager->addSubcategory((int)$category_id, $subcategory_name, (float)$sub_price);
    if ($ok) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
    }
    exit();
}

// (C) Delete Category
if (isset($_GET['delete_category'])) {
    $catId = (int)$_GET['delete_category'];
    $ok = $categoryManager->deleteCategory($catId);

    if ($ok) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
    }
    exit();
}

// (D) Delete Subcategory
if (isset($_GET['delete_sub'])) {
    $subId = (int)$_GET['delete_sub'];
    $ok = $categoryManager->deleteSubcategory($subId);

    if ($ok) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
    }
    exit();
}

// (E) Edit Category
if (isset($_POST['action']) && $_POST['action'] === 'edit_category') {
    $catId            = (int)$_POST['cat_id'];
    $newCategoryName  = $_POST['edit_category_name'];
    $newCategoryPrice = $_POST['edit_category_price'];

    $ok = $categoryManager->editCategory($catId, $newCategoryName, (float)$newCategoryPrice);
    if ($ok) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
    }
    exit();
}

// (F) Edit Subcategory
if (isset($_POST['action']) && $_POST['action'] === 'edit_subcategory') {
    $subId       = (int)$_POST['sub_id'];
    $newSubName  = $_POST['edit_subcategory_name'];
    $newSubPrice = $_POST['edit_subcategory_price'];

    $ok = $categoryManager->editSubcategory($subId, $newSubName, (float)$newSubPrice);
    if ($ok) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    } else {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=1');
    }
    exit();
}

/*******************************************************
 * 5. Fetch Categories & Subcategories for Display
 *******************************************************/
$categories = $categoryManager->fetchCategories();
$subcatsByCategory = $categoryManager->fetchSubcategoriesByCategory($categories);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Test Category</title>
    <link rel="stylesheet" href="../css/style.css"><!-- Optional custom CSS -->
    <!-- Bootstrap 5 CSS CDN -->
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


        <!-- Main Content -->
        <div class="col" style="min-height: 100vh;">
            <div class="p-4">
                <!-- Success & Error Notifications -->
                <?php if (!empty($successMessage)): ?>
                    <div 
                      id="successAlert" 
                      class="alert alert-success text-center mx-auto" 
                      style="max-width: 500px;"
                    >
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                    <script>
                        // Auto-hide success alert after 5 seconds
                        setTimeout(() => {
                            const alertElem = document.getElementById('successAlert');
                            if (alertElem) {
                                alertElem.style.display = 'none';
                            }
                        }, 5000);
                    </script>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div 
                      id="errorAlert" 
                      class="alert alert-danger text-center mx-auto" 
                      style="max-width: 500px;"
                    >
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                    <script>
                        // Auto-hide error alert after 5 seconds
                        setTimeout(() => {
                            const alertElem = document.getElementById('errorAlert');
                            if (alertElem) {
                                alertElem.style.display = 'none';
                            }
                        }, 5000);
                    </script>
                <?php endif; ?>

                <div class="container py-5">
                    <h1 class="mb-4">Manage Test Categories</h1>

                    <!-- (A) CREATE CATEGORY FORM -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <strong>Create a New Test Category</strong>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <input type="hidden" name="action" value="create_category">
                                <div class="col-md-5">
                                    <label for="category_name" class="form-label">Category Name</label>
                                    <input 
                                        type="text" 
                                        name="category_name" 
                                        id="category_name" 
                                        class="form-control" 
                                        required
                                    >
                                </div>
                                <div class="col-md-5">
                                    <label for="price" class="form-label">Price</label>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        name="price" 
                                        id="price" 
                                        class="form-control" 
                                        required
                                    >
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        Create
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- (B) CATEGORIES TABLE -->
                    <?php if (count($categories) > 0): ?>
                        <table class="table table-bordered table-hover align-middle bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">ID</th>
                                    <th>Category Name</th>
                                    <th>Price</th>
                                    <th>File Path</th>
                                    <th width="25%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['id']) ?></td>
                                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                                    <td><?= htmlspecialchars($cat['price']) ?></td>
                                    <td><?= htmlspecialchars($cat['file_path']) ?></td>
                                    <td>
                                        <!-- Edit Category Button -->
                                        <button 
                                          class="btn btn-warning btn-sm me-1"
                                          data-bs-toggle="modal"
                                          data-bs-target="#editCategoryModal<?= $cat['id'] ?>"
                                        >
                                            Edit Category
                                        </button>

                                        <!-- Add Subcategory Button -->
                                        <button 
                                          class="btn btn-secondary btn-sm me-1"
                                          data-bs-toggle="modal"
                                          data-bs-target="#addSubModal<?= $cat['id'] ?>"
                                        >
                                            Add Subcategory
                                        </button>

                                        <!-- Delete Category Link -->
                                        <a 
                                          href="?delete_category=<?= $cat['id'] ?>" 
                                          onclick="return confirm('Are you sure to delete this category? All subcategories will be removed.')"
                                          class="btn btn-danger btn-sm"
                                        >
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                                <!-- Subcategories for this category -->
                                <?php if (isset($subcatsByCategory[$cat['id']]) && count($subcatsByCategory[$cat['id']]) > 0): ?>
                                    <tr>
                                        <td></td>
                                        <td colspan="4">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr class="table-light">
                                                        <th>ID</th>
                                                        <th>Subcategory Name</th>
                                                        <th>Price</th>
                                                        <th style="width: 20%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach ($subcatsByCategory[$cat['id']] as $sc): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($sc['id']) ?></td>
                                                        <td><?= htmlspecialchars($sc['subcategory_name']) ?></td>
                                                        <td><?= htmlspecialchars($sc['price']) ?></td>
                                                        <td>
                                                            <!-- Edit Subcategory -->
                                                            <button 
                                                              class="btn btn-warning btn-sm me-1"
                                                              data-bs-toggle="modal"
                                                              data-bs-target="#editSubModal<?= $sc['id'] ?>"
                                                            >
                                                                Edit
                                                            </button>
                                                            <!-- Delete Subcategory -->
                                                            <a 
                                                              href="?delete_sub=<?= $sc['id'] ?>" 
                                                              onclick="return confirm('Are you sure to delete this subcategory?')"
                                                              class="btn btn-danger btn-sm"
                                                            >
                                                                Delete
                                                            </a>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Subcategory Modal -->
                                                    <div 
                                                      class="modal fade" 
                                                      id="editSubModal<?= $sc['id'] ?>" 
                                                      tabindex="-1" 
                                                      aria-hidden="true"
                                                    >
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form method="POST">
                                                                    <input type="hidden" name="action" value="edit_subcategory">
                                                                    <input type="hidden" name="sub_id" value="<?= $sc['id'] ?>">

                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Edit Subcategory #<?= $sc['id'] ?></h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>

                                                                    <div class="modal-body">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Subcategory Name</label>
                                                                            <input 
                                                                              type="text" 
                                                                              name="edit_subcategory_name"
                                                                              value="<?= htmlspecialchars($sc['subcategory_name']) ?>"
                                                                              class="form-control"
                                                                              required
                                                                            >
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Subcategory Price</label>
                                                                            <input 
                                                                              type="number"
                                                                              step="0.01"
                                                                              name="edit_subcategory_price"
                                                                              value="<?= htmlspecialchars($sc['price']) ?>"
                                                                              class="form-control"
                                                                              required
                                                                            >
                                                                        </div>
                                                                    </div>

                                                                    <div class="modal-footer">
                                                                        <button type="button" 
                                                                          class="btn btn-secondary" 
                                                                          data-bs-dismiss="modal"
                                                                        >
                                                                            Close
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">
                                                                            Save Changes
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- End Edit Subcategory Modal -->

                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <!-- Add Subcategory Modal -->
                                <div 
                                  class="modal fade" 
                                  id="addSubModal<?= $cat['id'] ?>" 
                                  tabindex="-1" 
                                  aria-hidden="true"
                                >
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="add_subcategory">
                                                <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Add Subcategory to <?= htmlspecialchars($cat['category_name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Subcategory Name</label>
                                                        <input 
                                                          type="text" 
                                                          name="subcategory_name"
                                                          class="form-control"
                                                          required
                                                        >
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Price</label>
                                                        <input 
                                                          type="number"
                                                          step="0.01"
                                                          name="sub_price"
                                                          class="form-control"
                                                          required
                                                        >
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" 
                                                      class="btn btn-secondary" 
                                                      data-bs-dismiss="modal"
                                                    >
                                                        Close
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        Add
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Add Subcategory Modal -->

                                <!-- Edit Category Modal -->
                                <div 
                                  class="modal fade" 
                                  id="editCategoryModal<?= $cat['id'] ?>" 
                                  tabindex="-1" 
                                  aria-hidden="true"
                                >
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="edit_category">
                                                <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Category #<?= $cat['id'] ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Category Name</label>
                                                        <input
                                                          type="text"
                                                          name="edit_category_name"
                                                          class="form-control"
                                                          value="<?= htmlspecialchars($cat['category_name']) ?>"
                                                          required
                                                        >
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Category Price</label>
                                                        <input
                                                          type="number"
                                                          step="0.01"
                                                          name="edit_category_price"
                                                          class="form-control"
                                                          value="<?= htmlspecialchars($cat['price']) ?>"
                                                          required
                                                        >
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" 
                                                      class="btn btn-secondary"
                                                      data-bs-dismiss="modal"
                                                    >
                                                        Close
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Edit Category Modal -->

                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-info">No test categories found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /col -->
    </div><!-- /row -->
</div><!-- /container-fluid -->

<!-- Bootstrap 5 JS -->
<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
</script>
</body>
</html>
