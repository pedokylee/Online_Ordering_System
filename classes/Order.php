<?php
/**
 * Order Class - CRUD Operations for Orders
 * Uses PDO prepared statements for SQL injection protection
 */

class Order {
    private $conn;
    private $table = 'orders';
    private $orderItemsTable = 'order_items';

    // Properties
    public $id;
    public $customer_id;
    public $total_amount;
    public $status;
    public $created_at;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * CREATE - Create a new order from cart
     * @param int $customer_id Customer ID
     * @param float $total_amount Order total
     * @param string $status Order status
     * @return int Order ID or false on failure
     */
    public function create($customer_id, $total_amount, $status = 'pending') {
        $query = "INSERT INTO " . $this->table . " (customer_id, total_amount, status) VALUES (:customer_id, :total_amount, :status)";
        
        try {
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute([
                ':customer_id' => $customer_id,
                ':total_amount' => $total_amount,
                ':status' => $status
            ])) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Add items to order
     * @param int $order_id Order ID
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @param float $price Unit price
     * @return bool Success status
     */
    public function addItemToOrder($order_id, $product_id, $quantity, $price) {
        $query = "INSERT INTO " . $this->orderItemsTable . " (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':quantity' => $quantity,
                ':price' => $price
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get all orders
     * @return PDOStatement Result set
     */
    public function read() {
        $query = "SELECT o.id, o.customer_id, o.total_amount, o.status, o.created_at,
                         c.name, c.email
                  FROM " . $this->table . " o
                  JOIN customers c ON o.customer_id = c.id
                  ORDER BY o.created_at DESC";
        
        try {
            return $this->conn->query($query);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get single order by ID
     * @param int $id Order ID
     * @return array Order data or empty array
     */
    public function getById($id) {
        $query = "SELECT o.id, o.customer_id, o.total_amount, o.status, o.created_at,
                         c.name, c.email, c.phone
                  FROM " . $this->table . " o
                  JOIN customers c ON o.customer_id = c.id
                  WHERE o.id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get orders for specific customer
     * @param int $customer_id Customer ID
     * @return PDOStatement Result set
     */
    public function getByCustomerId($customer_id) {
        $query = "SELECT id, customer_id, total_amount, status, created_at
                  FROM " . $this->table . "
                  WHERE customer_id = :customer_id
                  ORDER BY created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':customer_id' => $customer_id]);
            return $stmt;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get order items
     * @param int $order_id Order ID
     * @return PDOStatement Result set
     */
    public function getOrderItems($order_id) {
        $query = "SELECT oi.id, oi.product_id, oi.quantity, oi.price,
                         p.name
                  FROM " . $this->orderItemsTable . " oi
                  JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':order_id' => $order_id]);
            return $stmt;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * UPDATE - Update order status
     * @param int $id Order ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * DELETE - Remove an order
     * @param int $id Order ID
     * @return bool Success status
     */
    public function delete($id) {
        try {
            // First delete order items
            $query = "DELETE FROM " . $this->orderItemsTable . " WHERE order_id = :order_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':order_id' => $id]);

            // Then delete order
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get total number of orders
     * @return int Total orders
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
     * Get orders with pagination
     * @param int $limit Items per page
     * @param int $offset Starting position
     * @return PDOStatement Result set
     */
    public function getPaginated($limit, $offset) {
        $query = "SELECT o.id, o.customer_id, o.total_amount, o.status, o.created_at,
                         c.name, c.email
                  FROM " . $this->table . " o
                  JOIN customers c ON o.customer_id = c.id
                  ORDER BY o.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
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
     * Get total revenue
     * @return float Total revenue
     */
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_amount) as revenue FROM " . $this->table . " WHERE status = 'completed'";
        try {
            $result = $this->conn->query($query);
            $row = $result->fetch();
            return $row['revenue'] ? $row['revenue'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>
