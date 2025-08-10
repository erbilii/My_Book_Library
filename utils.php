<?php
function esc($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
function paginate($total, $page, $per_page) {
    $pages = max(1, (int)ceil($total / $per_page));
    $page = max(1, min($page, $pages));
    return [$page, $pages];
}
?>
