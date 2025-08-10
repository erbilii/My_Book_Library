<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_login();

$pdo = db();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM books WHERE id=? AND user_id=?');
    $stmt->execute([$id, $user['id']]);
}
header('Location: ' . url('/books.php'));
exit;
