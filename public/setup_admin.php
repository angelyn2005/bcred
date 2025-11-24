<?php
// Web-accessible admin creator
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Account Setup</h1>";
echo "<pre>";

try {
    require_once __DIR__ . '/app/config/database.php';
    
    $dbConfig = &database_config();
    $cfg = $dbConfig['main'];
    
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', 
        $cfg['hostname'], 
        $cfg['port'], 
        $cfg['database'], 
        $cfg['charset']
    );
    
    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Database connected!\n\n";
    
    // Check existing admin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@ph']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    if ($existing) {
        echo "Found existing admin. Updating...\n";
        $stmt = $pdo->prepare("UPDATE users SET password = ?, fullname = ?, role = 'admin' WHERE email = ?");
        $stmt->execute([$password, 'System Administrator', 'admin@ph']);
        echo "✅ UPDATED\n\n";
    } else {
        echo "Creating new admin...\n";
        $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            'System Administrator',
            'admin@ph',
            $password,
            'admin',
            date('Y-m-d H:i:s')
        ]);
        echo "✅ CREATED\n\n";
    }
    
    // Verify
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@ph']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "=== Admin Details ===\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Password: admin123\n\n";
    
    echo "✅ Ready to login!\n";
    echo 'Go to: <a href="/login">/login</a>';
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}

echo "</pre>";
