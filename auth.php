<?php
// auth.php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!current_user()) {
        header('Location: /index.php'); // works even if vhost points at /public
        exit;
    }
}

function require_role($roles) {
    $u = current_user();
    if (!$u || !in_array($u['role'], (array)$roles, true)) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1>';
        exit;
    }
}

function login($email, $password) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}
