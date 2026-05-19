<?php

require_once __DIR__ . '/../../../app/response.php';
require_once __DIR__ . '/../../../app/auth.php';
require_once __DIR__ . '/../../../app/repo.php';

$u = require_login('admin');

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) error_response('Invalid JSON', 400);

$companyId = (int)($body['company_id'] ?? 0);
$name = trim((string)($body['company_name'] ?? ''));
$industryType = trim((string)($body['industry_type'] ?? ''));
$hrEmail = trim((string)($body['hr_email'] ?? ''));
$packageOffered = $body['package_offered'] ?? null;

if ($companyId <= 0) error_response('company_id is required', 422);
if ($name === '') error_response('company_name is required', 422);

$pkg = null;
if ($packageOffered !== null && $packageOffered !== '') {
  $pkg = (float)$packageOffered;
  if ($pkg < 0) error_response('package_offered must be >= 0', 422);
}

try {
  repo_create_company_v2(
    $companyId,
    $name,
    $industryType !== '' ? $industryType : null,
    $hrEmail !== '' ? $hrEmail : null,
    $pkg
  );
  json_response(['ok' => true]);
} catch (PDOException $e) {
  if ((int)($e->errorInfo[1] ?? 0) === 1062) error_response('Company ID already exists', 409);
  error_response('DB error: ' . $e->getMessage(), 500);
} catch (Throwable $e) {
  error_response('Server error: ' . $e->getMessage(), 500);
}

