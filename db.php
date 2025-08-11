<?php
// db.php
function db(): PDO
{
    static $pdo;
    if ($pdo)
        return $pdo;
    $cfg = require __DIR__ . '/config.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['db']['host'],
        $cfg['db']['port'],
        $cfg['db']['name'],
        $cfg['db']['charset']
    );
    $pdo = new PDO($dsn, $cfg['db']['user'], $cfg['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}