<?php
// Quick database check and fix for signature column
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = 'localhost';
$db = 'ecrdntldb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Database Signature Column Check</h2>";
    
    // Check if column exists
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasSignatureColumn = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'signature_path') {
            $hasSignatureColumn = true;
            echo "<p style='color:green'>✓ Column 'signature_path' exists in users table</p>";
            echo "<pre>Type: {$col['Type']}\nNull: {$col['Null']}\nDefault: {$col['Default']}</pre>";
            break;
        }
    }
    
    if (!$hasSignatureColumn) {
        echo "<p style='color:red'>✗ Column 'signature_path' does NOT exist</p>";
        echo "<p>Attempting to add column...</p>";
        
        $pdo->exec("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL AFTER photo");
        
        echo "<p style='color:green'>✓ Column added successfully!</p>";
    }
    
    // Show all columns
    echo "<h3>All columns in users table:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Default']}</td></tr>";
    }
    echo "</table>";
    
    // Check upload directory
    $uploadDir = __DIR__ . '/uploads/signatures/';
    echo "<h3>Upload Directory Check:</h3>";
    echo "<p>Path: $uploadDir</p>";
    echo "<p>Exists: " . (is_dir($uploadDir) ? '✓ Yes' : '✗ No') . "</p>";
    echo "<p>Writable: " . (is_writable($uploadDir) ? '✓ Yes' : '✗ No') . "</p>";
    
    // PHP settings
    echo "<h3>PHP Upload Settings:</h3>";
    echo "<p>upload_max_filesize: " . ini_get('upload_max_filesize') . "</p>";
    echo "<p>post_max_size: " . ini_get('post_max_size') . "</p>";
    echo "<p>max_file_uploads: " . ini_get('max_file_uploads') . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
}
