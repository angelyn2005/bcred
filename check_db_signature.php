<?php
// Quick check - what's in the database for admin signature?
$pdo = new PDO('mysql:host=localhost;dbname=ecrdntldb', 'root', '');
$stmt = $pdo->query("SELECT id, email, signature_path FROM users WHERE email = 'admin@ph' OR role = 'admin' LIMIT 1");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Admin ID: " . $admin['id'] . "\n";
echo "Email: " . $admin['email'] . "\n";
echo "Signature Path: " . ($admin['signature_path'] ?? 'NULL') . "\n";

if (!empty($admin['signature_path'])) {
    $fullPath = __DIR__ . '/' . $admin['signature_path'];
    echo "Full Path: $fullPath\n";
    echo "File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
    
    if (file_exists($fullPath)) {
        echo "File Size: " . filesize($fullPath) . " bytes\n";
    }
} else {
    echo "\nERROR: No signature_path in database!\n";
    echo "Please check if the upload actually updated the database.\n";
}
