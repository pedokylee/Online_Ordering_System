<?php
/**
 * Database Creation Script
 * Creates the 'online_ordering' database if it doesn't exist
 * Run this script first, then run db_setup.php
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);
define('DB_NAME', 'online_ordering');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Creator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-top: 0;
            text-align: center;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            font-weight: 500;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .steps {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        .steps li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .steps li:last-child {
            border-bottom: none;
        }
        .checkmark {
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        .next-steps {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }
        .next-steps h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Creator</h1>

        <?php
        try {
            // Connect to MySQL without specifying database
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo "<div class='status success'><span class='checkmark'>✓</span> Connected to MySQL server successfully</div>";

            // Check if database exists
            $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?");
            $stmt->execute([DB_NAME]);
            
            if ($stmt->rowCount() > 0) {
                echo "<div class='status info'>
                    <span class='checkmark'>ℹ</span> Database '" . DB_NAME . "' already exists
                </div>";
                $dbExists = true;
            } else {
                // Create database
                $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                        CHARACTER SET utf8mb4 
                        COLLATE utf8mb4_unicode_ci";
                
                $pdo->exec($sql);
                
                echo "<div class='status success'>
                    <span class='checkmark'>✓</span> Database '" . DB_NAME . "' created successfully
                </div>";
                $dbExists = false;
            }

            // Now connect to the database
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo "<div class='status success'><span class='checkmark'>✓</span> Connected to database '" . DB_NAME . "'</div>";

            // Display all steps completed
            echo "<div style='margin-top: 30px;'>";
            echo "<h2 style='text-align: center; color: #333;'>✨ Setup Complete!</h2>";
            
            echo "<ul class='steps'>";
            echo "<li><span class='checkmark'>✓</span> MySQL server connection established</li>";
            echo "<li><span class='checkmark'>✓</span> Database '" . DB_NAME . "' is ready</li>";
            echo "<li><span class='checkmark'>✓</span> Character set: UTF8MB4 (supports all characters)</li>";
            echo "<li><span class='checkmark'>✓</span> Collation: utf8mb4_unicode_ci</li>";
            echo "</ul>";
            
            echo "</div>";

            echo "<div class='next-steps'>
                <h3>📋 Next Steps:</h3>
                <ol style='margin: 10px 0;'>
                    <li><strong>Run</strong> <code style='background: white; padding: 2px 5px;'>db_setup.php</code> to create tables and insert sample data</li>
                    <li><strong>Access</strong> the application at <code style='background: white; padding: 2px 5px;'>http://localhost/final/</code></li>
                    <li><strong>Delete</strong> this file and <code style='background: white; padding: 2px 5px;'>db_setup.php</code> after setup</li>
                </ol>
            </div>";

        } catch (PDOException $e) {
            echo "<div class='status error'><span class='checkmark'>✗</span> Connection Error</div>";
            echo "<div class='status error'>";
            echo "Error Details: " . $e->getMessage();
            echo "</div>";

            echo "<div class='next-steps' style='background: #ffe7e7; border-left-color: #f44336;'>";
            echo "<h3 style='color: #d32f2f;'>⚠️ Troubleshooting:</h3>";
            echo "<ul style='margin: 10px 0;'>";
            echo "<li><strong>Is MySQL running?</strong> Check XAMPP control panel - MySQL should be started (green)</li>";
            echo "<li><strong>Is Apache running?</strong> Apache should also be started (green)</li>";
            echo "<li><strong>Wrong credentials?</strong> Edit the database credentials at the top of this file</li>";
            echo "<li><strong>Port issue?</strong> Make sure MySQL is on port " . DB_PORT . "</li>";
            echo "</ul>";
            echo "</div>";
        }
        ?>

        <div class="code">
            <strong>Database Info:</strong><br>
            Host: <?php echo DB_HOST; ?><br>
            Port: <?php echo DB_PORT; ?><br>
            Database: <?php echo DB_NAME; ?><br>
            User: <?php echo DB_USER; ?>
        </div>

        <div class="footer">
            <p>Online Ordering System - Database Setup Tool</p>
            <p>This file can be deleted after successful setup</p>
        </div>
    </div>
</body>
</html>
