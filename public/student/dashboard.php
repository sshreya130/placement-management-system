<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/repo.php';

$u = require_login('student');
$title = 'Student dashboard · Placement Portal';

$profile = null;
$drives = [];
$apps = [];
$err = null;

try {
  $profile = repo_student_profile($u['id']);
  $drives = repo_list_drives_for_student($u['id']);
  $apps = repo_list_student_applications($u['id']);
} catch (Throwable $e) {
  $err = $e->getMessage();
}

ob_start();
?>
<div class="hero" style="grid-template-columns: 1fr;">
  <?php if ($err): ?>
    <div class="card">
      <div class="msg err">DB error: <?= htmlspecialchars($err) ?></div>
      <p class="muted">If your table/column names are different, update queries inside <code>app/repo.php</code>.</p>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="section-title">
      <h2>My profile</h2>
      <span class="badge warn">Student</span>
    </div>

    <?php if ($profile): ?>
      <div style="margin-top:12px" class="row">
        <div class="msg">
          <div><strong><?= htmlspecialchars($profile['name']) ?></strong></div>
          <div class="muted"><?= htmlspecialchars($profile['email']) ?></div>
        </div>
        <div class="msg">
          <div><strong>USN:</strong> <?= htmlspecialchars($profile['usn']) ?></div>
          <div class="muted"><?= htmlspecialchars($profile['department']) ?> · CGPA <?= htmlspecialchars($profile['cgpa']) ?> · <?= htmlspecialchars($profile['graduation_year']) ?></div>
          <div style="margin-top:10px">
  <strong>Placement Status:</strong>
  <span class="badge <?= strtolower($profile['placement_status']) === 'placed' ? 'ok' : 'bad' ?>">
    <?= htmlspecialchars($profile['placement_status']) ?>
  </span>
</div>
        </div>
      </div>
    <?php else: ?>
      <div class="msg err" style="margin-top:12px">Profile not found in DB.</div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Eligible job drives</h2>
      <span class="muted">Apply with one click</span>
    </div>
    <div id="msg" class="msg" style="display:none; margin-top:12px"></div>

    <?php if (!$drives): ?>
      <p class="muted" style="margin-top:12px">No eligible drives found.</p>
    <?php else: ?>
      <div style="overflow:auto; margin-top:12px">
        <table>
          <thead>
            <tr>
              <th>Company</th>
              <th>Drive</th>
              <th>Min CGPA</th>
              <th>Last date</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($drives as $d): ?>
            <tr>
              <td>
                <div><strong><?= htmlspecialchars($d['company_name']) ?></strong></div>
                <div class="muted" style="font-size:12px"><?= htmlspecialchars($d['company_website'] ?? '') ?></div>
              </td>
              <td>
                <div><strong><?= htmlspecialchars($d['title']) ?></strong></div>
                <div class="muted" style="font-size:12px"><?= htmlspecialchars($d['description'] ?? '') ?></div>
              </td>
              <td><?= htmlspecialchars($d['eligible_min_cgpa']) ?></td>
              <td><?= htmlspecialchars($d['last_date']) ?></td>
              <td>
                <?php if ($d['my_status']): ?>
                  <span class="badge ok"><?= htmlspecialchars($d['my_status']) ?></span>
                <?php else: ?>
                  <span class="badge">not applied</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($d['my_status']): ?>
                  <button class="btn" disabled>Applied</button>
                <?php else: ?>
                  <button class="btn primary applyBtn" data-drive-id="<?= (int)$d['id'] ?>">Apply</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>My applications</h2>
      <span class="muted">Track status</span>
    </div>
    <?php if (!$apps): ?>
      <p class="muted" style="margin-top:12px">No applications yet.</p>
    <?php else: ?>
      <div style="overflow:auto; margin-top:12px">
        <table>
          <thead>
            <tr>
              <th>Company</th>
              <th>Drive</th>
              <th>Last date</th>
              <th>Status</th>
              <th>Applied at</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($apps as $a): ?>
            <tr>
              <td><strong><?= htmlspecialchars($a['company_name']) ?></strong></td>
              <td><?= htmlspecialchars($a['title']) ?></td>
              <td><?= htmlspecialchars($a['last_date']) ?></td>
              <td><span class="badge ok"><?= htmlspecialchars($a['status']) ?></span></td>
              <td class="muted"><?= htmlspecialchars($a['applied_at']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
(() => {
  const msg = document.getElementById('msg');
  document.querySelectorAll('.applyBtn').forEach((btn) => {
    btn.addEventListener('click', async () => {
      const driveId = Number(btn.dataset.driveId);
      try {
        PlacementApp.setMsg(msg, "Applying…", "");
        await PlacementApp.apiPost("/api/student/apply.php", { drive_id: driveId });
        PlacementApp.setMsg(msg, "Applied successfully. Refreshing…", "ok");
        setTimeout(() => window.location.reload(), 400);
      } catch (e) {
        PlacementApp.setMsg(msg, e.message || "Failed to apply", "err");
      }
    });
  });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';

