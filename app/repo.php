<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . '/db.php';

/**
 * All SQL lives here so you can adapt it to YOUR existing tables/columns.
 * If your table names differ, update only this file.
 */

function repo_find_user_by_email_and_role(string $email, string $role): ?array {
  // Your schema (from screenshot) uses separate tables:
  // - student(student_id, name, email, branch, cgpa, ...)
  // - placement_cell(admin_id, name, email, role)
  // - company(company_id, company_name, ... , hr_...)
  //
  // Password column is not visible in your screenshot, so this project uses
  // "email-only" login by default. If you DO have a password column, tell me
  // its name and I’ll wire it up to require password verification.

  if ($role === 'student') {
    $stmt = db()->prepare('SELECT student_id AS id, name, email FROM student WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) return null;
    return [
      'id' => (int)$row['id'],
      'role' => 'student',
      'name' => $row['name'],
      'email' => $row['email'],
      'password_hash' => null,
    ];
  }

  if ($role === 'admin') {
    $stmt = db()->prepare("SELECT admin_id AS id, name, email FROM placement_cell WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) return null;
    return [
      'id' => (int)$row['id'],
      'role' => 'admin',
      'name' => $row['name'],
      'email' => $row['email'],
      'password_hash' => null,
    ];
  }

  if ($role === 'company') {
    $stmt = db()->prepare('SELECT company_id AS id, company_name AS name, hr_email AS email
                           FROM company
                           WHERE hr_email = ?
                           LIMIT 1');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if (!$row) return null;
    return [
      'id' => (int)$row['id'],
      'role' => 'company',
      'name' => $row['name'],
      'email' => $row['email'],
      'password_hash' => null,
    ];
  }

  return null;
}

function repo_student_profile(int $userId): ?array {
  $stmt = db()->prepare('SELECT student_id AS id, name, email, branch, cgpa, graduation_year,placement_status
                         FROM student
                         WHERE student_id = ?
                         LIMIT 1');
  $stmt->execute([$userId]);
  $row = $stmt->fetch();
  if (!$row) return null;
  // Normalize keys expected by UI
  return [
    'id' => (int)$row['id'],
    'name' => $row['name'],
    'email' => $row['email'],
    // Your table doesn't have a dedicated USN column, so we show student_id as USN.
    'usn' => (string)($row['id']),
    'department' => $row['branch'],
    'cgpa' => $row['cgpa'],
    'graduation_year' => $row['graduation_year'] ?? null,
    'placement_status' => $row['placement_status'] ?? 'Not Placed',
  ];
}

function repo_company_for_user(int $userId): ?array {
  // In your schema, company users are in `company` itself.
  $stmt = db()->prepare('SELECT company_id AS id,
                                company_name AS name,
                                industry_type,
                                hr_email,
                                package_offered
                         FROM company
                         WHERE company_id = ?
                         LIMIT 1');
  $stmt->execute([$userId]);
  $row = $stmt->fetch();
  if (!$row) return null;
  return [
    'id' => (int)$row['id'],
    'name' => $row['name'],
    'website' => null,
    'min_cgpa' => null,
    'industry_type' => $row['industry_type'] ?? null,
    'hr_email' => $row['hr_email'] ?? null,
    'package_offered' => $row['package_offered'] ?? null,
  ];
}

function repo_list_companies(): array {
  $stmt = db()->query('SELECT company_id AS id,
                              company_name AS name,
                              industry_type,
                              hr_email,
                              package_offered
                       FROM company
                       ORDER BY company_id DESC');
  $rows = $stmt->fetchAll();
  return array_map(static function ($r) {
    return [
      'id' => (int)$r['id'],
      'name' => $r['name'],
      'website' => null,
      'min_cgpa' => null,
      'created_at' => null,
      'industry_type' => $r['industry_type'] ?? null,
      'hr_email' => $r['hr_email'] ?? null,
      'package_offered' => $r['package_offered'] ?? null,
    ];
  }, $rows);
}

function repo_create_company(int $adminUserId, string $name, ?string $website, float $minCgpa): int {
  // Your company table:
  // company(company_id PK NOT NULL, company_name, industry_type, hr_email, package_offered)
  // company_id is NOT auto-increment, so we accept it from the caller.
  throw new RuntimeException('Use repo_create_company_v2 (with company_id) for your schema.');
}

function repo_create_company_v2(int $companyId, string $name, ?string $industryType, ?string $hrEmail, ?float $packageOffered): void {
  $stmt = db()->prepare('INSERT INTO company (company_id, company_name, industry_type, hr_email, package_offered)
                         VALUES (?, ?, ?, ?, ?)');
  $stmt->execute([
    $companyId,
    $name,
    $industryType,
    $hrEmail,
    $packageOffered,
  ]);
}

function repo_list_drives_for_student(int $studentUserId): array {
  // Uses your tables:
  // - job_drive(drive_id, company_id, admin_id, role, cgpa_cutoff, ...)
  // - company(company_id, company_name, ...)
  // - application(application_id, student_id, drive_id, ...)
  // Eligible if student.cgpa >= job_drive.cgpa_cutoff

  $stmt = db()->prepare("
    SELECT jd.drive_id AS id,
           jd.role AS title,
           NULL AS description,
           jd.cgpa_cutoff AS eligible_min_cgpa,
           jd.deadline AS last_date,
           c.company_name AS company_name,
           NULL AS company_website,
           CASE
             WHEN a.application_id IS NULL THEN NULL
             ELSE COALESCE(a.status, 'applied')
           END AS my_status
    FROM job_drive jd
    JOIN company c ON c.company_id = jd.company_id
    JOIN student s ON s.student_id = ?
    LEFT JOIN application a
      ON a.drive_id = jd.drive_id AND a.student_id = s.student_id
    WHERE s.cgpa >= jd.cgpa_cutoff
    ORDER BY jd.drive_id DESC
  ");
  $stmt->execute([$studentUserId]);
  return $stmt->fetchAll();
}

function repo_apply_to_drive(int $driveId, int $studentUserId): void {
  // application(application_id PK, student_id, drive_id, application_date, status)
  $stmt = db()->prepare("INSERT INTO application (student_id, drive_id, application_date, status)
                         VALUES (?, ?, CURDATE(), 'applied')");
  $stmt->execute([$studentUserId, $driveId]);
}

function repo_list_student_applications(int $studentUserId): array {
  $stmt = db()->prepare("
    SELECT a.application_id AS id,
           COALESCE(a.status, 'applied') AS status,
           a.application_date AS applied_at,
           jd.role AS title,
           jd.deadline AS last_date,
           c.company_name AS company_name
    FROM application a
    JOIN job_drive jd ON jd.drive_id = a.drive_id
    JOIN company c ON c.company_id = jd.company_id
    WHERE a.student_id = ?
    ORDER BY a.application_id DESC
  ");
  $stmt->execute([$studentUserId]);
  return $stmt->fetchAll();
}

function repo_list_drives_for_company(int $companyId): array {
  $stmt = db()->prepare('SELECT drive_id AS id,
                                role AS title,
                                NULL AS description,
                                cgpa_cutoff AS eligible_min_cgpa,
                                deadline AS last_date,
                                drive_date AS created_at
                         FROM job_drive
                         WHERE company_id = ?
                         ORDER BY drive_id DESC');
  $stmt->execute([$companyId]);
  return $stmt->fetchAll();
}

function repo_create_drive(int $companyId, string $title, ?string $desc, float $eligibleMinCgpa, string $lastDate): int {
  throw new RuntimeException('Use repo_create_drive_v2 (with drive_id) for your schema.');
}

function repo_create_drive_v2(
  int $driveId,
  int $companyId,
  ?int $adminId,
  ?string $role,
  ?float $cgpaCutoff,
  ?string $deadline,
  ?string $driveDate,
  ?string $status
): void {
  $stmt = db()->prepare('INSERT INTO job_drive (drive_id, company_id, admin_id, role, cgpa_cutoff, deadline, drive_date, status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
  $stmt->execute([
    $driveId,
    $companyId,
    $adminId,
    $role,
    $cgpaCutoff,
    $deadline,
    $driveDate,
    $status,
  ]);
}

function repo_admin_stats(): array {
  $pdo = db();
  $students = (int)$pdo->query("SELECT COUNT(*) AS c FROM student")->fetch()['c'];
  $companies = (int)$pdo->query("SELECT COUNT(*) AS c FROM company")->fetch()['c'];
  $drives = (int)$pdo->query("SELECT COUNT(*) AS c FROM job_drive")->fetch()['c'];
  $apps = (int)$pdo->query("SELECT COUNT(*) AS c FROM application")->fetch()['c'];
  return compact('students', 'companies', 'drives', 'apps');
}

function repo_list_students(): array {
  $stmt = db()->query('SELECT student_id AS id, name, email, branch AS department, cgpa, graduation_year, placement_status
                       FROM student
                       ORDER BY student_id DESC');
  $rows = $stmt->fetchAll();
  return array_map(static function ($r) {
    return [
      'id' => (int)$r['id'],
      'name' => $r['name'],
      'email' => $r['email'],
      'usn' => (string)($r['id']),
      'department' => $r['department'],
      'cgpa' => $r['cgpa'],
      'graduation_year' => $r['graduation_year'] ?? null,
      'placement_status' => $r['placement_status'] ?? 'Not Placed',
    ];
  }, $rows);
}

function repo_list_applications(): array {
  $stmt = db()->query("
    SELECT a.application_id AS id,
           COALESCE(a.status, 'applied') AS status,
           a.application_date AS applied_at,
           s.name AS student_name,
           s.email AS student_email,
           jd.role AS drive_title,
           jd.deadline AS last_date,
           c.company_name AS company_name
    FROM application a
    JOIN student s ON s.student_id = a.student_id
    JOIN job_drive jd ON jd.drive_id = a.drive_id
    JOIN company c ON c.company_id = jd.company_id
    ORDER BY a.application_id DESC
  ");
  return $stmt->fetchAll();
}

function repo_update_student_placement_status(int $studentId, string $status): void {
  $allowed = ['Placed', 'Not Placed'];
  if (!in_array($status, $allowed, true)) {
    throw new InvalidArgumentException('Invalid placement status');
  }

  $stmt = db()->prepare('UPDATE student SET placement_status = ? WHERE student_id = ?');
  $stmt->execute([$status, $studentId]);
}