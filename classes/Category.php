<?php
/**
 * Category class - Handle all category CRUD operations
 */
class Category {
    private $conn;
    private $table = 'categories';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create category
    public function create($name, $description = '') {
        $query = "INSERT INTO {$this->table} (name, description) 
                  VALUES (:name, :description)";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':name' => $name,
            ':description' => $description
        ]);
    }

    // Read all categories
    public function read() {
        $query = "SELECT id, name, description, created_at 
                  FROM {$this->table} 
                  ORDER BY name ASC";
        
        return $this->conn->query($query);
    }

    // Get single category by ID
    public function getById($id) {
        $query = "SELECT id, name, description, created_at 
                  FROM {$this->table} 
                  WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update category
    public function update($id, $name, $description = '') {
        $query = "UPDATE {$this->table} 
                  SET name = :name, description = :description 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':description' => $description
        ]);
    }

    // Delete category
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([':id' => $id]);
    }

    // Get count of categories
    public function getCount() {
        $query = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->conn->query($query);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return $row['count'] ?? 0;
    }
}
?>
