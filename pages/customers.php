<?php
/**
 * Customers page
 */
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../classes/Customer.php';

$base_path  = '../';
$page_title = 'Customers';
$customer   = new Customer($conn);
$action     = $_GET['action'] ?? 'list';
$message    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($action === 'add') {
        if ($customer->create($name, $email, $phone)) {
            $message = ['type' => 'success', 'text' => '✅ Customer added!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to add customer. Email may already exist.'];
        }
    } elseif ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        if ($customer->update($id, $name, $email, $phone)) {
            $message = ['type' => 'success', 'text' => '✅ Customer updated!'];
            $action  = 'list';
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to update customer.'];
        }
    }
}

if ($action === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $message = $customer->delete($id)
        ? ['type' => 'success', 'text' => '✅ Customer deleted.']
        : ['type' => 'danger',  'text' => '❌ Could not delete customer.'];
    $action = 'list';
}

$edit_data = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_data = $customer->getById(intval($_GET['id']));
    if (empty($edit_data)) { $action = 'list'; }
}

// Fetch all via read() → PDOStatement → fetchAll
$customers = [];
if ($action === 'list') {
    $stmt = $customer->read();
    if ($stmt) {
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function avatar_initials($name) {
    $parts = explode(' ', trim($name));
    $init  = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) $init .= strtoupper(substr(end($parts), 0, 1));
    return $init ?: '?';
}
$avatar_colors = ['#D4830A','#27814A','#C0392B','#185FA5','#7F77DD'];
?>
<?php include(dirname(__FILE__) . '/../includes/header.php'); ?>
<?php include(dirname(__FILE__) . '/../includes/navbar.php'); ?>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $message['type']; ?>"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>

  <div class="page-header">
    <div class="page-header-text">
      <h1>Customers</h1>
      <p>Manage your customer database.</p>
    </div>
    <div class="page-header-actions">
      <a href="?action=add" class="btn btn-primary">👤 Add Customer</a>
    </div>
  </div>

  <div class="table-container">
    <div class="table-toolbar">
      <h3>All Customers (<?php echo count($customers); ?>)</h3>
      <div class="search-box">
        <span>🔍</span>
        <input type="text" id="searchInput" placeholder="Search customers…" oninput="filterTable()">
      </div>
    </div>

    <?php if (empty($customers)): ?>
      <div class="empty-state">
        <div class="empty-icon">👥</div>
        <h3>No customers yet</h3>
        <p>Add your first customer to get started.</p>
        <a href="?action=add" class="btn btn-primary">+ Add Customer</a>
      </div>
    <?php else: ?>
      <div class="table-overflow">
        <table id="customersTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Customer</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Date Added</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($customers as $i => $c): ?>
              <tr>
                <td class="text-muted text-sm"><?php echo $i + 1; ?></td>
                <td>
                  <div class="d-flex align-center gap-1">
                    <div style="width:34px;height:34px;border-radius:50%;background:<?php echo $avatar_colors[$i % count($avatar_colors)]; ?>;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:600;flex-shrink:0;">
                      <?php echo avatar_initials($c['name']); ?>
                    </div>
                    <span class="fw-600"><?php echo htmlspecialchars($c['name']); ?></span>
                  </div>
                </td>
                <td class="text-sm"><?php echo htmlspecialchars($c['email'] ?? '—'); ?></td>
                <td class="text-sm"><?php echo htmlspecialchars($c['phone'] ?? '—'); ?></td>
                <td class="text-sm text-muted">
                  <?php echo !empty($c['created_at']) ? date('M d, Y', strtotime($c['created_at'])) : '—'; ?>
                </td>
                <td>
                  <div class="td-actions">
                    <a href="?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edit</a>
                    <a href="?action=delete&id=<?php echo $c['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this customer?')">🗑️</a>
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
      <h1><?php echo $action === 'add' ? 'Add Customer' : 'Edit Customer'; ?></h1>
    </div>
    <div class="page-header-actions">
      <a href="customers.php" class="btn btn-secondary">← Back</a>
    </div>
  </div>

  <div class="form-card">
    <form method="POST" action="?action=<?php echo $action; ?><?php echo ($action === 'edit' && $edit_data) ? '&id=' . intval($edit_data['id']) : ''; ?>">
      <?php if ($action === 'edit' && $edit_data): ?>
        <input type="hidden" name="id" value="<?php echo intval($edit_data['id']); ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div class="form-group">
          <label for="name">Full Name *</label>
          <input type="text" id="name" name="name" required placeholder="Juan Dela Cruz"
                 value="<?php echo htmlspecialchars($edit_data['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="juan@example.com"
                 value="<?php echo htmlspecialchars($edit_data['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" placeholder="+63 912 345 6789"
                 value="<?php echo htmlspecialchars($edit_data['phone'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-lg">
          <?php echo $action === 'add' ? '✅ Add Customer' : '💾 Save Changes'; ?>
        </button>
        <a href="customers.php" class="btn btn-secondary btn-lg">Cancel</a>
      </div>
    </form>
  </div>

<?php endif; ?>

<script>
function filterTable() {
  var q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('#customersTable tbody tr').forEach(function(row) {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}
</script>

<?php include(dirname(__FILE__) . '/../includes/footer.php'); ?>