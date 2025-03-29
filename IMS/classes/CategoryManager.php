<?php
/**
 * =============================================================================
 * classes/CategoryManager.php
 * =============================================================================
 * This class encapsulates all CRUD operations for Categories and Subcategories:
 *   - Create Category
 *   - Add Subcategory
 *   - Delete Category/Subcategory
 *   - Edit Category/Subcategory
 *   - Fetch Categories & Subcategories
 *
 * (OOP, Method Invocation, Completeness):
 *  - All DB interactions for categories in one cohesive class.
 *  - i handled all relevant operations here.
 * =============================================================================
 */

class CategoryManager
{
    private PDO $pdo;

    /**
     * Constructor requires a PDO instance for DB operations.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new category.
     */
    public function createCategory(string $category_name, float $price): bool
    {
        try {
            // Insert category
            $stmt = $this->pdo->prepare("INSERT INTO test_categories (category_name, price) VALUES (:cn, :pr)");
            $stmt->bindParam(':cn', $category_name);
            $stmt->bindParam(':pr', $price);
            $stmt->execute();

            // Get last inserted ID
            $categoryId = $this->pdo->lastInsertId();

            // Create folder path
            $filePath = "uploads/category_" . $categoryId;
            if (!is_dir(__DIR__ . '/../../' . $filePath)) {
                mkdir(__DIR__ . '/../../' . $filePath, 0777, true);
            }

            // Update the category row with file path
            $stmt2 = $this->pdo->prepare("UPDATE test_categories SET file_path = :fp WHERE id = :id");
            $stmt2->execute([
                ':fp' => $filePath,
                ':id' => $categoryId
            ]);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Add a new subcategory.
     */
    public function addSubcategory(int $category_id, string $subcategory_name, float $sub_price): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO test_subcategories (category_id, subcategory_name, price)
                VALUES (:cid, :scn, :spr)
            ");
            $stmt->bindParam(':cid', $category_id);
            $stmt->bindParam(':scn', $subcategory_name);
            $stmt->bindParam(':spr', $sub_price);
            $stmt->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Delete a category (and possibly cascade subcategories).
     */
    public function deleteCategory(int $catId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM test_categories WHERE id = :id");
            $stmt->bindParam(':id', $catId);
            $stmt->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Delete a subcategory.
     */
    public function deleteSubcategory(int $subId): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM test_subcategories WHERE id = :id");
            $stmt->bindParam(':id', $subId);
            $stmt->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Edit an existing category.
     */
    public function editCategory(int $catId, string $newName, float $newPrice): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE test_categories
                SET category_name = :cname, price = :cprice
                WHERE id = :cid
            ");
            $stmt->bindParam(':cname', $newName);
            $stmt->bindParam(':cprice', $newPrice);
            $stmt->bindParam(':cid', $catId);
            $stmt->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Edit an existing subcategory.
     */
    public function editSubcategory(int $subId, string $newName, float $newPrice): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE test_subcategories
                SET subcategory_name = :scn, price = :spr
                WHERE id = :sid
            ");
            $stmt->bindParam(':scn', $newName);
            $stmt->bindParam(':spr', $newPrice);
            $stmt->bindParam(':sid', $subId);
            $stmt->execute();
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Fetch all categories in descending order of creation.
     */
    public function fetchCategories(): array
    {
        $query = "SELECT * FROM test_categories ORDER BY created_at DESC";
        $stmt  = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch subcategories grouped by category.
     */
    public function fetchSubcategoriesByCategory(array $categories): array
    {
        $subcatsByCategory = [];

        if (!empty($categories)) {
            $catIds = array_column($categories, 'id');
            $inQuery = implode(',', $catIds);

            // If no categories, skip
            if (!empty($inQuery)) {
                $querySub = "
                    SELECT *
                    FROM test_subcategories
                    WHERE category_id IN ($inQuery)
                    ORDER BY created_at DESC
                ";
                $stmtSub = $this->pdo->query($querySub);
                $subcategories = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

                foreach ($subcategories as $sub) {
                    $catId = $sub['category_id'];
                    if (!isset($subcatsByCategory[$catId])) {
                        $subcatsByCategory[$catId] = [];
                    }
                    $subcatsByCategory[$catId][] = $sub;
                }
            }
        }
        return $subcatsByCategory;
    }
}
