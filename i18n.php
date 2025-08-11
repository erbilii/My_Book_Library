<?php
// i18n.php
$cfg = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang']) && isset($cfg['locales'][$_GET['lang']])) {
    $_SESSION['locale'] = $_GET['lang'];
}

$LOCALE = $_SESSION['locale'] ?? $cfg['default_locale'];
$RTL    = $cfg['locales'][$LOCALE]['rtl'] ?? false;

function t($key) {
    global $LOCALE, $STRINGS;
    return $STRINGS[$LOCALE][$key] ?? ($STRINGS['en'][$key] ?? $key);
}

$STRINGS = [
  'en' => [
    'app_title'=>'Book System Management',
    'login'=>'Login','email'=>'Email','password'=>'Password','sign_in'=>'Sign in',
    'logout'=>'Logout','dashboard'=>'Dashboard','books'=>'Books','users'=>'Users',
    'add_book'=>'Add Book','edit_book'=>'Edit Book','delete'=>'Delete','save'=>'Save','cancel'=>'Cancel',
    'title'=>'Title','author'=>'Author','isbn'=>'ISBN','year'=>'Year','language'=>'Language','genre'=>'Genre','tags'=>'Tags','description'=>'Description',
    'actions'=>'Actions','search'=>'Search','filters'=>'Filters','clear'=>'Clear',
    'role'=>'Role','admin'=>'Admin','editor'=>'Editor','viewer'=>'Viewer','name'=>'Name',
    'export'=>'Export','export_csv'=>'CSV','export_xlsx'=>'Excel','export_pdf'=>'PDF',
    'dark_mode'=>'Dark mode','language_ui'=>'UI Language',
  ],
  // (ckb, ar, fa, tr) … same as before
];
