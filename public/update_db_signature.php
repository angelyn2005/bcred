<?php
// Add signature_path column to users table
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$port = '3306';
$dbname = 'ecrdntldb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h2>Database Update - Add Signature Support</h2>";
    echo "<pre>";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'signature_path'");
    $columnExists = $stmt->rowCount() > 0;
    
    if ($columnExists) {
        echo "✅ Column 'signature_path' already exists in users table\n";
    } else {
        echo "Adding 'signature_path' column to users table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL AFTER photo");
        echo "✅ Column added successfully!\n";
    }
    
    echo "\n=== Database Ready ===\n";
    echo "Admins can now upload their e-signature!\n";
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>❌ ERROR: " . $e->getMessage() . "</pre>";
}
