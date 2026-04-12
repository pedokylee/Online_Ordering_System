<?php
/**
 * Cart Class - CRUD Operations for Shopping Cart
 * Uses PDO prepared statements for SQL injection protection
 */

class Cart {
    private $conn;
    private $table = 'cart';

    // Properties
    public $id;
    public $customer_id;
    public $product_id;
    public $quantity;
    public $created_at;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * CREATE - Add item to cart
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @return bool Success status
     */
    public function addToCart($customer_id, $product_id, $quantity) {
        // Check if item already in cart
        $existing = $this->getCartItem($customer_id, $product_id);
        
        if ($existing) {
            // Update quantity if already exists
            return $this->updateQuantity($existing['id'], $existing['quantity'] + $quantity);
        }

        $query = "INSERT INTO " . $this->table . " (customer_id, product_id, quantity) VALUES (:customer_id, :product_id, :quantity)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':customer_id' => $customer_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get all cart items for a customer
     * @param int $customer_id Customer ID
     * @return PDOStatement Result set
     */
    public function getCart($customer_id) {
        $query = "SELECT c.id, c.customer_id, c.product_id, c.quantity, c.created_at,
                         p.name, p.price
                  FROM " . $this->table . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.customer_id = :customer_id
                  ORDER BY c.created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':customer_id' => $customer_id]);
            return $stmt;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get single cart item
     * @param int $customer_id Customer ID
     * @param int $product_id Product ID
     * @return array Cart item data or empty array
     */
    public function getCartItem($customer_id, $product_id) {
        $query = "SELECT id, quantity FROM " . $this->table . " 
                  WHERE customer_id = :customer_id AND product_id = :product_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':product_id' => $product_id
            ]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * UPDATE - Update cart item quantity
     * @param int $id Cart item ID
     * @param int $quantity New quantity
     * @return bool Success status
     */
    public function updateQuantity($id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($id);
        }

        $query = "UPDATE " . $this->table . " SET quantity = :quantity WHERE id = :id";
        
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
     * DELETE - Remove item from cart
     * @param int $id Cart item ID
     * @return bool Success status
     */
    public function removeFromCart($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Clear entire cart for a customer
     * @param int $customer_id Customer ID
     * @return bool Success status
     */
    public function clearCart($customer_id) {
        $query = "DELETE FROM " . $this->table . " WHERE customer_id = :customer_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':customer_id' => $customer_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get cart total price
     * @param int $customer_id Customer ID
     * @return float Total price
     */
    public function getCartTotal($customer_id) {
        $query = "SELECT SUM(c.quantity * p.price) as total FROM " . $this->table . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.customer_id = :customer_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':customer_id' => $customer_id]);
            $row = $stmt->fetch();
            return isset($row['total']) && $row['total'] !== null ? $row['total'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get number of items in cart
     * @param int $customer_id Customer ID
     * @return int Item count
     */
    public function getCartCount($customer_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE customer_id = :customer_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':customer_id' => $customer_id]);
            $row = $stmt->fetch();
            return $row['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>
