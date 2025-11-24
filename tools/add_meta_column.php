<?php
if (!defined('PREVENT_DIRECT_ACCESS')) define('PREVENT_DIRECT_ACCESS', true);
require __DIR__ . '/../app/config/database.php';
$config = $database['main'];
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['hostname'], $config['port'], $config['database'], $config['charset']);
try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $sql = "ALTER TABLE email_verifications ADD COLUMN meta TEXT DEFAULT NULL AFTER verified_at";
    $pdo->exec($sql);
    echo "ALTER TABLE executed: meta column added.\n";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
