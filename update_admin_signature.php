<?php
// Update existing signature file to admin user
$pdo = new PDO('mysql:host=localhost;dbname=ecrdntldb', 'root', '');

// Find signature files
$sigDir = __DIR__ . '/public/uploads/signatures/';
$files = scandir($sigDir);
$files = array_diff($files, ['.', '..']);

if (count($files) > 0) {
    // Get the most recent signature file
    $latestFile = null;
    $latestTime = 0;
    
    foreach ($files as $file) {
        $filePath = $sigDir . $file;
        $mtime = filemtime($filePath);
        if ($mtime > $latestTime) {
            $latestTime = $mtime;
            $latestFile = $file;
        }
    }
    
    if ($latestFile) {
        $relativePath = 'public/uploads/signatures/' . $latestFile;
        
        // Update admin user
        $stmt = $pdo->prepare("UPDATE users SET signature_path = ? WHERE role = 'admin'");
        $stmt->execute([$relativePath]);
        
        echo "SUCCESS!\n";
        echo "Updated admin signature to: $relativePath\n";
        echo "Rows affected: " . $stmt->rowCount() . "\n";
        
        // Verify
        $check = $pdo->query("SELECT id, email, signature_path FROM users WHERE role = 'admin'")->fetch(PDO::FETCH_ASSOC);
        echo "\nAdmin user:\n";
        echo "ID: " . $check['id'] . "\n";
        echo "Email: " . $check['email'] . "\n";
        echo "Signature: " . $check['signature_path'] . "\n";
    }
} else {
    echo "No signature files found in $sigDir\n";
    echo "Please upload a signature first.\n";
}
