<?php
require_once __DIR__ . '/../app/auth.php';

$u = current_user();
$title = $title ?? 'Placement Portal';

// Compute base URL so assets/links work when hosted under:
// http://localhost/<project>/public/...
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$pos = strpos($script, '/public/');
$base = $pos === false ? '' : substr($script, 0, $pos + 7); // include "/public"
$base = rtrim($base, '/');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars($base) ?>/assets/styles.css" />
  <script>window.__BASE__ = <?= json_encode($base, JSON_UNESCAPED_SLASHES) ?>;</script>
  <script defer src="<?= htmlspecialchars($base) ?>/assets/app.js"></script>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <a class="brand" href="<?= htmlspecialchars($base) ?>/index.php">
        <div class="brand-badge" aria-hidden="true"></div>
        <div>
          <div>Placement Portal</div>
          <div class="muted" style="font-size:12px">UCS-310 DBMS Project</div>
        </div>
      </a>
      <div class="nav">
        <?php if ($u): ?>
          <span class="pill"><strong><?= htmlspecialchars($u['role']) ?></strong> · <?= htmlspecialchars($u['name']) ?></span>
          <?php if ($u['role'] === 'student'): ?>
            <a class="btn" href="<?= htmlspecialchars($base) ?>/student/dashboard.php">Student</a>
          <?php elseif ($u['role'] === 'admin'): ?>
            <a class="btn" href="<?= htmlspecialchars($base) ?>/admin/dashboard.php">Admin</a>
          <?php elseif ($u['role'] === 'company'): ?>
            <a class="btn" href="<?= htmlspecialchars($base) ?>/company/dashboard.php">Company</a>
          <?php endif; ?>
          <a class="btn danger" href="<?= htmlspecialchars($base) ?>/logout.php">Logout</a>
        <?php else: ?>
          <a class="btn" href="<?= htmlspecialchars($base) ?>/login.php?role=student">Student login</a>
          <a class="btn" href="<?= htmlspecialchars($base) ?>/login.php?role=admin">Admin login</a>
          <a class="btn" href="<?= htmlspecialchars($base) ?>/login.php?role=company">Company login</a>
        <?php endif; ?>
      </div>
    </div>

    <?= $content ?? '' ?>
  </div>
</body>
</html>

