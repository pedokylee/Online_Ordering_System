<?php
/**
 * Products / Menu Items page
 */
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../classes/Product.php';

$base_path  = '../';
$page_title = 'Menu Items';
$product    = new Product($conn);
$action     = $_GET['action'] ?? 'list';
$message    = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($action === 'add') {
        if ($product->create($name, $price, $stock)) {
            $message = ['type' => 'success', 'text' => '✅ Menu item added successfully!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to add item. Please try again.'];
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($product->update($id, $name, $price, $stock)) {
            $message = ['type' => 'success', 'text' => '✅ Item updated successfully!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to update item.'];
        }
    }
}

// Handle delete
if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $message = $product->delete($id)
        ? ['type' => 'success', 'text' => '✅ Item deleted successfully!']
        : ['type' => 'danger',  'text' => '❌ Could not delete item.'];
    $action = 'list';
}

// Edit — fetch existing data
$edit_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_data = $product->getById(intval($_GET['id']));
    if (empty($edit_data)) { $action = 'list'; }
}

// Fetch all products via read() which returns a PDOStatement
$products = [];
if ($action === 'list') {
    $stmt = $product->read();
    if ($stmt) {
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$icon_types = ['food', 'menu', 'orders', 'users', 'checkout'];
?>
<?php include(dirname(__FILE__) . '/../includes/header.php'); ?>
<?php include(dirname(__FILE__) . '/../includes/navbar.php'); ?>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $message['type']; ?>">
    <?php echo htmlspecialchars($message['text']); ?>
  </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>

  <div class="page-header">
    <div class="page-header-text">
      <h1>Menu Items</h1>
      <p>Manage your food catalog — add, edit, or remove dishes.</p>
    </div>
    <div class="page-header-actions">
      <a href="?action=add" class="btn btn-primary"><?php echo svg_icon('food', '16'); ?> Add New Item</a>
    </div>
  </div>

  <div class="table-container">
    <div class="table-toolbar">
      <h3>All Items (<?php echo count($products); ?>)</h3>
      <div class="search-box">
        <span><?php echo svg_icon('menu', '16'); ?></span>
        <input type="text" id="searchInput" placeholder="Search menu items…" oninput="filterTable()">
      </div>
    </div>

    <?php if (empty($products)): ?>
      <div class="empty-state">
        <div class="empty-icon"><?php echo svg_icon('food', '32'); ?></div>
        <h3>No menu items yet</h3>
        <p>Start building your menu by adding your first dish.</p>
        <a href="?action=add" class="btn btn-primary">+ Add First Item</a>
      </div>
    <?php else: ?>
      <div class="table-overflow">
        <table id="productsTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Item</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Date Added</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $i => $p): ?>
              <tr>
                <td class="text-muted text-sm"><?php echo $i + 1; ?></td>
                <td>
                  <div class="d-flex align-center gap-1">
                    <span style="font-size:20px"><?php echo svg_icon($icon_types[$i % count($icon_types)], '20'); ?></span>
                    <span class="fw-600"><?php echo htmlspecialchars($p['name']); ?></span>
                  </div>
                </td>
                <td class="fw-600 text-gold">$<?php echo number_format($p['price'], 2); ?></td>
                <td>
                  <?php $stock = intval($p['stock']); ?>
                  <span class="badge <?php echo $stock > 0 ? 'badge-success' : 'badge-danger'; ?>">
                    <?php echo $stock > 0 ? "In Stock ($stock)" : 'Out of Stock'; ?>
                  </span>
                </td>
                <td class="text-sm text-muted">
                  <?php echo !empty($p['created_at']) ? date('M d, Y', strtotime($p['created_at'])) : '—'; ?>
                </td>
                <td>
                  <div class="td-actions">
                    <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edit</a>
                    <a href="?action=delete&id=<?php echo $p['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this item?')">🗑️</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

  <div class="page-header">
    <div class="page-header-text">
      <h1><?php echo $action === 'add' ? 'Add Menu Item' : 'Edit Menu Item'; ?></h1>
      <p><?php echo $action === 'add' ? 'Fill in the details for your new dish.' : 'Update the details for this dish.'; ?></p>
    </div>
    <div class="page-header-actions">
      <a href="products.php" class="btn btn-secondary">← Back to Menu</a>
    </div>
  </div>

  <div class="form-card">
    <form method="POST" action="?action=<?php echo $action; ?><?php echo ($action === 'edit' && $edit_data) ? '&id=' . intval($edit_data['id']) : ''; ?>">
      <?php if ($action === 'edit' && $edit_data): ?>
        <input type="hidden" name="id" value="<?php echo intval($edit_data['id']); ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group full">
          <label for="name">Item Name *</label>
          <input type="text" id="name" name="name" required placeholder="e.g. Cheeseburger Deluxe"
                 value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="price">Price ($) *</label>
          <input type="number" id="price" name="price" step="0.01" min="0" required
                 placeholder="0.00"
                 value="<?php echo isset($edit_data['price']) ? number_format($edit_data['price'], 2, '.', '') : ''; ?>">
        </div>
        <div class="form-group">
          <label for="stock">Stock / Qty Available</label>
          <input type="number" id="stock" name="stock" min="0"
                 placeholder="0"
                 value="<?php echo intval($edit_data['stock'] ?? 0); ?>">
          <span class="form-hint">Set to 0 if out of stock.</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">
          <?php echo $action === 'add' ? '✅ Add Item' : '💾 Save Changes'; ?>
        </button>
        <a href="products.php" class="btn btn-secondary btn-lg">Cancel</a>
      </div>
    </form>
  </div>

<?php endif; ?>

<script>
function filterTable() {
  var q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#productsTable tbody tr').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php include(dirname(__FILE__) . '/../includes/footer.php'); ?>