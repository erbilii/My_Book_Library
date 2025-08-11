<?php
// i18n.php (English-only)
$cfg = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Force English only
$LOCALE = 'en';
$RTL = false;

function t($key)
{
  global $STRINGS, $LOCALE;
  return $STRINGS[$LOCALE][$key] ?? $key;
}

$STRINGS = [
  'en' => [
    'app_title' => 'Book System Management',
    'login' => 'Login',
    'email' => 'Email',
    'password' => 'Password',
    'sign_in' => 'Sign in',
    'logout' => 'Logout',
    'dashboard' => 'Dashboard',
    'books' => 'Books',
    'users' => 'Users',
    'add_book' => 'Add Book',
    'edit_book' => 'Edit Book',
    'delete' => 'Delete',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'title' => 'Title',
    'author' => 'Author',
    'isbn' => 'ISBN',
    'year' => 'Year',
    'genre' => 'Genre',
    'tags' => 'Tags',
    'description' => 'Description',
    'actions' => 'Actions',
    'search' => 'Search',
    'filters' => 'Filters',
    'clear' => 'Clear',
    'role' => 'Role',
    'admin' => 'Admin',
    'editor' => 'Editor',
    'viewer' => 'Viewer',
    'name' => 'Name',
    'export' => 'Export',
    'export_csv' => 'CSV',
    'export_xlsx' => 'Excel',
    'export_pdf' => 'PDF',
    'dark_mode' => 'Dark mode',
  ],
];
