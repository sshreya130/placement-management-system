<?php

require_once __DIR__ . '/../../../app/response.php';
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/repo.php';

$u = require_login('student');

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) error_response('Invalid JSON', 400);

$driveId = (int)($body['drive_id'] ?? 0);
if ($driveId <= 0) error_response('drive_id is required', 422);

try {
  repo_apply_to_drive($driveId, $u['id']);
  json_response(['ok' => true]);
} catch (PDOException $e) {
  // Duplicate apply
  if ((int)($e->errorInfo[1] ?? 0) === 1062) {
    error_response('Already applied', 409);
  }
  error_response('DB error: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
  error_response('Server error: ' . $e->getMessage(), 500);
}

