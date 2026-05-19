<?php

function app_config(): array {
  static $cfg = null;
  if ($cfg !== null) return $cfg;

  $path = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
  if (!file_exists($path)) {
    throw new RuntimeException("Missing app/config.php. Copy app/config.sample.php to app/config.php and edit it.");
  }

  /** @var array $cfg */
  $cfg = require $path;
  return $cfg;
}

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  $cfg = app_config();
  $db = $cfg['db'];
  $dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $db['host'],
    (int)$db['port'],
    $db['name'],
    $db['charset'] ?? 'utf8mb4'
  );

  $pdo = new PDO($dsn, $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);

  return $pdo;
}

