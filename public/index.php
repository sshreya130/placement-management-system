<?php
require_once __DIR__ . '/../app/auth.php';

$u = current_user();
$base = base_path();
$title = 'Home · Placement Portal';

ob_start();
?>
<div class="landing-page">

<div class="decor decor-left">💼</div>
<div class="decor decor-card">🪪</div>
<div class="decor decor-chart">📈</div>
<div class="decor decor-right">✈️</div>

<section class="login-card-home">
  <div class="login-main-icon">🎓</div>

  <h1>Welcome Back!</h1>
  <p class="login-subtitle">Sign in to continue to your account</p>

  <?php if ($u): ?>
    <div class="actions center-actions">
      <?php if ($u['role'] === 'student'): ?>
        <a class="btn primary" href="<?= htmlspecialchars($base) ?>/student/dashboard.php">Go to student dashboard</a>
      <?php elseif ($u['role'] === 'admin'): ?>
        <a class="btn primary" href="<?= htmlspecialchars($base) ?>/admin/dashboard.php">Go to admin dashboard</a>
      <?php elseif ($u['role'] === 'company'): ?>
        <a class="btn primary" href="<?= htmlspecialchars($base) ?>/company/dashboard.php">Go to company dashboard</a>
      <?php endif; ?>
      <a class="btn" href="<?= htmlspecialchars($base) ?>/logout.php">Logout</a>
    </div>
  <?php else: ?>

    <form id="roleForm" class="home-role-form">
      <div class="divider-title">
        <span></span>
        <p>Choose your role</p>
        <span></span>
      </div>

      <label class="home-role-option active-role">
        <span class="role-left">
          <span class="role-icon student-icon">👤</span>
          <b>Student</b>
        </span>
        <input type="radio" name="role" value="student" checked>
      </label>

      <label class="home-role-option">
        <span class="role-left">
          <span class="role-icon admin-icon">🛡️</span>
          <b>Admin</b>
        </span>
        <input type="radio" name="role" value="admin">
      </label>

      <label class="home-role-option">
        <span class="role-left">
          <span class="role-icon company-icon">🏢</span>
          <b>Company</b>
        </span>
        <input type="radio" name="role" value="company">
      </label>

      <button class="home-continue-btn" type="submit">Continue</button>

      <p class="login-note">🛡️ Login is email-only based on your existing database.</p>
    </form>

  <?php endif; ?>
</section>

<button class="how-work-btn" onclick="toggleHowItWorks()">
  <span>ℹ️</span> How it Works <span>›</span>
</button>

<section id="howItWorks" class="how-work-panel hidden">
  <h2>How it works</h2>
  <p><b>Frontend:</b> HTML + CSS + basic JS</p>
  <p><b>Backend:</b> PHP endpoints + sessions</p>
  <p><b>Database:</b> MySQL tables + queries</p>
  <p>Everything runs on localhost using XAMPP/WAMP.</p>
</section>

</div>

<section class="home-features">
<div class="feature-item">
  <div class="feature-icon purple">🖥️</div>
  <div>
    <h3>Role-based Access</h3>
    <p>Secure dashboards for students, admins & companies</p>
  </div>
</div>

<div class="feature-item">
  <div class="feature-icon green">📅</div>
  <div>
    <h3>Eligible Drives</h3>
    <p>View and apply for eligible placement drives</p>
  </div>
</div>

<div class="feature-item">
  <div class="feature-icon blue">📄</div>
  <div>
    <h3>Application Tracking</h3>
    <p>Track your application status in real-time</p>
  </div>
</div>

<div class="feature-item">
  <div class="feature-icon orange">📊</div>
  <div>
    <h3>Placement Results</h3>
    <p>View test results and placement outcomes</p>
  </div>
</div>
</section>

<footer class="home-footer">
© 2026 Placement Portal. All rights reserved.
</footer>

<script>
(() => {
  const form = document.getElementById('roleForm');

  if (form) {
    form.addEventListener('change', () => {
      document.querySelectorAll('.home-role-option').forEach(label => {
        label.classList.remove('active-role');
      });

      const checked = form.querySelector('input[name="role"]:checked');
      if (checked) {
        checked.closest('.home-role-option').classList.add('active-role');
      }
    });

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const checked = form.querySelector('input[name="role"]:checked');
      const role = checked ? checked.value : 'student';

      const base = (typeof window !== "undefined" && window.__BASE__) ? window.__BASE__ : "";
      window.location.href = `${base}/login.php?role=${encodeURIComponent(role)}`;
    });
  }

  window.toggleHowItWorks = function () {
    document.getElementById("howItWorks").classList.toggle("hidden");
  };
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/_layout.php';

