<?php
require_once __DIR__ . '/../app/auth.php';

$role = $_GET['role'] ?? 'student';
if (!in_array($role, ['student', 'admin', 'company'], true)) {
  $role = 'student';
}

$base = base_path();
$title = ucfirst($role) . ' login · Placement Portal';

ob_start();
?>
<div class="hero" style="grid-template-columns: 1fr;">
  <div class="card">
    <div class="section-title">
      <h2><?= htmlspecialchars(ucfirst($role)) ?> Login</h2>
      <a class="btn" href="<?= htmlspecialchars($base) ?>/index.php">Back</a>
    </div>

    <p class="muted" style="margin-top:6px">Login is handled by the backend and uses sessions.</p>

    <div id="msg" class="msg" style="display:none"></div>

    <form class="form" id="loginForm" style="margin-top:12px">
      <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>" />
      <div class="row">
        <div>
          <label>Email</label>
          <input name="email" type="email" autocomplete="username" placeholder="you@example.com" required />
        </div>
        <div>
          <label>Password (optional)</label>
          <input name="password" type="password" autocomplete="current-password" placeholder="Leave empty if your DB has no password" />
        </div>
      </div>
      <div class="actions">
        <button class="btn primary" type="submit">Login</button>
        <span class="muted">Role: <strong><?= htmlspecialchars($role) ?></strong></span>
      </div>
    </form>
  </div>
</div>

<script>
(() => {
  const form = document.getElementById('loginForm');
  const msg = document.getElementById('msg');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fd = new FormData(form);
    const payload = Object.fromEntries(fd.entries());
    const base = (typeof window !== "undefined" && window.__BASE__) ? window.__BASE__ : "";

    try {
      PlacementApp.setMsg(msg, "Signing in…", "");
      const out = await PlacementApp.apiPost("/api/auth/login.php", payload);
      PlacementApp.setMsg(msg, "Login success. Redirecting…", "ok");
      window.location.href = out.redirect || `${base}/index.php`;
    } catch (err) {
      PlacementApp.setMsg(msg, err.message || "Login failed", "err");
    }
  });
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';