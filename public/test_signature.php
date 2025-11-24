<?php
// Test signature retrieval
define('PREVENT_DIRECT_ACCESS', true);
define('ROOT_DIR', __DIR__ . '/');

require_once 'vendor/autoload.php';

session_start();

// Database connection
$host = 'localhost';
$db = 'ecrdntldb';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Signature Path Verification</h2>";
    
    // Get admin user (should be ID 1 for admin@ph)
    $stmt = $pdo->prepare("SELECT id, username, email, signature_path FROM users WHERE email = 'admin@ph' OR role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "<h3>Admin User Found:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><td>{$admin['id']}</td></tr>";
        echo "<tr><th>Username</th><td>{$admin['username']}</td></tr>";
        echo "<tr><th>Email</th><td>{$admin['email']}</td></tr>";
        echo "<tr><th>Signature Path (DB)</th><td>" . ($admin['signature_path'] ?: '<em>NULL/Empty</em>') . "</td></tr>";
        echo "</table>";
        
        if (!empty($admin['signature_path'])) {
            $fullPath = ROOT_DIR . $admin['signature_path'];
            echo "<h3>File Check:</h3>";
            echo "<p><strong>Full Path:</strong> $fullPath</p>";
            echo "<p><strong>File Exists:</strong> " . (file_exists($fullPath) ? '✅ YES' : '❌ NO') . "</p>";
            
            if (file_exists($fullPath)) {
                echo "<p><strong>File Size:</strong> " . filesize($fullPath) . " bytes</p>";
                echo "<p><strong>MIME Type:</strong> " . mime_content_type($fullPath) . "</p>";
                echo "<h3>Signature Preview:</h3>";
                echo "<img src='/{$admin['signature_path']}' style='max-width:300px; border:1px solid #ccc; padding:10px;'>";
                
                // Test base64 encoding
                echo "<h3>Base64 Encoding Test:</h3>";
                $imageData = base64_encode(file_get_contents($fullPath));
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $imageSrc = 'data:' . $mimeType . ';base64,' . $imageData;
                echo "<p>Base64 length: " . strlen($imageData) . " characters</p>";
                echo "<img src='$imageSrc' style='max-width:300px; border:1px solid #ccc; padding:10px;'>";
            }
        } else {
            echo "<p style='color:red;'>⚠️ No signature uploaded yet. Please upload a signature in Settings.</p>";
        }
    } else {
        echo "<p style='color:red;'>No admin user found!</p>";
    }
    
    // List all uploaded signatures
    $sigDir = ROOT_DIR . 'public/uploads/signatures/';
    echo "<h3>Signature Files in Directory:</h3>";
    echo "<p>Directory: $sigDir</p>";
    
    if (is_dir($sigDir)) {
        $files = scandir($sigDir);
        $files = array_diff($files, ['.', '..']);
        
        if (count($files) > 0) {
            echo "<ul>";
            foreach ($files as $file) {
                echo "<li>$file - " . filesize($sigDir . $file) . " bytes</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No files in signatures directory</p>";
        }
    } else {
        echo "<p style='color:red;'>Signatures directory doesn't exist!</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . $e->getMessage() . "</p>";
}
