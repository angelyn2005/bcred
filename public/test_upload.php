<?php
// Test signature upload directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

echo json_encode([
    'POST' => $_POST,
    'FILES' => $_FILES,
    'SESSION' => $_SESSION ?? 'No session',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'signatures_dir_exists' => is_dir(__DIR__ . '/uploads/signatures/'),
    'signatures_dir_writable' => is_writable(__DIR__ . '/uploads/signatures/'),
]);
