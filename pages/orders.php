<?php
/**
 * Orders page
 */
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../classes/Order.php';

$base_path  = '../';
$page_title = 'Orders';
$order_obj  = new Order($conn);
$message    = '';
$action     = $_GET['action'] ?? 'list';

// Status update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $id     = intval($_POST['order_id']);
    $status = $_POST['status'];
    $message = $order_obj->updateStatus($id, $status)
        ? ['type' => 'success', 'text' => '✅ Order status updated!']
        : ['type' => 'danger',  'text' => '❌ Failed to update status.'];
}

// Delete
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $message = $order_obj->delete($id)
        ? ['type' => 'success', 'text' => '✅ Order deleted.']
        : ['type' => 'danger',  'text' => '❌ Could not delete order.'];
    $action = 'list';
}

// View single order
$view_order = null;
$order_items = [];
if ($action === 'view' && isset($_GET['id'])) {
    $view_order  = $order_obj->getById(intval($_GET['id']));
    $items_stmt  = $order_obj->getOrderItems(intval($_GET['id']));
    $order_items = $items_stmt ? $items_stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    if (empty($view_order)) { $action = 'list'; }
}

// Fetch all orders via read() → PDOStatement → fetchAll
$orders = [];
if ($action === 'list') {
    $stmt = $order_obj->read();
    if ($stmt) {
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$statuses = ['pending','processing','ready','completed','cancelled'];

function order_badge($status) {
    $map = [
        'pending'    => 'badge-warning',
        'processing' => 'badge-neutral',
        'ready'      => 'badge-success',
        'completed'  => 'badge-success',
        'cancelled'  => 'badge-danger',
    ];
    return $map[strtolower($status)] ?? 'badge-neutral';
}
?>
<?php include(dirname(__FILE__) . '/../includes/header.php'); ?>
<?php include(dirname(__FILE__) . '/../includes/navbar.php'); ?>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $message['type']; ?>"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>

  <div class="page-header">
    <div class="page-header-text">
      <h1>Orders</h1>
      <p>Track and manage all customer orders.</p>
    </div>
    <div class="page-header-actions">
      <a href="../pages/cart.php" class="btn btn-primary"><?php echo svg_icon('checkout', '16'); ?> New Order</a>
    </div>
  </div>

  <div class="table-container">
    <div class="table-toolbar">
      <h3>All Orders (<?php echo count($orders); ?>)</h3>
      <div class="search-box">
        <span><?php echo svg_icon('menu', '16'); ?></span>
        <input type="text" id="searchInput" placeholder="Search orders…" oninput="filterTable()">
      </div>
    </div>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <div class="empty-icon"><?php echo svg_icon('orders', '32'); ?></div>
        <h3>No orders yet</h3>
        <p>Orders placed through the cart will appear here.</p>
        <a href="../pages/cart.php" class="btn btn-primary"><?php echo svg_icon('checkout', '16'); ?> Start an Order</a>
      </div>
    <?php else: ?>
      <div class="table-overflow">
        <table id="ordersTable">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td class="fw-600">#<?php echo str_pad($o['id'], 4, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo htmlspecialchars($o['name'] ?? 'Unknown'); ?></td>
                <td class="fw-600 text-gold">$<?php echo number_format($o['total_amount'] ?? 0, 2); ?></td>
                <td>
                  <form method="POST" style="display:inline-flex;align-items:center;gap:6px;">
                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                    <select name="status" onchange="this.form.submit()"
                            style="font-size:11px;padding:3px 6px;border:1px solid var(--c-border-strong);border-radius:6px;background:var(--c-surface);color:var(--c-ink);cursor:pointer;font-family:var(--ff-body);">
                      <?php foreach ($statuses as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo strtolower($o['status'] ?? '') === $s ? 'selected' : ''; ?>>
                          <?php echo ucfirst($s); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td class="text-sm text-muted">
                  <?php echo !empty($o['created_at']) ? date('M d, Y', strtotime($o['created_at'])) : '—'; ?>
                </td>
                <td>
                  <div class="td-actions">
                    <a href="?action=view&id=<?php echo $o['id']; ?>" class="btn btn-sm btn-secondary">👁 View</a>
                    <a href="?action=delete&id=<?php echo $o['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete order #<?php echo str_pad($o['id'],4,'0',STR_PAD_LEFT); ?>?')">🗑️</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

<?php elseif ($action === 'view' && $view_order): ?>

  <div class="page-header">
    <div class="page-header-text">
      <h1>Order #<?php echo str_pad($view_order['id'], 4, '0', STR_PAD_LEFT); ?></h1>
      <p>Placed on <?php echo !empty($view_order['created_at']) ? date('F d, Y', strtotime($view_order['created_at'])) : '—'; ?></p>
    </div>
    <div class="page-header-actions">
      <a href="orders.php" class="btn btn-secondary">← Back to Orders</a>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
    <div class="table-container">
      <div class="table-toolbar"><h3>Items Ordered</h3></div>
      <?php if (empty($order_items)): ?>
        <div class="empty-state" style="padding:2rem;">
          <p>No items found for this order.</p>
        </div>
      <?php else: ?>
        <div class="table-overflow">
          <table>
            <thead>
              <tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
              <?php foreach ($order_items as $item): ?>
                <tr>
                  <td class="fw-600"><?php echo htmlspecialchars($item['name']); ?></td>
                  <td><?php echo intval($item['quantity']); ?></td>
                  <td>$<?php echo number_format($item['price'], 2); ?></td>
                  <td class="fw-600 text-gold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="order-summary">
      <div class="order-summary-header"><h3>Order Info</h3></div>
      <div class="order-summary-body">
        <div class="summary-row"><span>Customer</span><span><?php echo htmlspecialchars($view_order['name']); ?></span></div>
        <div class="summary-row"><span>Email</span><span><?php echo htmlspecialchars($view_order['email'] ?? '—'); ?></span></div>
        <div class="summary-row"><span>Phone</span><span><?php echo htmlspecialchars($view_order['phone'] ?? '—'); ?></span></div>
        <div class="summary-row"><span>Status</span>
          <span class="badge <?php echo order_badge($view_order['status'] ?? ''); ?>">
            <?php echo ucfirst($view_order['status'] ?? '—'); ?>
          </span>
        </div>
        <div class="summary-row total"><span>Total</span><span>$<?php echo number_format($view_order['total_amount'] ?? 0, 2); ?></span></div>
      </div>
    </div>
  </div>

  <style>
  @media(max-width:768px){
    div[style*="grid-template-columns:1fr 300px"]{display:block!important;}
    div[style*="grid-template-columns:1fr 300px"] > *{margin-bottom:1rem;}
  }
  </style>

<?php endif; ?>

<script>
function filterTable() {
  var q = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase() : '';
  document.querySelectorAll('#ordersTable tbody tr').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php include(dirname(__FILE__) . '/../includes/footer.php'); ?>