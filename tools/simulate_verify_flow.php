<?php
if (!defined('PREVENT_DIRECT_ACCESS')) define('PREVENT_DIRECT_ACCESS', true);
require __DIR__ . '/../app/config/database.php';
$config = $database['main'];
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $config['hostname'], $config['port'], $config['database'], $config['charset']);
try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Prepare test meta
    $meta = [
        'fullname' => 'Test User ' . rand(1000,9999),
        'username' => 'testuser' . rand(1000,9999),
        'email' => 'test+' . rand(1000,9999) . '@example.com',
        'password' => password_hash('Password123!', PASSWORD_DEFAULT),
        'role' => 'resident',
        'contact' => '09171234567',
        'address' => 'Test Address',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $code = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Insert verification
    $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, email, code, expires_at, created_at, meta) VALUES (NULL, ?, ?, ?, NOW(), ?)");
    $stmt->execute([$meta['email'], (string)$code, $expires_at, json_encode($meta)]);
    $vid = $pdo->lastInsertId();
    echo "Inserted verification id={$vid}, code={$code}\n";

    // Simulate verification lookup
    $stmt = $pdo->prepare("SELECT * FROM email_verifications WHERE id = ? AND code = ? AND expires_at >= NOW() AND verified_at IS NULL");
    $stmt->execute([$vid, (string)$code]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo "Verification lookup failed\n";
        exit(1);
    }

    echo "Found verification row id={$row['id']}\n";

    $meta = json_decode($row['meta'], true) ?: [];

    // Insert into users table
    $ins = $pdo->prepare("INSERT INTO users (fullname, username, email, photo, password, role, created_at, contact, address) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, ?)");
    $ins->execute([
        $meta['fullname'], $meta['username'], $meta['email'], $meta['password'], $meta['role'], $meta['created_at'], $meta['contact'], $meta['address']
    ]);
    $newUserId = $pdo->lastInsertId();
    echo "Created user id={$newUserId}\n";

    // Mark verification
    $upd = $pdo->prepare("UPDATE email_verifications SET verified_at = NOW(), user_id = ? WHERE id = ?");
    $upd->execute([$newUserId, $vid]);
    echo "Marked verification as verified.\n";

    // Show created user row
    $u = $pdo->query("SELECT id, fullname, username, email, created_at FROM users WHERE id = $newUserId")->fetch(PDO::FETCH_ASSOC);
    echo json_encode($u) . "\n";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
