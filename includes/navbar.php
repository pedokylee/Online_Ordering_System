<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_dir  = basename(dirname($_SERVER['PHP_SELF']));
$base         = isset($base_path) ? $base_path : '';

function nav_active($page, $current) {
  return $page === $current ? 'active' : '';
}
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <a href="<?php echo $base; ?>index.php" class="brand-logo">
      <div class="brand-icon"><?php echo svg_icon('food', '24'); ?></div>
      <div>
        <div class="brand-name">FeastFlow</div>
        <div class="brand-tagline">Food Ordering System</div>
      </div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="<?php echo $base; ?>index.php"
       class="nav-link <?php echo nav_active('index', $current_page); ?>">
      <span class="nav-icon"><?php echo svg_icon('menu', '18'); ?></span> Dashboard
    </a>

    <div class="nav-section-label">Catalog</div>
    <a href="<?php echo $base; ?>pages/products.php"
       class="nav-link <?php echo nav_active('products', $current_page); ?>">
      <span class="nav-icon"><?php echo svg_icon('food', '18'); ?></span> Menu Items
    </a>
    <a href="<?php echo $base; ?>pages/categories.php"
       class="nav-link <?php echo nav_active('categories', $current_page); ?>">
      <span class="nav-icon"><?php echo svg_icon('menu', '18'); ?></span> Categories
    </a>

    <div class="nav-section-label">Orders</div>
    <a href="<?php echo $base; ?>pages/cart.php"
       class="nav-link <?php echo nav_active('cart', $current_page); ?>">
      <span class="nav-icon"><?php echo svg_icon('checkout', '18'); ?></span> Cart
      <?php
        $cart_count = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;
        if ($cart_count > 0):
      ?>
        <span class="nav-badge"><?php echo $cart_count; ?></span>
      <?php endif; ?>
    </a>
    <a href="<?php echo $base; ?>pages/orders.php"
       class="nav-link <?php echo nav_active('orders', $current_page); ?>">
      <span class="nav-icon"><?php echo svg_icon('orders', '18'); ?></span> All Orders
    </a>

    <div class="nav-section-label">People</div>
    <a href="<?php echo $base; ?>pages/customers.php"
       class="nav-link <?php echo nav_active('customers', $current_page); ?>">
      <span class="nav-icon"><?php echo svg_icon('users', '18'); ?></span> Customers
    </a>
  </nav>

  <div class="sidebar-footer">
    <p>FeastFlow &copy; <?php echo date('Y'); ?></p>
  </div>
</aside>

<!-- Main content wrapper -->
<div class="main-content">

<!-- Top Bar -->
<header class="topbar">
  <div class="topbar-left">
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
      &#9776;
    </button>
    <span class="topbar-title">
      <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?>
    </span>
  </div>
  <div class="topbar-right">
    <a href="<?php echo $base; ?>pages/products.php?action=add" class="topbar-btn primary">+ Add Item</a>
    <a href="<?php echo $base; ?>pages/cart.php" class="topbar-btn cart-btn">
      <?php echo svg_icon('checkout', '18'); ?>
      <?php if ($cart_count > 0): ?>
        <span class="cart-count"><?php echo $cart_count; ?></span>
      <?php endif; ?>
    </a>
  </div>
</header>

<div class="content-area">