<?php
// Add signature_path column to users table
$host = 'localhost';
$db = 'ecrdntldb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'signature_path'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "Column 'signature_path' already exists!\n";
    } else {
        // Add the column
        $pdo->exec("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL AFTER photo");
        echo "SUCCESS! Column 'signature_path' has been added to users table.\n";
    }
    
    // Verify
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('signature_path', $columns)) {
        echo "\nVerified: signature_path column exists in users table.\n";
        echo "You can now upload signatures!\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
