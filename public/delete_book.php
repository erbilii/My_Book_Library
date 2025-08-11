<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_login();
require_role(['admin']);
$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM books WHERE id=?');
    $stmt->execute([$id]);
}
header('Location: /public/dashboard.php');