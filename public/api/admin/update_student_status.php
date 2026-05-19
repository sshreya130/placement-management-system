<?php

require_once __DIR__ . '/../../../app/response.php';
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/repo.php';

require_login('admin');

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!is_array($body)) {
  error_response('Invalid JSON', 400);
}

$studentId = (int)($body['student_id'] ?? 0);
$status = trim((string)($body['placement_status'] ?? ''));

if ($studentId <= 0) {
  error_response('Student ID is required', 422);
}

if (!in_array($status, ['Placed', 'Not Placed'], true)) {
  error_response('Invalid placement status', 422);
}

try {
  repo_update_student_placement_status($studentId, $status);
  json_response(['ok' => true]);
} catch (Throwable $e) {
  error_response('DB error: ' . $e->getMessage(), 500);
}