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
    public $created_at;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * CREATE - Add a new product
     * @param string $name Product name
     * @param float $price Product price
     * @param int $stock Product stock quantity
     * @return bool Success status
     */
    public function create($name, $price, $stock) {
        $query = "INSERT INTO " . $this->table . " (name, price, stock) VALUES (:name, :price, :stock)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':stock' => $stock
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
        $query = "SELECT id, name, price, stock, created_at FROM " . $this->table . " ORDER BY created_at DESC";
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
        $query = "SELECT id, name, price, stock, created_at FROM " . $this->table . " WHERE id = :id";
        
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
     * @return bool Success status
     */
    public function update($id, $name, $price, $stock) {
        $query = "UPDATE " . $this->table . " SET name = :name, price = :price, stock = :stock WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':price' => $price,
                ':stock' => $stock
            ]);
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
        $query = "SELECT id, name, price, stock, created_at FROM " . $this->table . 
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
}
?>
