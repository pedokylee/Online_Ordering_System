<?php
/**
 * Customers page - CRUD operations for customers
 */

require_once dirname(__FILE__, 2) . '/config/config.php';
require_once dirname(__FILE__, 2) . '/classes/Customer.php';

$page_title = 'Customers';
$customer = new Customer($conn);
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : '';
$message = '';
$error = '';

// Handle POST requests for CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action']);

    if ($action === 'create') {
        // CREATE - Add new customer
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');

        if (empty($name) || empty($email) || empty($phone)) {
            $error = "All fields are required!";
        } elseif (!validate_email($email)) {
            $error = "Invalid email format!";
        } elseif ($customer->emailExists($email)) {
            $error = "Email already exists!";
        } else {
            if ($customer->create($name, $email, $phone)) {
                $message = "✓ Customer added successfully!";
                $_POST = [];
            } else {
                $error = "Error adding customer!";
            }
        }
    } elseif ($action === 'update') {
        // UPDATE - Modify customer
        $id = $_POST['id'] ?? '';
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $phone = sanitize_input($_POST['phone'] ?? '');

        if (empty($id) || empty($name) || empty($email) || empty($phone)) {
            $error = "All fields are required!";
        } elseif (!validate_email($email)) {
            $error = "Invalid email format!";
        } else {
            if ($customer->update($id, $name, $email, $phone)) {
                $message = "✓ Customer updated successfully!";
                $action = '';
                $_POST = [];
            } else {
                $error = "Error updating customer!";
            }
        }
    } elseif ($action === 'delete') {
        // DELETE - Remove customer
        $id = sanitize_input($_POST['id'] ?? '');
        if (!empty($id) && $customer->delete($id)) {
            $message = "✓ Customer deleted successfully!";
        } else {
            $error = "Error deleting customer!";
        }
    }
}

// Get customer ID for editing
$edit_customer = [];
if ($action === 'edit' && isset($_GET['id'])) {
    $id = sanitize_input($_GET['id']);
    $edit_customer = $customer->getById($id);
}

?>
<?php include(dirname(__FILE__, 2) . '/includes/header.php'); ?>
<?php include(dirname(__FILE__, 2) . '/includes/navbar.php'); ?>

<main class="main-content">
    <div class="page-header">
        <h1>Customer Management</h1>
        <a href="?action=add" class="btn btn-primary">+ Add New Customer</a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Form for adding/editing customers -->
        <div class="form-container">
            <h2><?php echo $action === 'add' ? 'Add New Customer' : 'Edit Customer'; ?></h2>
            <form method="POST" action="" class="form">
                <input type="hidden" name="action" value="<?php echo $action === 'edit' ? 'update' : 'create'; ?>">
                
                <?php if ($action === 'edit' && !empty($edit_customer)): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_customer['id']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($edit_customer['name'] ?? $_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($edit_customer['email'] ?? $_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required
                           value="<?php echo htmlspecialchars($edit_customer['phone'] ?? $_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $action === 'add' ? 'Add Customer' : 'Update Customer'; ?>
                    </button>
                    <a href="customers.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Customers table -->
    <div class="table-container">
        <h2>All Customers</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $customer->read();
                if ($result && $result->rowCount() > 0):
                    while($row = $result->fetch()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="?action=edit&id=<?php echo $row['id']; ?>" class="btn-icon btn-edit" title="Edit"><?php echo svg_icon('edit', '16'); ?></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this customer?');">
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
                    <td colspan="6" class="text-center">No customers found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include(dirname(__FILE__, 2) . '/includes/footer.php'); ?>
