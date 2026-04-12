<?php
/**
 * Home page - Dashboard
 */
require_once dirname(__FILE__) . '/config/config.php';
require_once dirname(__FILE__) . '/classes/Product.php';
require_once dirname(__FILE__) . '/classes/Customer.php';
require_once dirname(__FILE__) . '/classes/Order.php';

$page_title = 'Dashboard';

$product       = new Product($conn);
$customer      = new Customer($conn);
$order         = new Order($conn);
$product_count = $product->getCount();
$customer_count= $customer->getCount();
$order_count   = $order->getCount();
$total_revenue = $order->getTotalRevenue();
?>
<?php include(dirname(__FILE__) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__) . '/includes/navbar.php'); ?>

<!-- Hero -->
<div class="hero-section">
  <div class="hero-badge">🟡 Live System</div>
  <h1>Welcome back to FeastFlow</h1>
  <p>Manage your menu, track orders, and serve your customers — all from one place.</p>
</div>

<!-- Stats -->
<section>
  <h2 class="mb-2">Overview</h2>
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-icon"><?php echo svg_icon('food', '24'); ?></div>
      <div class="stat-number"><?php echo $product_count; ?></div>
      <div class="stat-label">Menu Items</div>
      <a href="pages/products.php" class="stat-link">View Menu →</a>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><?php echo svg_icon('users', '24'); ?></div>
      <div class="stat-number"><?php echo $customer_count; ?></div>
      <div class="stat-label">Customers</div>
      <a href="pages/customers.php" class="stat-link">View Customers →</a>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><?php echo svg_icon('orders', '24'); ?></div>
      <div class="stat-number"><?php echo $order_count; ?></div>
      <div class="stat-label">Orders Received</div>
      <a href="pages/orders.php" class="stat-link">View Orders →</a>
    </div>
    <div class="stat-card">
      <div class="stat-icon"><?php echo svg_icon('checkout', '24'); ?></div>
      <div class="stat-number">$<?php echo number_format($total_revenue, 2); ?></div>
      <div class="stat-label">Total Revenue</div>
      <a href="pages/orders.php" class="stat-link">View Revenue →</a>
    </div>
  </div>
</section>

<!-- Features -->
<section>
  <h2 class="mb-2">System Features</h2>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon"><?php echo svg_icon('menu', '32'); ?></div>
      <h3>SQL Injection Protection</h3>
      <p>All database queries use PDO prepared statements for maximum security.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><?php echo svg_icon('orders', '32'); ?></div>
      <h3>Database Design</h3>
      <p>Well-structured database with proper relationships and constraints.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><?php echo svg_icon('checkout', '32'); ?></div>
      <h3>Full CRUD Operations</h3>
      <p>Create, Read, Update, and Delete operations for all entities.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><?php echo svg_icon('food', '32'); ?></div>
      <h3>Responsive Design</h3>
      <p>Fully responsive interface that works on desktop, tablet, and mobile.</p>
    </div>
  </div>
</section>

<!-- Quick Actions -->
<section>
  <h2 class="mb-2">Quick Actions</h2>
  <div class="action-links">
    <a href="pages/products.php?action=add" class="btn btn-primary"><?php echo svg_icon('food', '16'); ?> Add Menu Item</a>
    <a href="pages/customers.php?action=add" class="btn btn-primary"><?php echo svg_icon('users', '16'); ?> Add Customer</a>
    <a href="pages/cart.php" class="btn btn-secondary"><?php echo svg_icon('checkout', '16'); ?> View Cart</a>
    <a href="pages/orders.php" class="btn btn-secondary"><?php echo svg_icon('orders', '16'); ?> View All Orders</a>
  </div>
</section>

<?php include(dirname(__FILE__) . '/includes/footer.php'); ?>