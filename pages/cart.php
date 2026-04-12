<?php
/**
 * Cart page - Shopping cart management
 */

require_once dirname(__FILE__, 2) . '/config/config.php';
require_once dirname(__FILE__, 2) . '/classes/Cart.php';
require_once dirname(__FILE__, 2) . '/classes/Product.php';
require_once dirname(__FILE__, 2) . '/classes/Customer.php';

$page_title = 'Shopping Cart';
$cart = new Cart($conn);
$product = new Product($conn);
$customer = new Customer($conn);
$message = '';
$error = '';

// For demonstration, use customer ID 1
$customer_id = 1;

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');

    if ($action === 'add') {
        // ADD TO CART
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($product_id > 0 && $quantity > 0) {
            if ($product->hasStock($product_id, $quantity)) {
                if ($cart->addToCart($customer_id, $product_id, $quantity)) {
                    $message = "✓ Product added to cart!";
                } else {
                    $error = "Error adding product to cart!";
                }
            } else {
                $error = "Insufficient stock!";
            }
        } else {
            $error = "Invalid product or quantity!";
        }
    } elseif ($action === 'update') {
        // UPDATE QUANTITY
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($cart_id > 0) {
            if ($cart->updateQuantity($cart_id, $quantity)) {
                $message = "✓ Cart updated!";
            } else {
                $error = "Error updating cart!";
            }
        }
    } elseif ($action === 'remove') {
        // REMOVE FROM CART
        $cart_id = (int)($_POST['cart_id'] ?? 0);

        if ($cart_id > 0 && $cart->removeFromCart($cart_id)) {
            $message = "✓ Item removed from cart!";
        } else {
            $error = "Error removing item!";
        }
    } elseif ($action === 'clear') {
        // CLEAR ENTIRE CART
        if ($cart->clearCart($customer_id)) {
            $message = "✓ Cart cleared!";
        } else {
            $error = "Error clearing cart!";
        }
    }
}

// Get cart items
$cart_result = $cart->getCart($customer_id);
$cart_total = $cart->getCartTotal($customer_id);

?>
<?php include(dirname(__FILE__, 2) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__, 2) . '/includes/navbar.php'); ?>

<main class="main-content">
    <div class="page-header">
        <h1>Order Cart</h1>
        <p>Customer ID: <?php echo $customer_id; ?> (Demo Mode)</p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="cart-layout">
        <!-- Cart Items -->
        <div class="cart-items">
            <h2>Cart Items</h2>
            <?php if ($cart_result && $cart_result->rowCount() > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $cart_result->fetch()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="quantity" min="1" value="<?php echo $row['quantity']; ?>" 
                                               onchange="this.form.submit()" class="qty-input">
                                    </form>
                                </td>
                                <td><?php echo CURRENCY_SYMBOL . number_format($row['price'] * $row['quantity'], 2); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-icon btn-delete" title="Remove">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>Your cart is empty</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary">
            <div class="summary-box">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <strong><?php echo CURRENCY_SYMBOL . number_format($cart_total, 2); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Tax (10%):</span>
                    <strong><?php echo CURRENCY_SYMBOL . number_format($cart_total * 0.10, 2); ?></strong>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <strong><?php echo CURRENCY_SYMBOL . number_format($cart_total * 1.10, 2); ?></strong>
                </div>

                <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                
                <?php if ($cart_result && $cart_result->rowCount() > 0): ?>
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-secondary btn-block" onclick="return confirm('Clear entire cart?')">
                            Clear Cart
                        </button>
                    </form>
                <?php endif; ?>

                <a href="products.php" class="btn btn-ghost btn-block">Continue Shopping</a>
            </div>
        </div>
    </div>
</main>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
