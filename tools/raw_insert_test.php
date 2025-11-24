<?php
if (!defined('PREVENT_DIRECT_ACCESS')) define('PREVENT_DIRECT_ACCESS', true);
require __DIR__ . '/../app/config/database.php';
$config = $database['main'];
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['hostname'], $config['port'], $config['database'], $config['charset']);
try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $email = 'rawtest+' . rand(100,999) . '@example.com';
    $code = (string) rand(100000,999999);
    $meta = json_encode(['fullname'=>'Raw Test','username'=>'rawtest' . rand(100,999),'email'=>$email,'password'=>'x','role'=>'resident','contact'=>'0917','address'=>'addr','created_at'=>date('Y-m-d H:i:s')]);
    $sql = "INSERT INTO email_verifications (user_id, email, code, expires_at, created_at, meta) VALUES (NULL, ?, ?, DATE_ADD(NOW(), INTERVAL 45 SECOND), NOW(), ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $code, $meta]);
    echo "Inserted raw test with id: " . $pdo->lastInsertId() . "\n";
    $row = $pdo->query('SELECT * FROM email_verifications ORDER BY id DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row) . "\n";
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
