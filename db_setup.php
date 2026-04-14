<?php
/**
 * Database Setup and Initialization Script
 * Creates all tables and inserts dummy data
 * Run this script once to initialize the database
 */

require_once 'config/db.php';

try {
    // Drop existing tables to ensure fresh schema (disable foreign key checks temporarily)
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop tables in correct order
    $tables_to_drop = ['order_items', 'cart', 'orders', 'products', 'categories', 'customers'];
    foreach ($tables_to_drop as $table) {
        try {
            $conn->exec("DROP TABLE IF EXISTS " . $table);
        } catch (PDOException $e) {
            // Table might not exist yet, that's ok
        }
    }
    
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "✓ Tables dropped successfully<br>";

    // Create Customers table
    $createCustomers = "CREATE TABLE IF NOT EXISTS customers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(15) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci";

    $conn->exec($createCustomers);
    echo "✓ Customers table created successfully<br>";

    // Create Categories table
    $createCategories = "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci";

    $conn->exec($createCategories);
    echo "✓ Categories table created successfully<br>";

    // Create Products table
    $createProducts = "CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        stock INT NOT NULL DEFAULT 0,
        image_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci";

    $conn->exec($createProducts);
    echo "✓ Products table created successfully<br>";

    // Create Cart table
    $createCart = "CREATE TABLE IF NOT EXISTS cart (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci";

    $conn->exec($createCart);
    echo "✓ Cart table created successfully<br>";

    // Create Orders table
    $createOrders = "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        customer_id INT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci";

    $conn->exec($createOrders);
    echo "✓ Orders table created successfully<br>";

    // Create Order Items table (to store order details)
    $createOrderItems = "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci";

    $conn->exec($createOrderItems);
    echo "✓ Order Items table created successfully<br>";

    echo "<br><strong>Inserting sample data...</strong><br>";

    // Insert sample customers
    $customers = [
        ['John Doe', 'john@example.com', '555-0101'],
        ['Jane Smith', 'jane@example.com', '555-0102'],
        ['Michael Brown', 'michael@example.com', '555-0103'],
        ['Sarah Johnson', 'sarah@example.com', '555-0104']
    ];

    $stmt = $conn->prepare("INSERT INTO customers (name, email, phone) VALUES (:name, :email, :phone)");
    
    foreach ($customers as $customer) {
        $stmt->execute([
            ':name' => $customer[0],
            ':email' => $customer[1],
            ':phone' => $customer[2]
        ]);
    }
    echo "✓ Sample customers inserted<br>";

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
    echo "✓ Sample categories inserted<br>";

    // Insert sample products (food menu items for FeastFlow)
    $products = [
        ['Margherita Pizza', 12.99, 20],
        ['Grilled Chicken Burger', 8.99, 35],
        ['Vegetable Stir Fry', 9.99, 25],
        ['Classic Caesar Salad', 7.99, 40],
        ['Ribeye Steak (12 oz)', 24.99, 15],
        ['Garlic Bread', 3.99, 50],
        ['Chocolate Lava Cake', 5.99, 30],
        ['Iced Coffee', 4.99, 100]
    ];

    $stmt = $conn->prepare("INSERT INTO products (name, price, stock) VALUES (:name, :price, :stock)");
    
    foreach ($products as $product) {
        $stmt->execute([
            ':name' => $product[0],
            ':price' => $product[1],
            ':stock' => $product[2]
        ]);
    }
    echo "✓ Sample menu items inserted<br>";

    // Insert sample cart items
    $cartItems = [
        [1, 2, 2],
        [1, 3, 1],
        [2, 1, 1]
    ];

    $stmt = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (:customer_id, :product_id, :quantity)");
    
    foreach ($cartItems as $item) {
        $stmt->execute([
            ':customer_id' => $item[0],
            ':product_id' => $item[1],
            ':quantity' => $item[2]
        ]);
    }
    echo "✓ Sample cart items inserted<br>";

    // Insert sample orders
    $orders = [
        [1, 1045.97, 'completed'],
        [2, 999.99, 'completed'],
        [3, 249.97, 'pending']
    ];

    $stmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, status) VALUES (:customer_id, :total_amount, :status)");
    
    foreach ($orders as $order) {
        $stmt->execute([
            ':customer_id' => $order[0],
            ':total_amount' => $order[1],
            ':status' => $order[2]
        ]);
    }
    echo "✓ Sample orders inserted<br>";

    echo "<br><strong style='color: green;'>Database setup completed successfully!</strong><br>";
    echo "You can now delete this file or access the application.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
