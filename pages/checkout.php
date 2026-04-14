<?php
/**
 * Checkout page - Convert cart to order
 */

require_once dirname(__FILE__, 2) . '/config/config.php';
require_once dirname(__FILE__, 2) . '/classes/Cart.php';
require_once dirname(__FILE__, 2) . '/classes/Order.php';
require_once dirname(__FILE__, 2) . '/classes/Product.php';

$page_title = 'Complete Your Order';
$cart = new Cart($conn);
$order = new Order($conn);
$product = new Product($conn);
$message = '';
$error = '';

// Demo customer ID
$customer_id = 1;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $cart_result = $cart->getCart($customer_id);
    
    if (!$cart_result || $cart_result->rowCount() === 0) {
        $error = "Cart is empty!";
    } else {
        $total = $cart->getCartTotal($customer_id);
        
        // Create order using prepared statement
        $order_id = $order->create($customer_id, $total * 1.10, 'pending');
        
        if ($order_id) {
            // Add items to order
            $cart_result_items = $cart->getCart($customer_id);
            $success = true;
            
            while($item = $cart_result_items->fetch()) {
                // Reduce stock
                if (!$product->reduceStock($item['product_id'], $item['quantity'])) {
                    $success = false;
                    break;
                }
                
                // Add item to order
                if (!$order->addItemToOrder($order_id, $item['product_id'], $item['quantity'], $item['price'])) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                // Clear cart
                $cart->clearCart($customer_id);
                
                $message = "✓ Order placed successfully! Order ID: " . $order_id;
                echo "<script>setTimeout(function() { window.location.href = 'orders.php'; }, 2000);</script>";
            } else {
                $error = "Error processing order!";
            }
        } else {
            $error = "Error creating order!";
        }
    }
}

$cart_result = $cart->getCart($customer_id);
$cart_total = $cart->getCartTotal($customer_id);

?>
<?php include(dirname(__FILE__, 2) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__, 2) . '/includes/navbar.php'); ?>

<div class="content-area">
<main class="main-content">
    <div class="page-header container">
        <div class="page-header-text">
            <h1>Checkout</h1>
            <p>Review your cart and complete the order.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success container"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger container"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($cart_result && $cart_result->rowCount() > 0): ?>
        <div class="cart-layout-grid container">
            <div class="table-container">
                <div class="table-toolbar">
                    <h3>Order Items</h3>
                </div>
                <div class="table-overflow">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cart_result = $cart->getCart($customer_id);
                            $grand_total = 0;
                            while($row = $cart_result->fetch()):
                                $sub = $row['price'] * $row['quantity'];
                                $grand_total += $sub;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td>$<?php echo number_format($row['price'], 2); ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td class="fw-600">$<?php echo number_format($sub, 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="order-summary">
                <div class="order-summary-header"><h3>Summary</h3></div>
                <div class="order-summary-body">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($grand_total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($grand_total * 0.10, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($grand_total * 1.10, 2); ?></span>
                    </div>

                    <form method="POST" action="" class="mt-3">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
                            Place Order
                        </button>
                    </form>
                    <a href="cart.php" class="btn btn-secondary btn-lg" style="width:100%; margin-top:0.5rem; display:block;">← Back to Cart</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state container">
            <div class="empty-icon"><?php echo svg_icon('checkout', '48'); ?></div>
            <h2>Your cart is empty</h2>
            <p>Please add items to your cart before checking out.</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php endif; ?>
</main>
</div>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
