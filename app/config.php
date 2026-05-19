<?php
// Localhost configuration (edit as needed).
// If you already have your own database name, set it below.

return [
  'db' => [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'college_db',  // <-- change to your DB name
    'user' => 'root',
    'pass' => 'Dbms@321',
    'charset' => 'utf8mb4',
  ],
  'session' => [
    'name' => 'placement_sess',
  ],
];

