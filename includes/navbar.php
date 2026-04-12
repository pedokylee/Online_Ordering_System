<?php
/**
 * Navbar include file
 */
?>
<nav class="navbar">
    <div class="navbar-container">
        <a href="/final/" class="navbar-logo">
            <span class="logo-icon"><?php echo svg_icon('food', '24'); ?></span> FeastFlow
        </a>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="/final/pages/products.php" class="nav-link"><?php echo svg_icon('menu', '18'); ?> Menu</a>
            </li>
            <li class="nav-item">
                <a href="/final/pages/customers.php" class="nav-link"><?php echo svg_icon('users', '18'); ?> Customers</a>
            </li>
            <li class="nav-item">
                <a href="/final/pages/cart.php" class="nav-link">
                    <?php echo svg_icon('cart', '18'); ?> Cart
                    <?php if (isset($_SESSION['customer_id'])): 
                        require_once(dirname(__FILE__, 2) . '/config/db.php');
                        require_once(dirname(__FILE__, 2) . '/classes/Cart.php');
                        $cart = new Cart($conn);
                        $count = $cart->getCartCount($_SESSION['customer_id']);
                        if ($count > 0): ?>
                        <span class="cart-badge"><?php echo $count; ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="/final/pages/orders.php" class="nav-link"><?php echo svg_icon('orders', '18'); ?> Orders</a>
            </li>
        </ul>
    </div>
</nav>
