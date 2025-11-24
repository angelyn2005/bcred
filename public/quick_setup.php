<?php
// Quick admin setup - No framework dependencies
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database config
$host = 'localhost';
$port = '3306';
$dbname = 'ecrdntldb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h2>Admin Setup Tool</h2><pre>";
    
    // First, check if admin@ph exists
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@ph']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "Admin already exists:\n";
        print_r($admin);
        echo "\n\nUpdating password...\n";
        
        $newPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE email = ?");
        $stmt->execute([$newPassword, 'admin@ph']);
        
        echo "✅ Password updated!\n";
    } else {
        echo "No admin found. Creating new admin...\n";
        
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            'System Administrator',
            'admin@ph',
            $password,
            'admin',
            date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Admin created!\n";
    }
    
    // Verify the result
    echo "\n=== Verification ===\n";
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE email = ?");
    $stmt->execute(['admin@ph']);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "ID: " . $result['id'] . "\n";
        echo "Username: " . $result['username'] . "\n";
        echo "Email: " . $result['email'] . "\n";
        echo "Role: " . $result['role'] . "\n";
        echo "Created: " . $result['created_at'] . "\n\n";
        
        // Test password verification
        $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->execute(['admin@ph']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify('admin123', $row['password'])) {
            echo "✅ Password verification: SUCCESS\n";
            echo "\n<strong style='color: green; font-size: 18px;'>✅ You can now login with:</strong>\n";
            echo "<strong>Email:</strong> admin@ph\n";
            echo "<strong>Password:</strong> admin123\n\n";
            echo '<a href="/login" style="background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Go to Login</a>';
        } else {
            echo "❌ Password verification: FAILED\n";
            echo "There might be an issue with password hashing.\n";
        }
    } else {
        echo "❌ Could not find admin account after creation!\n";
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<pre>❌ ERROR: " . $e->getMessage() . "</pre>";
}
