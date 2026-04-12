<?php
/**
 * Cart / Checkout page
 */
session_start();
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../classes/Product.php';
require_once dirname(__FILE__) . '/../classes/Order.php';
require_once dirname(__FILE__) . '/../classes/Customer.php';

$base_path  = '../';
$page_title = 'Cart';
$message    = '';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add item to cart
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_product_id'])) {
    $pid = intval($_POST['add_product_id']);
    $qty = max(1, intval($_POST['qty'] ?? 1));
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid]['qty'] += $qty;
    } else {
        $product_obj = new Product($conn);
        $p = $product_obj->getById($pid);
        if (!empty($p)) {
            $_SESSION['cart'][$pid] = [
                'id'    => $p['id'],
                'name'  => $p['name'],
                'price' => $p['price'],
                'qty'   => $qty,
            ];
        }
    }
}

// Remove item
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][intval($_GET['remove'])]);
}

// Update quantities
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_qty'], $_POST['qty_item'])) {
    foreach ($_POST['qty_item'] as $pid => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            if (isset($_SESSION['cart'][$pid])) {
                $_SESSION['cart'][$pid]['qty'] = $qty;
            }
        }
    }
    $message = ['type' => 'success', 'text' => '✅ Cart updated!'];
}

// Checkout
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['checkout'])) {
    $customer_id = intval($_POST['customer_id'] ?? 0);
    if (!empty($_SESSION['cart']) && $customer_id > 0) {
        $cart        = $_SESSION['cart'];
        $subtotal    = array_sum(array_map(function($i){ return $i['price'] * $i['qty']; }, $cart));
        $total       = round($subtotal * 1.10, 2); // includes 10% tax

        $order_obj   = new Order($conn);
        $order_id    = $order_obj->create($customer_id, $total, 'pending');

        if ($order_id) {
            // Insert each cart item into order_items
            foreach ($cart as $item) {
                $order_obj->addItemToOrder($order_id, $item['id'], $item['qty'], $item['price']);
            }
            $_SESSION['cart'] = [];
            $message = ['type' => 'success', 'text' => '🎉 Order #' . str_pad($order_id, 4, '0', STR_PAD_LEFT) . ' placed successfully!'];
        } else {
            $message = ['type' => 'danger', 'text' => '❌ Failed to place order. Please try again.'];
        }
    } elseif ($customer_id <= 0) {
        $message = ['type' => 'warning', 'text' => '⚠️ Please select a customer before checking out.'];
    } else {
        $message = ['type' => 'warning', 'text' => '⚠️ Your cart is empty.'];
    }
}

// Fetch all products via read() → PDOStatement
$product_obj = new Product($conn);
$prod_stmt   = $product_obj->read();
$products    = $prod_stmt ? $prod_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Fetch all customers via read() → PDOStatement
$customer_obj = new Customer($conn);
$cust_stmt    = $customer_obj->read();
$customers    = $cust_stmt ? $cust_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

$cart     = $_SESSION['cart'];
$subtotal = array_sum(array_map(function($i){ return $i['price'] * $i['qty']; }, $cart));
$tax      = round($subtotal * 0.10, 2);
$total    = round($subtotal + $tax, 2);

$icon_types = ['food', 'menu', 'orders', 'users', 'checkout'];
?>
<?php include(dirname(__FILE__) . '/../includes/header.php'); ?>
<?php include(dirname(__FILE__) . '/../includes/navbar.php'); ?>

<?php if ($message): ?>
  <div class="alert alert-<?php echo $message['type']; ?>"><?php echo htmlspecialchars($message['text']); ?></div>
<?php endif; ?>

<div class="page-header">
  <div class="page-header-text">
    <h1>Cart &amp; Checkout</h1>
    <p>Review your order and place it when ready.</p>
  </div>
</div>

