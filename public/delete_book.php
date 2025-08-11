<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_login();
require_role(['admin']);

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // remove cover file if present
    $stmt = $pdo->prepare('SELECT cover FROM books WHERE id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['cover'])) {
        $abs = __DIR__ . '/' . $row['cover'];
        if (is_file($abs)) {
            @unlink($abs);
        }
    }

    $stmt = $pdo->prepare('DELETE FROM books WHERE id=?');
    $stmt->execute([$id]);
}

header('Location: dashboard.php');
