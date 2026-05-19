<?php

require_once __DIR__ . '/../../../app/response.php';
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/repo.php';

$u = require_login('admin'); // admin_id comes from placement_cell.admin_id

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) error_response('Invalid JSON', 400);

$driveId = (int)($body['drive_id'] ?? 0);
$companyId = (int)($body['company_id'] ?? 0);
$role = trim((string)($body['role'] ?? ''));
$cgpaCutoff = $body['cgpa_cutoff'] ?? null;
$deadline = (string)($body['deadline'] ?? '');
$driveDate = (string)($body['drive_date'] ?? '');
$status = trim((string)($body['status'] ?? ''));

if ($driveId <= 0) error_response('drive_id is required', 422);
if ($companyId <= 0) error_response('company_id is required', 422);

$cgpa = null;
if ($cgpaCutoff !== null && $cgpaCutoff !== '') {
  $cgpa = (float)$cgpaCutoff;
  if ($cgpa < 0 || $cgpa > 10) error_response('cgpa_cutoff must be between 0 and 10', 422);
}

if ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) error_response('deadline must be YYYY-MM-DD', 422);
if ($driveDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $driveDate)) error_response('drive_date must be YYYY-MM-DD', 422);

try {
  repo_create_drive_v2(
    $driveId,
    $companyId,
    (int)$u['id'],
    $role !== '' ? $role : null,
    $cgpa,
    $deadline !== '' ? $deadline : null,
    $driveDate !== '' ? $driveDate : null,
    $status !== '' ? $status : null
  );
  json_response(['ok' => true]);
} catch (PDOException $e) {
  if ((int)($e->errorInfo[1] ?? 0) === 1062) error_response('Drive ID already exists', 409);
  error_response('DB error: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
  error_response('Server error: ' . $e->getMessage(), 500);
}

