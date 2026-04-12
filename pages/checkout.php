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

<main class="main-content">
    <div class="page-header">
        <h1>Complete Your Order</h1>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($cart_result && $cart_result->rowCount() > 0): ?>
        <div class="checkout-layout">
            <!-- Order Review -->
            <div class="checkout-review">
                <h2>Order Summary</h2>
                <table class="data-table">
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
                        while($row = $cart_result->fetch()):
                        ?>
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?></td>
                            <td><?php echo $row['quantity']; ?></td>
                            <td><?php echo CURRENCY_SYMBOL . number_format($row['price'] * $row['quantity'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Checkout Form -->
            <div class="checkout-form">
                <h2>Complete Your Order</h2>
                <div class="order-summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <strong><?php echo CURRENCY_SYMBOL . number_format($cart_total, 2); ?></strong>
                    </div>
                    <div class="summary-row">
                        <span>Tax (10%):</span>
                        <strong><?php echo CURRENCY_SYMBOL . number_format($cart_total * 0.10, 2); ?></strong>
                    </div>
                    <div class="summary-row total">
                        <span>Final Total:</span>
                        <strong><?php echo CURRENCY_SYMBOL . number_format($cart_total * 1.10, 2); ?></strong>
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="action" value="checkout">
                        <button type="submit" class="btn btn-primary btn-block btn-large">
                            Place Order
                        </button>
                    </form>

                    <a href="cart.php" class="btn btn-secondary btn-block">Back to Cart</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>Your cart is empty</h2>
            <p>Please add items to your cart before checking out.</p>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    <?php endif; ?>
</main>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
