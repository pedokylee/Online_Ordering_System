<?php
/**
 * Menu page - CRUD operations for food menu items
 */

require_once dirname(__FILE__, 2) . '/config/config.php';
require_once dirname(__FILE__, 2) . '/classes/Product.php';

$page_title = 'Menu';
$product = new Product($conn);
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$message = '';
$error = '';

// Handle POST requests for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action']);

    if ($action === 'create') {
        // CREATE - Add new dish
        $name = sanitize_input($_POST['name'] ?? '');
        $price = $_POST['price'] ?? '';
        $stock = $_POST['stock'] ?? '';

        if (empty($name) || empty($price) || empty($stock)) {
            $error = "All fields are required!";
        } elseif (!validate_number($price) || !validate_number($stock)) {
            $error = "Price and availability must be valid numbers!";
        } else {
            if ($product->create($name, $price, $stock)) {
                $message = "✓ Dish added successfully!";
                $_POST = [];
            } else {
                $error = "Error adding dish!";
            }
        }
    } elseif ($action === 'update') {
        // UPDATE - Modify dish
        $id = $_POST['id'] ?? '';
        $name = sanitize_input($_POST['name'] ?? '');
        $price = $_POST['price'] ?? '';
        $stock = $_POST['stock'] ?? '';

        if (empty($id) || empty($name) || empty($price) || empty($stock)) {
            $error = "All fields are required!";
        } elseif (!validate_number($price) || !validate_number($stock)) {
            $error = "Price and availability must be valid numbers!";
        } else {
            if ($product->update($id, $name, $price, $stock)) {
                $message = "✓ Dish updated successfully!";
                $action = '';
                $_POST = [];
            } else {
                $error = "Error updating dish!";
            }
        }
    } elseif ($action === 'delete') {
        // DELETE - Remove dish
        $id = sanitize_input($_POST['id'] ?? '');
        if (!empty($id) && $product->delete($id)) {
            $message = "✓ Dish deleted successfully!";
        } else {
            $error = "Error deleting dish!";
        }
    }
}

// Get product ID for editing
$edit_product = [];
if ($action === 'edit' && isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    $edit_product = $product->getById($id);
}

?>
<?php include(dirname(__FILE__, 2) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__, 2) . '/includes/navbar.php'); ?>

<main class="main-content">
    <div class="page-header">
        <h1>Menu Management</h1>
        <a href="?action=add" class="btn btn-primary">+ Add New Product</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Form for adding/editing products -->
        <div class="form-container">
            <h2><?php echo $action === 'add' ? 'Add New Dish' : 'Edit Dish'; ?></h2>
            <form method="POST" action="" class="form">
                <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                
                <?php if ($action === 'edit' && !empty($edit_product)): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_product['id']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Dish Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($edit_product['name'] ?? $_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           value="<?php echo htmlspecialchars($edit_product['price'] ?? $_POST['price'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="stock">Stock Quantity *</label>
                    <input type="number" id="stock" name="stock" min="0" required
                           value="<?php echo htmlspecialchars($edit_product['stock'] ?? $_POST['stock'] ?? ''); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                    </button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Menu table -->
    <div class="table-container">
        <h2>All Menu Items</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Dish Name</th>
                    <th>Price</th>
                    <th>Available</th>
                    <th>Added Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $product->read();
                if ($result && $result->rowCount() > 0):
                    while($row = $result->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo CURRENCY_SYMBOL . number_format($row['price'], 2); ?></td>
                    <td>
                        <span class="stock-badge <?php echo $row['stock'] > 10 ? 'stock-high' : ($row['stock'] > 0 ? 'stock-med' : 'stock-low'); ?>">
                            <?php echo htmlspecialchars($row['stock']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn-icon btn-edit" title="Edit"><?php echo svg_icon('edit', '16'); ?></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn-icon btn-delete" title="Delete"><?php echo svg_icon('delete', '16'); ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php 
                    endwhile;
                else:
                ?>
                <tr>
                    <td colspan="6" class="text-center">No menu items found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
