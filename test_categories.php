<?php
require_once 'config/db.php';
require_once 'classes/Category.php';

$category = new Category($conn);
$result = $category->read();

echo "=== Category Test Results ===\n";

if ($result) {
    $categories = $result->fetchAll(PDO::FETCH_ASSOC);
    echo "Categories found: " . count($categories) . "\n\n";
    
    foreach ($categories as $c) {
        echo "ID: {$c['id']}\n";
        echo "Name: {$c['name']}\n";
        echo "Description: {$c['description']}\n";
        echo "Created: {$c['created_at']}\n";
        echo "---\n";
    }
} else {
    echo "No categories found or query failed\n";
}
?>
