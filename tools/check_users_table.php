<?php
if (!defined('PREVENT_DIRECT_ACCESS')) define('PREVENT_DIRECT_ACCESS', true);
require __DIR__ . '/../app/config/database.php';
$config = $database['main'];
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['hostname'], $config['port'], $config['database'], $config['charset']);
try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $exists = $stmt->rowCount() > 0;
    echo "users table exists: ".($exists?"YES":"NO")."\n";
    if ($exists) {
        $rows = $pdo->query("SELECT id, fullname, username, email, created_at FROM users ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            echo json_encode($r)."\n";
        }
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
