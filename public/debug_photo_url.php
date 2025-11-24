<?php
session_start();
define('PREVENT_DIRECT_ACCESS', true);
require __DIR__ . '/../app/config/database.php';
require __DIR__ . '/../app/config/config.php';

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

$userId = $_SESSION['user_id'];
$config = $database['main'];
$pdo = new PDO(
    "mysql:host={$config['hostname']};dbname={$config['database']}",
    $config['username'],
    $config['password']
);

$stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$photoPath = $user['photo'];
echo "<h2>Debug Photo URL</h2>";
echo "<strong>Photo value from DB:</strong> " . htmlspecialchars($photoPath) . "<br>";
echo "<strong>BASE_URL constant:</strong> " . (defined('BASE_URL') ? BASE_URL : 'NOT DEFINED') . "<br>";
echo "<strong>config BASE_URL:</strong> " . ($config['BASE_URL'] ?? 'NOT SET') . "<br>";

// Simulate buildPhotoUrl function
if (!empty($photoPath)) {
    $baseUrl = defined('BASE_URL') && BASE_URL !== null
        ? BASE_URL
        : ($config['BASE_URL'] ?? '');
    
    $baseUrl = $baseUrl !== '' ? rtrim((string) $baseUrl, '/') : '';
    
    $normalized = ltrim(str_replace('\\', '/', trim((string) $photoPath)), '/');
    if (stripos($normalized, 'public/') !== 0) {
        if (stripos($normalized, 'uploads/') === 0) {
            $normalized = 'public/' . $normalized;
        } else {
            $normalized = 'public/uploads/profile_photos/' . basename($normalized);
        }
    }
    
    $finalUrl = ($baseUrl !== '' ? $baseUrl . '/' : '/') . ltrim($normalized, '/');
    
    echo "<strong>Normalized path:</strong> " . htmlspecialchars($normalized) . "<br>";
    echo "<strong>Base URL used:</strong> " . htmlspecialchars($baseUrl) . "<br>";
    echo "<strong>Final URL:</strong> " . htmlspecialchars($finalUrl) . "<br><br>";
    
    echo "<h3>Image Test:</h3>";
    echo "<img src='" . htmlspecialchars($finalUrl) . "' style='max-width:200px; border:3px solid blue;' alt='Photo'><br>";
    echo "<small>If image appears above, URL is correct</small><br><br>";
    
    // Check file exists
    $absolutePath = __DIR__ . '/../' . $normalized;
    echo "<strong>Absolute file path:</strong> " . $absolutePath . "<br>";
    echo "<strong>File exists:</strong> " . (file_exists($absolutePath) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "<br>";
    
    if (file_exists($absolutePath)) {
        echo "<strong>File size:</strong> " . number_format(filesize($absolutePath)) . " bytes<br>";
        
        // Try direct path
        $directPath = '/ecredentials/' . $normalized;
        echo "<h3>Try Direct Path:</h3>";
        echo "<strong>Direct path:</strong> " . htmlspecialchars($directPath) . "<br>";
        echo "<img src='" . htmlspecialchars($directPath) . "' style='max-width:200px; border:3px solid green;' alt='Direct'><br>";
    }
}
?>
