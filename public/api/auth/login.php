<?php

require_once __DIR__ . '/../../../app/response.php';
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/repo.php';

ensure_session_started();

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) error_response('Invalid JSON', 400);

$email = trim((string)($body['email'] ?? ''));
$password = (string)($body['password'] ?? '');
$role = (string)($body['role'] ?? 'student');
if (!in_array($role, ['student', 'admin', 'company'], true)) $role = 'student';

if ($email === '') error_response('Email is required', 422);

try {
  $user = repo_find_user_by_email_and_role($email, $role);
  if (!$user) error_response('Invalid credentials', 401);

  if (!empty($user['password_hash'])) {
    if ($password === '') error_response('Password is required', 422);
    if (!verify_password($password, (string)$user['password_hash'])) {
      error_response('Invalid credentials', 401);
    }
  }

  login_user($user);

  $base = base_path();

  $redirect = $base . '/index.php';
  if ($role === 'student') $redirect = $base . '/student/dashboard.php';
  if ($role === 'admin') $redirect = $base . '/admin/dashboard.php';
  if ($role === 'company') $redirect = $base . '/company/dashboard.php';

  json_response(['ok' => true, 'redirect' => $redirect]);
} catch (Throwable $e) {
  error_response('Server error: ' . $e->getMessage(), 500);
}