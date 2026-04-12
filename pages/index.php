<?php
/**
 * Home page - Dashboard
 */

require_once dirname(__FILE__, 2) . '/config/config.php';
require_once dirname(__FILE__, 2) . '/classes/Product.php';
require_once dirname(__FILE__, 2) . '/classes/Customer.php';
require_once dirname(__FILE__, 2) . '/classes/Order.php';

$page_title = 'Home';

// Get statistics
$product = new Product($conn);
$customer = new Customer($conn);
$order = new Order($conn);

$product_count = $product->getCount();
$customer_count = $customer->getCount();
$order_count = $order->getCount();
$total_revenue = $order->getTotalRevenue();

?>
<?php include(dirname(__FILE__, 2) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__, 2) . '/includes/navbar.php'); ?>

<main class="main-content">
    <div class="hero-section">
        <h1>Welcome to Online Ordering System</h1>
        <p>Manage Products, Customers, and Orders Efficiently</p>
    </div>

    <section class="dashboard-section">
        <h2>Dashboard Statistics</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $product_count; ?></div>
                <div class="stat-label">Total Products</div>
                <a href="products.php" class="stat-link">View Products →</a>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $customer_count; ?></div>
                <div class="stat-label">Total Customers</div>
                <a href="customers.php" class="stat-link">View Customers →</a>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $order_count; ?></div>
                <div class="stat-label">Total Orders</div>
                <a href="orders.php" class="stat-link">View Orders →</a>
            </div>
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
                <div class="stat-label">Total Revenue</div>
                <a href="orders.php" class="stat-link">View Revenue →</a>
            </div>
        </div>
    </section>

    <section class="features-section">
        <h2>Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>SQL Injection Protection</h3>
                <p>All database queries use MySQLi prepared statements for maximum security.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Database Design</h3>
                <p>Well-structured database with proper relationships and constraints.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <h3>Full CRUD Operations</h3>
                <p>Create, Read, Update, and Delete operations for all entities.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎨</div>
                <h3>Modern UI Design</h3>
                <p>Clean and responsive interface for better user experience.</p>
            </div>
        </div>
    </section>

    <section class="quick-links">
        <h2>Quick Actions</h2>
        <div class="action-links">
            <a href="products.php?action=add" class="btn btn-primary">+ Add Product</a>
            <a href="customers.php?action=add" class="btn btn-primary">+ Add Customer</a>
            <a href="cart.php" class="btn btn-secondary">View Cart</a>
            <a href="orders.php" class="btn btn-secondary">View Orders</a>
        </div>
    </section>
</main>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
