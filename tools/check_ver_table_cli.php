<?php
if (!defined('PREVENT_DIRECT_ACCESS')) {
    define('PREVENT_DIRECT_ACCESS', true);
}
require __DIR__ . '/../app/config/database.php';

$config = $database['main'];
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['hostname'], $config['port'], $config['database'], $config['charset']);
try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SHOW TABLES LIKE 'email_verifications'");
    $exists = $stmt->rowCount() > 0;
    echo "email_verifications table exists: ".($exists?"YES":"NO")."\n";
    if ($exists) {
        $rows = $pdo->query("SELECT id, email, code, created_at, expires_at, verified_at, meta FROM email_verifications ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            echo json_encode($r)."\n";
        }
    }
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
