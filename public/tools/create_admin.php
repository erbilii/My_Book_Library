<?php
require_once __DIR__ . '/../../db.php';

$pdo = db();
$email = 'admin@example.com';
$pass  = 'admin123'; // change after first login
$hash  = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
  INSERT INTO users (name,email,password_hash,role)
  VALUES ('Admin', ?, ?, 'admin')
  ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash), role='admin', name='Admin'
");
$stmt->execute([$email, $hash]);

echo "OK. Admin upserted: {$email} / {$pass}";
