<?php
/**
 * Product Class - CRUD Operations for Products
 * Uses PDO prepared statements for SQL injection protection
 */

class Product {
    private $conn;
    private $table = 'products';

    // Properties
    public $id;
    public $name;
    public $price;
    public $stock;
    public $image_path;
    public $created_at;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * CREATE - Add a new product
     * @param string $name Product name
     * @param float $price Product price
     * @param int $stock Product stock quantity
     * @param string $image_path Optional image path
     * @return bool Success status
     */
    public function create($name, $price, $stock, $image_path = null) {
        $query = "INSERT INTO " . $this->table . " (name, price, stock, image_path) VALUES (:name, :price, :stock, :image_path)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':stock' => $stock,
                ':image_path' => $image_path
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get all products
     * @return PDOStatement Result set
     */
    public function read() {
        $query = "SELECT id, name, price, stock, image_path, created_at FROM " . $this->table . " ORDER BY created_at DESC";
        try {
            return $this->conn->query($query);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get single product by ID
     * @param int $id Product ID
     * @return array Product data or empty array
     */
    public function getById($id) {
        $query = "SELECT id, name, price, stock, image_path, created_at FROM " . $this->table . " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * UPDATE - Update product information
     * @param int $id Product ID
     * @param string $name Product name
     * @param float $price Product price
     * @param int $stock Product stock
     * @param string $image_path Optional image path
     * @return bool Success status
     */
    public function update($id, $name, $price, $stock, $image_path = null) {
        $query = "UPDATE " . $this->table . " SET name = :name, price = :price, stock = :stock";
        
        if ($image_path !== null) {
            $query .= ", image_path = :image_path";
        }
        
        $query .= " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $params = [
                ':id' => $id,
                ':name' => $name,
                ':price' => $price,
                ':stock' => $stock
            ];
            
            if ($image_path !== null) {
                $params[':image_path'] = $image_path;
            }
            
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * DELETE - Remove a product
     * @param int $id Product ID
     * @return bool Success status
     */
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get products with pagination
     * @param int $limit Items per page
     * @param int $offset Starting position
     * @return PDOStatement Result set
     */
    public function getPaginated($limit, $offset) {
        $query = "SELECT id, name, price, stock, image_path, created_at FROM " . $this->table . 
                 " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get total product count
     * @return int Total number of products
     */
    public function getCount() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        try {
            $result = $this->conn->query($query);
            $row = $result->fetch();
            return $row['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Check if product has sufficient stock
     * @param int $id Product ID
     * @param int $quantity Required quantity
     * @return bool Stock availability
     */
    public function hasStock($id, $quantity) {
        $product = $this->getById($id);
        return isset($product['stock']) && $product['stock'] >= $quantity;
    }

    /**
     * Reduce stock after purchase
     * @param int $id Product ID
     * @param int $quantity Quantity to reduce
     * @return bool Success status
     */
    public function reduceStock($id, $quantity) {
        $query = "UPDATE " . $this->table . " SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':quantity' => $quantity
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Handle product image upload
     * @param array $file $_FILES['image'] array
     * @return string|false Image path if successful, false otherwise
     */
    public function uploadImage($file) {
        // Validate file
        if (!isset($file['tmp_name']) || !isset($file['name'])) {
            return false;
        }

        // Check file size (max 5MB)
        $max_size = 5242880; // 5MB in bytes
        if ($file['size'] > $max_size) {
            return false;
        }

        // Allowed file types
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return false;
        }

        // Generate unique filename
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . uniqid() . '.' . $file_ext;
        $upload_dir = dirname(__FILE__) . '/../uploads/products/';
        $upload_path = $upload_dir . $filename;

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return 'uploads/products/' . $filename;
        }

        return false;
    }

    /**
     * Delete product image
     * @param string $image_path Image path to delete
     * @return bool Success status
     */
    public function deleteImage($image_path) {
        if (empty($image_path)) {
            return true;
        }

        $file_path = dirname(__FILE__) . '/../' . $image_path;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }

        return true;
    }
}
?>
