<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Teacher.php';
require_once '../../models/Grade.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

$currentUser = $auth->getCurrentUser();

$teacherId = $_GET['id'] ?? null;

if (!$teacherId) {
    header('Location: teacher.php');
    exit;
}

$teacherModel = new Teacher();
$teacher = $teacherModel->getTeacherWithSubjects($teacherId);

if (!$teacher) {
    header('Location: teacher.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher Profile - Bright Future School</title>
  <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
  <!-- HEADER -->
  <header id="header">
    <div class="header-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo" onclick="window.location.href='../../index.php'">
          <div class="logo-icon">BF</div>
          <div class="logo-text">
            <h1>BRIGHT FUTURE</h1>
            <p>Primary School</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav id="nav">
          <button data-section="home" class="nav-btn" onclick="window.location.href='../../index.php'">Home</button>
          <button data-section="about" class="nav-btn" onclick="window.location.href='../../index.php#about'">About</button>
          <button data-section="admissions" class="nav-btn" onclick="window.location.href='../../index.php#admissions'">Admissions</button>
          <button data-section="academics" class="nav-btn" onclick="window.location.href='../../index.php#academics'">Academics</button>
          <button data-section="contact" class="nav-btn" onclick="window.location.href='../../index.php#contact'">Contact</button>
        </nav>

        <!-- User Menu -->
        <div class="user-menu">
          <button class="user-btn" onclick="toggleDropdown()">
            <span><?php echo htmlspecialchars($currentUser['email']); ?></span>
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
              <path d="M7 10l5 5 5-5z"/>
            </svg>
          </button>
          <div class="dropdown-menu" id="dropdownMenu">
            <a href="../index.php" class="dropdown-item">Admin Dashboard</a>
            <a href="../../logout.php" class="dropdown-item">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- TEACHER DETAILS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Teacher
          <span class="gradient">Profile</span>
        </h1>
        <p class="section-description">
          View and manage teacher information
        </p>
      </div>

      <a href="teacher.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Teachers
      </a>

      <div class="card" style="margin-top: 1.5rem; overflow: hidden;">
        <div class="header" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; padding: 2rem;">
          <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']); ?></h1>
          <p style="margin: 0; color: rgba(255, 255, 255, 0.8);">Teacher Profile</p>
        </div>

        <div class="section" style="padding: 2rem;">
          <h3 style="color: var(--color-primary); margin: 2rem 0 1.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Contact Information
          </h3>

          <div class="grid-3">
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Email</strong>
              <p style="color: var(--color-text); font-weight: 500; margin: 0;"><?php echo htmlspecialchars($teacher['email']); ?></p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Phone</strong>
              <p style="color: var(--color-text); font-weight: 500; margin: 0;"><?php echo htmlspecialchars($teacher['phone']); ?></p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Status</strong>
              <p style="color: #22c55e; font-weight: 500; margin: 0;">Active</p>
            </div>
          </div>

          <h3 style="color: var(--color-primary); margin: 2rem 0 1.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Subjects & Classes
          </h3>

          <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
            <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Assigned Subjects</strong>
            <p style="color: var(--color-text); font-weight: 500; margin: 0;">
              <?php if (!empty($teacher['subjects'])): ?>
                <?php echo htmlspecialchars($teacher['subjects']); ?>
              <?php else: ?>
                No subjects assigned
              <?php endif; ?>
            </p>
          </div>

          <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); margin-top: 1.5rem;">
            <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Assigned Grade</strong>
            <p style="color: var(--color-text); font-weight: 500; margin: 0;">
              <?php if (isset($teacher['grade']) && $teacher['grade']): ?>
                <?php echo htmlspecialchars($teacher['grade']); ?>
              <?php else: ?>
                No grade assigned
              <?php endif; ?>
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer>
    <div class="footer-content">
      <div class="footer-logo">
        <div class="footer-logo-icon">BF</div>
        <div>
          <div class="footer-logo-text">BRIGHT FUTURE</div>
          <div class="footer-logo-subtext">Primary School</div>
        </div>
      </div>

      <div class="footer-copyright">
        &copy; 2026 Bright Future School. All rights reserved.
      </div>

      <div class="footer-socials">
        <a href="#" class="social-link" aria-label="Facebook">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
          </svg>
        </a>
        <a href="#" class="social-link" aria-label="Twitter">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z" />
          </svg>
        </a>
        <a href="#" class="social-link" aria-label="Instagram">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01" />
          </svg>
        </a>
        <a href="#" class="social-link" aria-label="LinkedIn">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z" />
          </svg>
        </a>
      </div>
    </div>
  </footer>

  <script>
    // Toggle dropdown menu
    function toggleDropdown() {
      const dropdown = document.getElementById('dropdownMenu');
      dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    window.onclick = function(event) {
      if (!event.target.matches('.user-btn') && !event.target.closest('.user-menu')) {
        const dropdown = document.getElementById('dropdownMenu');
        if (dropdown.classList.contains('show')) {
          dropdown.classList.remove('show');
        }
      }
    };
  </script>
</body>
</html>


