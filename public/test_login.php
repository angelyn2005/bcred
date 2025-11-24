<?php
// Test login directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$port = '3306';
$dbname = 'ecrdntldb';
$user = 'root';
$pass = '';

echo "<h2>Login Test Tool</h2>";
echo "<pre>";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $testEmail = 'admin@ph';
    $testPassword = 'admin123';
    
    echo "Testing login with:\n";
    echo "Email: $testEmail\n";
    echo "Password: $testPassword\n\n";
    
    // Get user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ User NOT FOUND in database!\n";
        echo "\nCreating admin account now...\n";
        
        $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'admin',
            'System Administrator',
            $testEmail,
            $hashedPassword,
            'admin',
            date('Y-m-d H:i:s')
        ]);
        
        echo "✅ Admin created! Try logging in again.\n";
        
    } else {
        echo "✅ User FOUND!\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Role: " . $user['role'] . "\n\n";
        
        // Test password
        if (password_verify($testPassword, $user['password'])) {
            echo "✅ PASSWORD VERIFIED SUCCESSFULLY!\n\n";
            echo "<strong style='color: green; font-size: 20px;'>✅ LOGIN SHOULD WORK!</strong>\n\n";
            echo "Use these credentials:\n";
            echo "Email: admin@ph\n";
            echo "Password: admin123\n\n";
            echo '<a href="/login" style="background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Go to Login Page</a>';
        } else {
            echo "❌ PASSWORD VERIFICATION FAILED!\n";
            echo "\nResetting password...\n";
            
            $newPassword = password_hash($testPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$newPassword, $testEmail]);
            
            echo "✅ Password has been reset! Try logging in now.\n";
        }
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "</pre>";
}
