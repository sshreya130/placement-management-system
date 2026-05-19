<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/repo.php';

$u = require_login('company');
$title = 'Company dashboard · Placement Portal';

$company = null;
$drives = [];
$err = null;

try {
  $company = repo_company_for_user($u['id']);
  if ($company) {
    $drives = repo_list_drives_for_company((int)$company['id']);
  }
} catch (Throwable $e) {
  $err = $e->getMessage();
}

ob_start();
?>
<div class="hero" style="grid-template-columns: 1fr;">
  <?php if ($err): ?>
    <div class="card">
      <div class="msg err">DB error: <?= htmlspecialchars($err) ?></div>
      <p class="muted">If your schema differs, update only <code>app/repo.php</code>.</p>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="section-title">
      <h2>Company</h2>
      <span class="badge warn">Company</span>
    </div>
    <?php if ($company): ?>
      <div style="margin-top:12px" class="msg">
        <div><strong><?= htmlspecialchars($company['name']) ?></strong></div>
        <div class="muted">
          Industry: <?= htmlspecialchars($company['industry_type'] ?? '') ?>
          <?php if (!empty($company['hr_email'])): ?> · HR: <?= htmlspecialchars($company['hr_email']) ?><?php endif; ?>
          <?php if (!empty($company['package_offered'])): ?> · Package: <?= htmlspecialchars($company['package_offered']) ?><?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div style="margin-top:12px" class="msg err">
        No company profile linked to your company user.
        <div class="muted" style="margin-top:6px">Link it in DB (or adapt queries in <code>app/repo.php</code>).</div>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Create drive</h2>
      <span class="muted">Company action</span>
    </div>
    <div id="driveMsg" class="msg" style="display:none; margin-top:12px"></div>
    <?php if ($company): ?>
      <form class="form" id="driveForm" style="margin-top:12px">
        <div class="row">
          <div>
            <label>Drive ID (primary key)</label>
            <input name="drive_id" type="number" min="1" required placeholder="e.g., 5001" />
          </div>
          <div>
            <label>Status (optional)</label>
            <input name="status" placeholder="e.g., open" />
          </div>
        </div>
        <div class="row">
          <div>
            <label>Role (optional)</label>
            <input name="role" placeholder="e.g., Graduate Engineer Trainee" />
          </div>
          <div>
            <label>Deadline (optional)</label>
            <input name="deadline" type="date" />
          </div>
        </div>
        <div class="row">
          <div>
            <label>CGPA cutoff (optional)</label>
            <input name="cgpa_cutoff" type="number" step="0.01" min="0" max="10" placeholder="e.g., 7.00" />
          </div>
          <div style="display:flex; align-items:flex-end">
            <button class="btn primary" type="submit">Add drive</button>
          </div>
        </div>
        <div class="row">
          <div>
            <label>Drive date (optional)</label>
            <input name="drive_date" type="date" />
          </div>
          <div></div>
        </div>
      </form>
    <?php else: ?>
      <p class="muted" style="margin-top:12px">Cannot create drives until your account is linked to a company.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>My drives</h2>
      <span class="muted"><?= count($drives) ?> total</span>
    </div>
    <?php if (!$drives): ?>
      <p class="muted" style="margin-top:12px">No drives created yet.</p>
    <?php else: ?>
      <div style="overflow:auto; margin-top:12px">
        <table>
          <thead>
            <tr><th>Title</th><th>Min CGPA</th><th>Last date</th><th>Created</th></tr>
          </thead>
          <tbody>
            <?php foreach ($drives as $d): ?>
              <tr>
                <td>
                  <div><strong><?= htmlspecialchars($d['title']) ?></strong></div>
                  <div class="muted" style="font-size:12px"><?= htmlspecialchars($d['description'] ?? '') ?></div>
                </td>
                <td><?= htmlspecialchars($d['eligible_min_cgpa']) ?></td>
                <td><?= htmlspecialchars($d['last_date']) ?></td>
                <td class="muted"><?= htmlspecialchars($d['created_at']) ?></td>
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
  const form = document.getElementById('driveForm');
  const msg = document.getElementById('driveMsg');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());
    payload.drive_id = Number(payload.drive_id);
    if (payload.cgpa_cutoff === "") delete payload.cgpa_cutoff;
    try {
      PlacementApp.setMsg(msg, "Adding drive…", "");
      await PlacementApp.apiPost("/api/company/create_drive.php", payload);
      PlacementApp.setMsg(msg, "Drive added. Refreshing…", "ok");
      setTimeout(() => window.location.reload(), 500);
    } catch (err) {
      PlacementApp.setMsg(msg, err.message || "Failed to add drive", "err");
    }
  });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';

