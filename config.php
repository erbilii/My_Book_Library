<?php
// config.php
// Adjust these for your environment
return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'book_system',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
  ],
  // Default UI language if none selected
  'default_locale' => 'en',
  // Supported UI languages (RTL aware)
  'locales' => [
    'en' => ['label' => 'English', 'rtl' => false],
    'ckb' => ['label' => 'Kurdî (Sorani)', 'rtl' => true],
    'ar' => ['label' => 'العربية', 'rtl' => true],
    'fa' => ['label' => 'فارسی', 'rtl' => true],
    'tr' => ['label' => 'Türkçe', 'rtl' => false],
  ],
  // App name
  'app_name' => 'Book System Management',
];