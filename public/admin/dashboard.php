<?php
require_once __DIR__ . '/../../app/auth.php';
require_once __DIR__ . '/../../app/repo.php';

$u = require_login('admin');
$title = 'Admin dashboard · Placement Portal';

$stats = ['students' => 0, 'companies' => 0, 'drives' => 0, 'apps' => 0];
$companies = [];
$students = [];
$apps = [];
$err = null;

try {
  $stats = repo_admin_stats();
  $companies = repo_list_companies();
  $students = repo_list_students();
  $apps = repo_list_applications();
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
      <h2>Admin overview</h2>
      <span class="badge warn">Admin</span>
    </div>
    <div class="grid3" style="margin-top:12px">
      <div class="msg"><div class="muted">Students</div><div style="font-size:22px"><strong><?= (int)$stats['students'] ?></strong></div></div>
      <div class="msg"><div class="muted">Companies</div><div style="font-size:22px"><strong><?= (int)$stats['companies'] ?></strong></div></div>
      <div class="msg"><div class="muted">Drives</div><div style="font-size:22px"><strong><?= (int)$stats['drives'] ?></strong></div></div>
      <div class="msg"><div class="muted">Applications</div><div style="font-size:22px"><strong><?= (int)$stats['apps'] ?></strong></div></div>
    </div>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Create company</h2>
      <span class="muted">Insert into `company`</span>
    </div>
    <div id="companyMsg" class="msg" style="display:none; margin-top:12px"></div>
    <form class="form" id="companyForm" style="margin-top:12px">
      <div class="row">
        <div>
          <label>Company ID (primary key)</label>
          <input name="company_id" type="number" min="1" required placeholder="e.g., 101" />
        </div>
        <div>
          <label>Company name</label>
          <input name="company_name" required placeholder="e.g., Infosys" />
        </div>
      </div>
      <div class="row">
        <div>
          <label>Industry type (optional)</label>
          <input name="industry_type" placeholder="e.g., IT Services" />
        </div>
        <div>
          <label>HR email (optional)</label>
          <input name="hr_email" type="email" placeholder="hr@company.com" />
        </div>
      </div>
      <div class="row">
        <div>
          <label>Package offered (optional)</label>
          <input name="package_offered" type="number" step="0.01" min="0" placeholder="e.g., 6.50" />
        </div>
        <div style="display:flex; align-items:flex-end">
          <button class="btn primary" type="submit">Add company</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Create drive</h2>
      <span class="muted">Insert into `job_drive`</span>
    </div>
    <div id="driveMsg" class="msg" style="display:none; margin-top:12px"></div>
    <form class="form" id="driveForm" style="margin-top:12px">
      <div class="row">
        <div>
          <label>Drive ID (primary key)</label>
          <input name="drive_id" type="number" min="1" required placeholder="e.g., 5001" />
        </div>
        <div>
          <label>Company</label>
          <select name="company_id" required>
            <option value="">Select…</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= (int)$c['id'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row">
        <div>
          <label>Role (optional)</label>
          <input name="role" placeholder="e.g., Software Engineer" />
        </div>
        <div>
          <label>CGPA cutoff (optional)</label>
          <input name="cgpa_cutoff" type="number" step="0.01" min="0" max="10" placeholder="e.g., 7.00" />
        </div>
      </div>
      <div class="row">
        <div>
          <label>Deadline (optional)</label>
          <input name="deadline" type="date" />
        </div>
        <div>
          <label>Drive date (optional)</label>
          <input name="drive_date" type="date" />
        </div>
      </div>
      <div class="row">
        <div>
          <label>Status (optional)</label>
          <input name="status" placeholder="e.g., open" />
        </div>
        <div style="display:flex; align-items:flex-end">
          <button class="btn primary" type="submit">Add drive</button>
        </div>
      </div>
    </form>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Companies</h2>
      <span class="muted"><?= count($companies) ?> total</span>
    </div>
    <div style="overflow:auto; margin-top:12px">
      <table>
        <thead>
        <tr><th>Name</th><th>Industry</th><th>HR Email</th><th>Package</th></tr>
        </thead>
        <tbody>
          <?php foreach ($companies as $c): ?>
            <tr>
              <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
              <td class="muted"><?= htmlspecialchars($c['industry_type'] ?? '') ?></td>
              <td class="muted"><?= htmlspecialchars($c['hr_email'] ?? '') ?></td>
              <td><?= htmlspecialchars($c['package_offered'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Students</h2>
      <span class="muted"><?= count($students) ?> total</span>
    </div>
    <div style="overflow:auto; margin-top:12px">
      <table>
        <thead>
        <tr><th>Name</th><th>Email</th><th>USN</th><th>Dept</th><th>CGPA</th><th>Year</th><th>Status</th><th>Update</th></tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
              <td class="muted"><?= htmlspecialchars($s['email']) ?></td>
              <td><?= htmlspecialchars($s['usn']) ?></td>
              <td><?= htmlspecialchars($s['department']) ?></td>
              <td><?= htmlspecialchars($s['cgpa']) ?></td>
              <td><?= htmlspecialchars($s['graduation_year']) ?></td>
              <td>
  <span class="badge <?= strtolower($s['placement_status']) === 'placed' ? 'ok' : 'bad' ?>">
    <?= htmlspecialchars($s['placement_status']) ?>
  </span>
</td>
<td>
  <form class="statusForm" style="display:flex; gap:8px; align-items:center">
    <input type="hidden" name="student_id" value="<?= (int)$s['id'] ?>">

    <select name="placement_status">
      <option value="Not Placed" <?= $s['placement_status'] === 'Not Placed' ? 'selected' : '' ?>>Not Placed</option>
      <option value="Placed" <?= $s['placement_status'] === 'Placed' ? 'selected' : '' ?>>Placed</option>
    </select>

    <button class="btn primary" type="submit">Update</button>
  </form>
</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="section-title">
      <h2>Applications</h2>
      <span class="muted"><?= count($apps) ?> total</span>
    </div>
    <div style="overflow:auto; margin-top:12px">
      <table>
        <thead>
          <tr><th>Student</th><th>Company</th><th>Drive</th><th>Status</th><th>Applied at</th></tr>
        </thead>
        <tbody>
          <?php foreach ($apps as $a): ?>
            <tr>
              <td>
                <div><strong><?= htmlspecialchars($a['student_name']) ?></strong></div>
                <div class="muted" style="font-size:12px"><?= htmlspecialchars($a['student_email']) ?></div>
              </td>
              <td><strong><?= htmlspecialchars($a['company_name']) ?></strong></td>
              <td><?= htmlspecialchars($a['drive_title']) ?></td>
              <td><span class="badge ok"><?= htmlspecialchars($a['status']) ?></span></td>
              <td class="muted"><?= htmlspecialchars($a['applied_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(() => {
  const companyForm = document.getElementById('companyForm');
  const companyMsg = document.getElementById('companyMsg');
  const driveForm = document.getElementById('driveForm');
  const driveMsg = document.getElementById('driveMsg');

  companyForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(companyForm);
    const payload = Object.fromEntries(fd.entries());
    payload.company_id = Number(payload.company_id);
    if (payload.package_offered === "") delete payload.package_offered;
    try {
      PlacementApp.setMsg(companyMsg, "Adding company…", "");
      await PlacementApp.apiPost("/api/admin/create_company.php", payload);
      PlacementApp.setMsg(companyMsg, "Company added. Refreshing…", "ok");
      setTimeout(() => window.location.reload(), 500);
    } catch (err) {
      PlacementApp.setMsg(companyMsg, err.message || "Failed to add company", "err");
    }
  });

  driveForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(driveForm);
    const payload = Object.fromEntries(fd.entries());
    payload.drive_id = Number(payload.drive_id);
    payload.company_id = Number(payload.company_id);
    if (payload.cgpa_cutoff === "") delete payload.cgpa_cutoff;
    try {
      PlacementApp.setMsg(driveMsg, "Adding drive…", "");
      await PlacementApp.apiPost("/api/admin/create_drive.php", payload);
      PlacementApp.setMsg(driveMsg, "Drive added. Refreshing…", "ok");
      setTimeout(() => window.location.reload(), 500);
    } catch (err) {
      PlacementApp.setMsg(driveMsg, err.message || "Failed to add drive", "err");
    }
  });
  document.querySelectorAll('.statusForm').forEach((form) => {
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());
    payload.student_id = Number(payload.student_id);

    try {
      await PlacementApp.apiPost("/api/admin/update_student_status.php", payload);
      window.location.reload();
    } catch (err) {
      alert(err.message || "Failed to update student status");
    }
  });
});
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../_layout.php';

