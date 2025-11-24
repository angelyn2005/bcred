<?php
// Direct admin seeder script
require_once 'app/config/database.php';

try {
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
    
    echo "Connected to database successfully!\n\n";
    
    // Check if admin exists by email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['admin@ph']);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    
    if ($existing) {
        echo "Admin account exists. Updating...\n";
        $stmt = $pdo->prepare("UPDATE users SET password = ?, fullname = ?, email = ?, role = 'admin' WHERE id = ?");
        $stmt->execute([$password, 'System Administrator', 'admin@ph', $existing['id']]);
        echo "✅ Admin account UPDATED successfully!\n\n";
    } else {
        echo "Admin account does not exist. Creating...\n";
        $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            'System Administrator',
            'admin@ph',
            $password,
            'admin',
            date('Y-m-d H:i:s')
        ]);
        echo "✅ Admin account CREATED successfully!\n\n";
    }
    
    // Verify the account
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@ph']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "=== Admin Account Details ===\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Email: " . $admin['email'] . "\n";
    echo "Role: " . $admin['role'] . "\n";
    echo "Password: admin123\n\n";
    
    echo "✅ You can now login with:\n";
    echo "   Email: admin@ph\n";
    echo "   Password: admin123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
