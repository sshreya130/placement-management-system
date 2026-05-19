<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '/db.php';

function base_path(): string {
  $script = $_SERVER['SCRIPT_NAME'] ?? '';
  $pos = strpos($script, '/public/');
  if ($pos === false) return '';
  return rtrim(substr($script, 0, $pos + 7), '/'); // include "/public"
}

function ensure_session_started(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;

  $cfg = app_config();
  $name = $cfg['session']['name'] ?? 'app_sess';
  session_name($name);
  session_start();
}

function current_user(): ?array {
  ensure_session_started();
  return $_SESSION['user'] ?? null;
}

function require_login(?string $role = null): array {
  $u = current_user();
  if (!$u) {
    $base = base_path();
    header('Location: ' . ($base !== '' ? $base : '') . '/index.php');
    exit;
  }
  if ($role !== null && ($u['role'] ?? null) !== $role) {
    http_response_code(403);
    echo "Forbidden";
    exit;
  }
  return $u;
}

function login_user(array $userRow): void {
  ensure_session_started();
  $_SESSION['user'] = [
    'id' => (int)$userRow['id'],
    'role' => $userRow['role'],
    'name' => $userRow['name'],
    'email' => $userRow['email'],
  ];
}

function logout_user(): void {
  ensure_session_started();
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
}

function verify_password(string $plain, string $hash): bool {
  return password_verify($plain, $hash);
}

