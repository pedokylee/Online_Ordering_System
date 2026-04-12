<?php
require_once 'config/db.php';

try {
    // Insert sample categories
    $categories = [
        ['Pizzas', 'Fresh and delicious pizzas with various toppings'],
        ['Burgers', 'Juicy burgers made with premium ingredients'],
        ['Salads', 'Healthy fresh salads for nutritious meals'],
        ['Desserts', 'Sweet treats and delectable desserts'],
        ['Beverages', 'Refreshing drinks and hot beverages']
    ];

    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
    
    foreach ($categories as $category) {
        $stmt->execute([
            ':name' => $category[0],
            ':description' => $category[1]
        ]);
    }
    
    echo "✓ Categories inserted successfully!\n";
    
    // Verify
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "Total categories now: " . $row['count'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
