<?php
/**
 * Quick Verification Script
 * Run this to verify all files are in place
 */

$required_files = [
    'index.php' => 'Root dashboard',
    'config/db.php' => 'Database connection',
    'config/config.php' => 'Configuration',
    'classes/Product.php' => 'Product class with CRUD',
    'classes/Customer.php' => 'Customer class with CRUD',
    'classes/Cart.php' => 'Cart class with CRUD',
    'classes/Order.php' => 'Order class with CRUD',
    'pages/products.php' => 'Products management page',
    'pages/customers.php' => 'Customers management page',
    'pages/cart.php' => 'Shopping cart page',
    'pages/checkout.php' => 'Checkout page',
    'pages/orders.php' => 'Orders management page',
    'includes/header.php' => 'HTML header component',
    'includes/navbar.php' => 'Navigation bar component',
    'includes/footer.php' => 'Footer component',
    'css/style.css' => 'Main stylesheet',
    'js/script.js' => 'JavaScript functionality',
    'db_setup.php' => 'Database initialization',
    'README.md' => 'Documentation'
];

$base_path = dirname(__FILE__);
$missing = [];
$found = [];

foreach ($required_files as $file => $description) {
    $full_path = $base_path . DIRECTORY_SEPARATOR . $file;
    if (file_exists($full_path)) {
        $found[] = $file;
    } else {
        $missing[] = "$file - $description";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Verification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .header { background: #333; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .header h1 { font-size: 2rem; }
        .status { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h2 { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        ul { list-style: none; }
        li { padding: 8px 0; border-bottom: 1px solid #eee; }
        li:last-child { border-bottom: none; }
        .checkmark { color: #28a745; margin-right: 10px; }
        .cross { color: #dc3545; margin-right: 10px; }
        .stats { background: white; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .stat-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .next-steps { background: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin-top: 20px; border-radius: 5px; }
        .next-steps h3 { margin-bottom: 10px; }
        .next-steps ol { margin-left: 20px; }
        .next-steps li { border: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FeastFlow - Project Verification</h1>
            <p>File and structure verification report</p>
        </div>

        <div class="status">
            <div class="card">
                <h2>✅ Files Found</h2>
                <ul>
                    <?php foreach ($found as $file): ?>
                    <li><span class="checkmark">✓</span> <?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h2><?php echo count($missing) > 0 ? '❌ Missing Files' : '✅ All Files Present'; ?></h2>
                <?php if (count($missing) > 0): ?>
                <ul>
                    <?php foreach ($missing as $file): ?>
                    <li><span class="cross">✗</span> <?php echo htmlspecialchars($file); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p class="success">All required files are present!</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="stats">
            <h2>� Project Statistics</h2>
            <div class="stat-row">
                <span>Total Files Required:</span>
                <strong><?php echo count($required_files); ?></strong>
            </div>
            <div class="stat-row">
                <span>Files Found:</span>
                <strong class="success"><?php echo count($found); ?></strong>
            </div>
            <div class="stat-row">
                <span>Files Missing:</span>
                <strong class="<?php echo count($missing) > 0 ? 'error' : 'success'; ?>"><?php echo count($missing); ?></strong>
            </div>
            <div class="stat-row">
                <span>Completion:</span>
                <strong><?php echo round((count($found) / count($required_files)) * 100, 1); ?>%</strong>
            </div>
        </div>

        <div class="next-steps">
            <h3>🚀 Next Steps</h3>
            <ol>
                <li><strong>Verify Files</strong> - Ensure all listed files are present</li>
                <li><strong>Create Database</strong> - Run: <code>CREATE DATABASE online_ordering;</code></li>
                <li><strong>Initialize Database</strong> - Visit: <code>http://localhost/final/db_setup.php</code></li>
                <li><strong>Delete Setup File</strong> - Remove or rename <code>db_setup.php</code> after initialization</li>
                <li><strong>Start Application</strong> - Visit: <code>http://localhost/final/</code></li>
            </ol>
        </div>

        <div class="stats" style="background: #f0f8ff;">
            <h2>✨ Project Features Implemented</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                <div>
                    <h4>Database (5 Tables)</h4>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li>Customers</li>
                        <li>Products</li>
                        <li>Cart</li>
                        <li>Orders</li>
                        <li>Order Items</li>
                    </ul>
                </div>
                <div>
                    <h4>CRUD Operations</h4>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li>Create new records</li>
                        <li>Read/View records</li>
                        <li>Update existing records</li>
                        <li>Delete records</li>
                    </ul>
                </div>
                <div>
                    <h4>Security</h4>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li>MySQLi prepared statements</li>
                        <li>SQL injection protection</li>
                        <li>Input validation</li>
                        <li>Data sanitization</li>
                    </ul>
                </div>
                <div>
                    <h4>Features</h4>
                    <ul style="list-style: disc; margin-left: 20px;">
                        <li>Shopping cart</li>
                        <li>Checkout process</li>
                        <li>Order management</li>
                        <li>Stock management</li>
                    </ul>
                </div>
            </div>
        </div>

        <?php if (count($missing) === 0): ?>
        <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px; text-align: center;">
            <h3>✅ Project is Ready!</h3>
            <p>All files are in place. Follow the next steps above to initialize your database and start using the application.</p>
            <a href="/" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">Go to Application</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
