<?php
session_start();
define('PREVENT_DIRECT_ACCESS', true);
define('ROOT_DIR', __DIR__ . '/../');
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

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userRecord = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h2>Step-by-Step Debug</h2>";

echo "<h3>1. Database Value:</h3>";
$photoPath = $userRecord['photo'] ?? ($_SESSION['photo'] ?? null);
echo "Raw photo from DB: <code>" . htmlspecialchars($photoPath ?? 'NULL') . "</code><br>";
echo "Is empty: " . (empty($photoPath) ? 'YES' : 'NO') . "<br><br>";

echo "<h3>2. buildPhotoUrl() Simulation:</h3>";

// Check if URL
if (preg_match('#^https?://#i', $photoPath)) {
    echo "Is HTTP URL: YES<br>";
    $finalUrl = $photoPath;
} else {
    echo "Is HTTP URL: NO<br>";
    
    // Sanitize path
    $normalized = ltrim(str_replace('\\', '/', trim((string) $photoPath)), '/');
    echo "After normalize: <code>" . htmlspecialchars($normalized) . "</code><br>";
    
    if (stripos($normalized, 'public/') !== 0) {
        if (stripos($normalized, 'uploads/') === 0) {
            $normalized = 'public/' . $normalized;
        } else {
            $normalized = 'public/uploads/profile_photos/' . basename($normalized);
        }
        echo "After public/ check: <code>" . htmlspecialchars($normalized) . "</code><br>";
    }
    
    // Get BASE_URL
    $baseUrl = defined('BASE_URL') && BASE_URL !== null
        ? BASE_URL
        : (config_item('BASE_URL') ?? '');
    
    echo "BASE_URL constant defined: " . (defined('BASE_URL') ? 'YES' : 'NO') . "<br>";
    echo "BASE_URL value: <code>" . htmlspecialchars($baseUrl) . "</code><br>";
    
    $baseUrl = $baseUrl !== '' ? rtrim((string) $baseUrl, '/') : '';
    echo "BASE_URL trimmed: <code>" . htmlspecialchars($baseUrl) . "</code><br>";
    
    $finalUrl = ($baseUrl !== '' ? $baseUrl . '/' : '/') . ltrim($normalized, '/');
}

echo "<h3>3. Final Result:</h3>";
echo "photo_url: <code>" . htmlspecialchars($finalUrl) . "</code><br>";
echo "Is empty: " . (empty($finalUrl) ? '<span style="color:red">YES - THIS IS THE PROBLEM!</span>' : '<span style="color:green">NO</span>') . "<br><br>";

echo "<h3>4. Image Test:</h3>";
if (!empty($finalUrl)) {
    echo "<img src='" . htmlspecialchars($finalUrl) . "' style='max-width:200px; border:3px solid blue;' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\"><br>";
    echo "URL: <code>" . htmlspecialchars($finalUrl) . "</code><br><br>";
    
    // Try alternative
    $alt = "/ecredentials/public/uploads/profile_photos/" . basename($photoPath);
    echo "<h3>Alternative Direct Path:</h3>";
    echo "<img src='" . htmlspecialchars($alt) . "' style='max-width:200px; border:3px solid green;' onerror=\"this.style.border='3px solid red'; this.alt='FAILED TO LOAD';\"><br>";
    echo "URL: <code>" . htmlspecialchars($alt) . "</code><br>";
} else {
    echo "<span style='color:red'>Cannot display image - photo_url is empty!</span><br>";
}

// Check file
echo "<h3>5. File System:</h3>";
$normalized = ltrim(str_replace('\\', '/', trim((string) $photoPath)), '/');
if (stripos($normalized, 'public/') !== 0 && stripos($normalized, 'uploads/') === 0) {
    $normalized = 'public/' . $normalized;
}
$filePath = ROOT_DIR . $normalized;
echo "File path: <code>" . htmlspecialchars($filePath) . "</code><br>";
echo "Exists: " . (file_exists($filePath) ? '<span style="color:green">YES</span>' : '<span style="color:red">NO</span>') . "<br>";
?>
