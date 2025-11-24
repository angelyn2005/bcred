<?php
define('PREVENT_DIRECT_ACCESS', true);
require __DIR__ . '/../app/config/database.php';

$config = $database['main'];
$pdo = new PDO(
    "mysql:host={$config['hostname']};dbname={$config['database']}",
    $config['username'],
    $config['password']
);

$stmt = $pdo->query("SELECT id, username, fullname, photo FROM users WHERE id = 37");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo "User ID: " . $user['id'] . "\n";
echo "Username: " . $user['username'] . "\n";
echo "Fullname: " . $user['fullname'] . "\n";
echo "Photo value: [" . ($user['photo'] ?? 'NULL') . "]\n";
echo "Photo is empty: " . (empty($user['photo']) ? 'YES' : 'NO') . "\n";
echo "Photo length: " . strlen($user['photo'] ?? '') . "\n";

// Check if file exists
if (!empty($user['photo'])) {
    $possiblePaths = [
        __DIR__ . '/../' . $user['photo'],
        __DIR__ . '/../public/' . $user['photo'],
        __DIR__ . '/../public/uploads/profile_photos/' . basename($user['photo']),
    ];
    
    echo "\nChecking file locations:\n";
    foreach ($possiblePaths as $path) {
        echo "  $path ... " . (file_exists($path) ? "EXISTS" : "NOT FOUND") . "\n";
    }
}
