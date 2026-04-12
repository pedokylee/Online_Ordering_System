<?php
/**
 * Customer Class - CRUD Operations for Customers
 * Uses PDO prepared statements for SQL injection protection
 */

class Customer {
    private $conn;
    private $table = 'customers';

    // Properties
    public $id;
    public $name;
    public $email;
    public $phone;
    public $created_at;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * CREATE - Add a new customer
     * @param string $name Customer name
     * @param string $email Customer email
     * @param string $phone Customer phone
     * @return bool Success status
     */
    public function create($name, $email, $phone) {
        $query = "INSERT INTO " . $this->table . " (name, email, phone) VALUES (:name, :email, :phone)";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get all customers
     * @return PDOStatement Result set
     */
    public function read() {
        $query = "SELECT id, name, email, phone, created_at FROM " . $this->table . " ORDER BY created_at DESC";
        try {
            return $this->conn->query($query);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * READ - Get single customer by ID
     * @param int $id Customer ID
     * @return array Customer data or empty array
     */
    public function getById($id) {
        $query = "SELECT id, name, email, phone, created_at FROM " . $this->table . " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * READ - Get customer by email
     * @param string $email Customer email
     * @return array Customer data or empty array
     */
    public function getByEmail($email) {
        $query = "SELECT id, name, email, phone, created_at FROM " . $this->table . " WHERE email = :email";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * UPDATE - Update customer information
     * @param int $id Customer ID
     * @param string $name Customer name
     * @param string $email Customer email
     * @param string $phone Customer phone
     * @return bool Success status
     */
    public function update($id, $name, $email, $phone) {
        $query = "UPDATE " . $this->table . " SET name = :name, email = :email, phone = :phone WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':id' => $id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * DELETE - Remove a customer
     * @param int $id Customer ID
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
     * Get customers with pagination
     * @param int $limit Items per page
     * @param int $offset Starting position
     * @return PDOStatement Result set
     */
    public function getPaginated($limit, $offset) {
        $query = "SELECT id, name, email, phone, created_at FROM " . $this->table . 
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
     * Get total customer count
     * @return int Total number of customers
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
     * Check if email already exists
     * @param string $email Email address
     * @return bool Email exists status
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
