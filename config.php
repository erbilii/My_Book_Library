<?php
// config.php
return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'book_system',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
  ],
  'default_locale' => 'en',
  'locales' => [
    'en' => ['label' => 'English', 'rtl' => false],
  ],
  'app_name' => 'Book System Management',
];
