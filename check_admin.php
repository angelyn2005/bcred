<?php
// Check admin account
require_once 'app/config/database.php';

$dbConfig = &database_config();
$cfg = $dbConfig['main'];

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', 
    $cfg['hostname'], 
    $cfg['port'], 
    $cfg['database'], 
    $cfg['charset']
);

$pdo = new PDO($dsn, $cfg['username'], $cfg['password']);

// Check for admin@ph
echo "Checking for admin@ph...\n";
$stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
$stmt->execute(['admin@ph']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✅ Found!\n";
    print_r($admin);
} else {
    echo "❌ NOT FOUND - Creating now...\n\n";
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        'admin',
        'System Administrator',
        'admin@ph',
        $password,
        'admin',
        date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        echo "✅ Created successfully!\n";
        echo "Email: admin@ph\n";
        echo "Password: admin123\n";
    } else {
        echo "❌ Failed to create\n";
    }
}
