<?php
/**
 * Orders page - View and manage orders
 */

require_once dirname(__FILE__, 2) . '/config/config.php';
require_once dirname(__FILE__, 2) . '/classes/Order.php';

$page_title = 'Orders';
$order = new Order($conn);
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$message = '';
$error = '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? '');

    $valid_statuses = ['pending', 'confirmed', 'shipped', 'completed', 'cancelled'];
    
    if ($order_id > 0 && in_array($status, $valid_statuses)) {
        if ($order->updateStatus($order_id, $status)) {
            $message = "✓ Order status updated!";
        } else {
            $error = "Error updating order!";
        }
    } else {
        $error = "Invalid order or status!";
    }
}

// Get single order details
$order_details = [];
if ($action === 'view' && isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    $order_details = $order->getById($id);
}

?>
<?php include(dirname(__FILE__, 2) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__, 2) . '/includes/navbar.php'); ?>

<main class="main-content">
    <div class="page-header">
        <h1>Order Management</h1>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($action === 'view' && !empty($order_details)): ?>
        <!-- Order Details View -->
        <div class="order-detail">
            <div class="detail-header">
                <h2>Order #<?php echo htmlspecialchars($order_details['id']); ?></h2>
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
            </div>

            <div class="detail-grid">
                <div class="detail-section">
                    <h3>Order Information</h3>
                    <div class="detail-item">
                        <span class="label">Order ID:</span>
                        <span class="value"><?php echo htmlspecialchars($order_details['id']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Order Date:</span>
                        <span class="value"><?php echo date('M d, Y H:i', strtotime($order_details['created_at'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Total Amount:</span>
                        <span class="value highlight"><?php echo CURRENCY_SYMBOL . number_format($order_details['total_amount'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status:</span>
                        <span class="status-badge status-<?php echo htmlspecialchars($order_details['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($order_details['status'])); ?>
                        </span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Customer Information</h3>
                    <div class="detail-item">
                        <span class="label">Customer Name:</span>
                        <span class="value"><?php echo htmlspecialchars($order_details['name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo htmlspecialchars($order_details['email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Phone:</span>
                        <span class="value"><?php echo htmlspecialchars($order_details['phone']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Update Status Form -->
            <div class="update-status">
                <h3>Update Order Status</h3>
                <form method="POST" class="status-form">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" value="<?php echo $order_details['id']; ?>">
                    
                    <select name="status" class="form-select">
                        <option value="pending" <?php echo $order_details['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $order_details['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="shipped" <?php echo $order_details['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="completed" <?php echo $order_details['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $order_details['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </form>
            </div>

            <!-- Order Items -->
            <div class="order-items">
                <h3>Order Items</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $items_result = $order->getOrderItems($order_details['id']);
                        if ($items_result && $items_result->rowCount() > 0):
                            while($item = $items_result->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo CURRENCY_SYMBOL . number_format($item['price'], 2); ?></td>
                            <td><?php echo CURRENCY_SYMBOL . number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                        <?php 
                            endwhile;
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Orders List -->
        <div class="table-container">
            <h2>All Orders</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $order->read();
                    if ($result && $result->rowCount() > 0):
                        while($row = $result->fetch()):
                    ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($row['status']); ?>">
                                <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="?action=view&id=<?php echo $row['id']; ?>" class="btn-icon btn-edit" title="View">👁️</a>
                            </div>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="7" class="text-center">No orders found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
