<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../i18n.php';
$cfg = require __DIR__ . '/../../config.php';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($LOCALE) ?>" dir="<?= $RTL ? 'rtl' : 'ltr' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Apply saved theme early (Bootstrap 5.3 color modes) -->
    <script>
        (function () {
            try {
                var t = localStorage.getItem('theme');
                if (!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    t = 'dark';
                }
                if (!t) t = 'light';
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    <title><?= htmlspecialchars($cfg['app_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>