<div class="cart-layout-grid">

  <!-- Left: Cart + Add from Menu -->
  <div>

    <!-- Current Cart -->
    <div class="table-container mb-3">
      <div class="table-toolbar">
        <h3><?php echo svg_icon('checkout', '18'); ?> Cart (<?php echo count($cart); ?> item<?php echo count($cart) !== 1 ? 's' : ''; ?>)</h3>
        <?php if (!empty($cart)): ?>
          <a href="?clear=1" class="btn btn-sm btn-danger"
             onclick="return confirm('Clear cart?')"
             <?php if(isset($_GET['clear'])): ?><?php $_SESSION['cart']=[]; ?><?php endif; ?>>
            Clear Cart
          </a>
        <?php endif; ?>
      </div>

      <?php if (empty($cart)): ?>
        <div class="empty-state">
          <div class="empty-icon"><?php echo svg_icon('checkout', '32'); ?></div>
          <h3>Your cart is empty</h3>
          <p>Browse the menu below and add items to get started.</p>
        </div>
      <?php else: ?>
        <form method="POST">
          <?php $idx = 0; foreach ($cart as $pid => $item): ?>
            <div class="cart-item">
              <div class="cart-item-img"><?php echo svg_icon($icon_types[$idx % count($icon_types)], '24'); ?></div>
              <div class="cart-item-info">
                <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> each</div>
              </div>
              <div class="cart-item-controls">
                <input type="number" name="qty_item[<?php echo intval($pid); ?>]"
                       value="<?php echo intval($item['qty']); ?>" min="0" max="99"
                       style="width:65px;padding:0.35rem 0.5rem;text-align:center;border:1px solid var(--c-border-strong);border-radius:var(--radius-md);font-family:var(--ff-body);font-size:0.875rem;">
              </div>
              <div class="cart-item-total">$<?php echo number_format($item['price'] * $item['qty'], 2); ?></div>
              <a href="?remove=<?php echo intval($pid); ?>" class="btn btn-sm btn-danger" title="Remove">✕</a>
            </div>
          <?php $idx++; endforeach; ?>
          <div style="padding:1rem 1.25rem;border-top:1px solid var(--c-border);">
            <button type="submit" name="update_qty" class="btn btn-secondary">🔄 Update Quantities</button>
          </div>
        </form>
      <?php endif; ?>
    </div>

    <!-- Browse Menu -->
    <div class="table-container">
      <div class="table-toolbar">
        <h3><?php echo svg_icon('food', '18'); ?> Add from Menu</h3>
        <div class="search-box">
          <span><?php echo svg_icon('menu', '16'); ?></span>
          <input type="text" id="menuSearch" placeholder="Search menu…" oninput="filterMenu()">
        </div>
      </div>
      <?php if (empty($products)): ?>
        <div class="empty-state" style="padding:2rem;">
          <div class="empty-icon"><?php echo svg_icon('food', '32'); ?></div>
          <h3>No menu items found</h3>
          <p><a href="products.php?action=add">Add menu items</a> first.</p>
        </div>
      <?php else: ?>
        <div class="product-grid" id="menuGrid" style="padding:1.25rem;">
          <?php foreach ($products as $i => $p): ?>
            <div class="product-card menu-item"
                 data-name="<?php echo strtolower(htmlspecialchars($p['name'])); ?>">
              <div class="product-card-img"><?php echo svg_icon($icon_types[$i % count($icon_types)], '24'); ?></div>
              <div class="product-card-body">
                <div class="product-card-name"><?php echo htmlspecialchars($p['name']); ?></div>
                <div class="product-card-desc">
                  <?php
                    $stock = intval($p['stock']);
                    if ($stock > 0) {
                        echo '<span class="badge badge-success">In Stock (' . $stock . ')</span>';
                    } else {
                        echo '<span class="badge badge-danger">Out of Stock</span>';
                    }
                  ?>
                </div>
                <div class="product-card-footer">
                  <span class="product-price">$<?php echo number_format($p['price'], 2); ?></span>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="add_product_id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-sm btn-primary"
                            <?php echo $stock <= 0 ? 'disabled style="opacity:0.45;cursor:not-allowed;"' : ''; ?>>
                      + Add
                    </button>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div><!-- /left col -->

  <!-- Right: Summary + Checkout -->
  <div>

    <div class="order-summary mb-2">
      <div class="order-summary-header"><h3>Order Summary</h3></div>
      <div class="order-summary-body">
        <?php foreach ($cart as $item): ?>
          <div class="summary-row">
            <span><?php echo htmlspecialchars($item['name']); ?> ×<?php echo $item['qty']; ?></span>
            <span>$<?php echo number_format($item['price'] * $item['qty'], 2); ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (empty($cart)): ?>
          <div class="summary-row"><span class="text-muted">No items</span><span>—</span></div>
        <?php endif; ?>
        <div class="divider" style="margin:0.75rem 0;"></div>
        <div class="summary-row"><span>Subtotal</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
        <div class="summary-row"><span>Tax (10%)</span><span>$<?php echo number_format($tax, 2); ?></span></div>
        <div class="summary-row total"><span>Total</span><span>$<?php echo number_format($total, 2); ?></span></div>
      </div>
    </div>

    <?php if (!empty($cart)): ?>
      <div class="form-card" style="max-width:100%;">
        <h3 style="margin-bottom:1rem;font-size:1rem;font-family:var(--ff-display);">Checkout</h3>
        <form method="POST">
          <div class="form-group mb-2">
            <label for="customer_id">Select Customer *</label>
            <select id="customer_id" name="customer_id" required>
              <option value="">— Choose customer —</option>
              <?php foreach ($customers as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (empty($customers)): ?>
            <p class="text-sm text-muted mb-2">
              No customers yet. <a href="customers.php?action=add">Add a customer</a> first.
            </p>
          <?php endif; ?>
          <button type="submit" name="checkout" class="btn btn-primary btn-lg"
                  style="width:100%;justify-content:center;"
                  <?php echo empty($customers) ? 'disabled' : ''; ?>>
            🎉 Place Order — $<?php echo number_format($total, 2); ?>
          </button>
        </form>
      </div>
    <?php endif; ?>

  </div><!-- /right col -->

</div><!-- /cart-layout-grid -->

<style>
.cart-layout-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.5rem;
  align-items: start;
}
@media (max-width: 900px) {
  .cart-layout-grid { grid-template-columns: 1fr; }
}
</style>

<script>
function filterMenu() {
  var q = document.getElementById('menuSearch').value.toLowerCase();
  document.querySelectorAll('.menu-item').forEach(function(card) {
    card.style.display = card.dataset.name.includes(q) ? '' : 'none';
  });
}
</script>

<?php include(dirname(__FILE__) . '/../includes/footer.php'); ?>