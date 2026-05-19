<?php
require_once __DIR__ . '/../app/db.php';

header('Content-Type: text/plain; charset=utf-8');

try {
  $cfg = app_config();
  $dbCfg = $cfg['db'] ?? [];

  $safe = $dbCfg;
  if (isset($safe['pass']) && $safe['pass'] !== '') $safe['pass'] = '***set***';

  echo "OK: PHP is running\n";
  echo "Config loaded from: " . realpath(__DIR__ . '/../app/config.php') . "\n";
  echo "DB config: " . json_encode($safe, JSON_UNESCAPED_SLASHES) . "\n\n";

  $pdo = db();
  $ver = $pdo->query('SELECT VERSION() AS v')->fetch();
  echo "OK: MySQL connected\n";
  echo "MySQL version: " . ($ver['v'] ?? 'unknown') . "\n";
} catch (Throwable $e) {
  http_response_code(500);
  echo "ERROR: " . $e->getMessage() . "\n";
}

