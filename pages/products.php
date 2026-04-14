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
if ($_SERVER['REQUEST_METHOD'] ?? '' === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image_path = null;

    if ($action === 'add') {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_path = $product->uploadImage($_FILES['image']);
        }

        if ($product->create($name, $price, $stock, $image_path)) {
            $message = ['type' => 'success', 'text' => '✅ Menu item added successfully!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to add item. Please try again.'];
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $existing_product = $product->getById($id);

        // Handle image upload or keep existing
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $new_image_path = $product->uploadImage($_FILES['image']);
            if ($new_image_path) {
                // Delete old image if exists
                if (!empty($existing_product['image_path'])) {
                    $product->deleteImage($existing_product['image_path']);
                }
                $image_path = $new_image_path;
            } else {
                $image_path = $existing_product['image_path'];
            }
        } else {
            $image_path = $existing_product['image_path'] ?? null;
        }

        if ($product->update($id, $name, $price, $stock, $image_path)) {
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
    $prod = $product->getById($id);
    
    // Delete image if exists
    if ($prod && !empty($prod['image_path'])) {
        $product->deleteImage($prod['image_path']);
    }
    
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

<div class="content-area">
<div class="container">
<main class="main-content">

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
              <th>Image</th>
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
                  <div style="width: 50px; height: 50px; border-radius: 5px; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($p['image_path']) && file_exists('./' . $p['image_path'])): ?>
                      <img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                      <span style="font-size: 28px; color: #ccc;">📸</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <span class="fw-600"><?php echo htmlspecialchars($p['name']); ?></span>
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
    <form method="POST" action="?action=<?php echo $action; ?><?php echo ($action === 'edit' && $edit_data) ? '&id=' . intval($edit_data['id']) : ''; ?>" enctype="multipart/form-data">
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
        <div class="form-group full">
          <label for="image">Product Image</label>
          <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div style="flex: 1;">
              <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
              <span class="form-hint">Accepted: JPG, PNG, GIF, WebP (Max 5MB)</span>
            </div>
            <div id="imagePreview" style="width: 100px; height: 100px; border-radius: 5px; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
              <?php if ($action === 'edit' && !empty($edit_data['image_path']) && file_exists('./' . $edit_data['image_path'])): ?>
                <img id="previewImg" src="<?php echo htmlspecialchars($edit_data['image_path']); ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <span id="noImageText" style="font-size: 32px; color: #ccc;">📸</span>
              <?php endif; ?>
            </div>
          </div>
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

function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('imagePreview');
  const previewImg = document.getElementById('previewImg');
  const noImageText = document.getElementById('noImageText');
  
  if (file) {
    // Validate file size (5MB)
    if (file.size > 5242880) {
      alert('File size must not exceed 5MB');
      event.target.value = '';
      return;
    }
    
    // Validate file type
    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) {
      alert('Only JPG, PNG, GIF, and WebP images are allowed');
      event.target.value = '';
      return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
      if (previewImg) {
        previewImg.src = e.target.result;
        previewImg.style.display = 'block';
      } else {
        // Create img element if it doesn't exist
        const img = document.createElement('img');
        img.id = 'previewImg';
        img.src = e.target.result;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        preview.innerHTML = '';
        preview.appendChild(img);
      }
      
      if (noImageText) {
        noImageText.style.display = 'none';
      }
    };
    reader.readAsDataURL(file);
  }
}
</script>

</main>
</div>

<?php include(dirname(__FILE__) . '/../includes/footer.php'); ?>