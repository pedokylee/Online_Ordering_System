<?php
/**
 * Categories page
 */
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../classes/Category.php';

$base_path  = '../';
$page_title = 'Categories';
$category   = new Category($conn);
$action     = $_GET['action'] ?? 'list';
$message    = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name        = trim($_POST['name']  ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($action === 'add') {
        if ($category->create($name, $description)) {
            $message = ['type' => 'success', 'text' => '✅ Category added successfully!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to add category. Name may already exist.'];
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($category->update($id, $name, $description)) {
            $message = ['type' => 'success', 'text' => '✅ Category updated successfully!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to update category.'];
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $message = $category->delete($id)
        ? ['type' => 'success', 'text' => '✅ Category deleted successfully!']
        : ['type' => 'danger',  'text' => '❌ Could not delete category.'];
    $action = 'list';
}

$edit_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_data = $category->getById(intval($_GET['id']));
    if (empty($edit_data)) { $action = 'list'; }
}

$categories = [];
if ($action === 'list') {
    $stmt = $category->read();
    if ($stmt) {
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$icon_types = ['food', 'menu', 'orders', 'users', 'checkout'];
?>
<?php include(dirname(__FILE__) . '/../includes/header.php'); ?>
<?php include(dirname(__FILE__) . '/../includes/navbar.php'); ?>

<div class="content-area">
<main class="main-content">

<?php if ($message): ?>
  <div class="alert alert-<?php echo $message['type']; ?>">
    <?php echo htmlspecialchars($message['text']); ?>
  </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>

  <div class="page-header">
    <div class="page-header-text">
      <h1>Categories</h1>
      <p>Manage food categories for your menu.</p>
    </div>
    <div class="page-header-actions">
      <a href="?action=add" class="btn btn-primary"><?php echo svg_icon('food', '16'); ?> Add Category</a>
    </div>
  </div>

  <div class="table-container">
    <div class="table-toolbar">
      <h3>All Categories (<?php echo count($categories); ?>)</h3>
      <div class="search-box">
        <span><?php echo svg_icon('menu', '16'); ?></span>
        <input type="text" id="searchInput" placeholder="Search categories…" oninput="filterTable()">
      </div>
    </div>

    <?php if (empty($categories)): ?>
      <div class="empty-state">
        <div class="empty-icon"><?php echo svg_icon('food', '32'); ?></div>
        <h3>No categories yet</h3>
        <p>Create your first category to organize your menu items.</p>
        <a href="?action=add" class="btn btn-primary">+ Add Category</a>
      </div>
    <?php else: ?>
      <div class="table-overflow">
        <table id="categoriesTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Category Name</th>
              <th>Description</th>
              <th>Date Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($categories as $i => $c): ?>
              <tr>
                <td class="text-muted text-sm"><?php echo $i + 1; ?></td>
                <td>
                  <div class="d-flex align-center gap-1">
                    <span style="font-size:20px"><?php echo svg_icon($icon_types[$i % count($icon_types)], '20'); ?></span>
                    <span class="fw-600"><?php echo htmlspecialchars($c['name']); ?></span>
                  </div>
                </td>
                <td class="text-sm"><?php echo htmlspecialchars(substr($c['description'] ?? '', 0, 60)) . (strlen($c['description'] ?? '') > 60 ? '...' : ''); ?></td>
                <td class="text-sm text-muted">
                  <?php echo !empty($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '—'; ?>
                </td>
                <td>
                  <div class="td-actions">
                    <a href="?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edit</a>
                    <a href="?action=delete&id=<?php echo $c['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this category?')">🗑️</a>
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
      <h1><?php echo $action === 'add' ? 'Add Category' : 'Edit Category'; ?></h1>
      <p><?php echo $action === 'add' ? 'Create a new food category for your menu.' : 'Update this category details.'; ?></p>
    </div>
    <div class="page-header-actions">
      <a href="categories.php" class="btn btn-secondary">← Back to Categories</a>
    </div>
  </div>

  <div class="form-card">
    <form method="POST" action="?action=<?php echo $action; ?><?php echo ($action === 'edit' && $edit_data) ? '&id=' . intval($edit_data['id']) : ''; ?>">
      <?php if ($action === 'edit' && $edit_data): ?>
        <input type="hidden" name="id" value="<?php echo intval($edit_data['id']); ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group full">
          <label for="name">Category Name *</label>
          <input type="text" id="name" name="name" required placeholder="e.g. Pizzas, Burgers, Salads"
                 value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>">
        </div>
        <div class="form-group full">
          <label for="description">Description</label>
          <textarea id="description" name="description" placeholder="Enter a brief description of this category..."
                    ><?php echo htmlspecialchars($edit_data['description'] ?? ''); ?></textarea>
          <span class="form-hint">Optional: Provide a description to help customers understand this category.</span>
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">
          <?php echo $action === 'add' ? '✅ Add Category' : '💾 Save Changes'; ?>
        </button>
        <a href="categories.php" class="btn btn-secondary btn-lg">Cancel</a>
      </div>
    </form>
  </div>

<?php endif; ?>

</main>
</div>

<script>
function filterTable() {
  var q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#categoriesTable tbody tr').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php include(dirname(__FILE__) . '/../includes/footer.php'); ?>
