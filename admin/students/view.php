<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Student.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

$currentUser = $auth->getCurrentUser();

$studentId = $_GET['id'] ?? null;

if (!$studentId) {
    header('Location: student.php');
    exit;
}

$studentModel = new Student();
$student = $studentModel->getStudentWithParent($studentId);

if (!$student) {
    header('Location: student.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Profile - Bright Future School</title>
  <link rel="stylesheet" href="../../public/css/styles.css">
</head>

<body>
  <!-- HEADER -->
  <header id="header">
    <div class="header-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo" onclick="window.location.href='../index.php'">
          <div class="logo-icon">BF</div>
          <div class="logo-text">
            <h1>BRIGHT FUTURE</h1>
            <p>Primary School</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav id="nav">
          <button data-section="home" class="nav-btn" onclick="window.location.href='../index.php'">Home</button>
          <button data-section="about" class="nav-btn" onclick="window.location.href='../index.php#about'">About</button>
          <button data-section="admissions" class="nav-btn" onclick="window.location.href='../index.php#admissions'">Admissions</button>
          <button data-section="academics" class="nav-btn" onclick="window.location.href='../index.php#academics'">Academics</button>
          <button data-section="contact" class="nav-btn" onclick="window.location.href='../index.php#contact'">Contact</button>
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
            <a href="index.php" class="dropdown-item">Admin Dashboard</a>
            <a href="../logout.php" class="dropdown-item">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- STUDENT PROFILE PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Student
          <span class="gradient">Profile</span>
        </h1>
        <p class="section-description">
          Detailed information about the student
        </p>
      </div>

      <a href="student.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Students
      </a>

      <div class="card" style="margin-top: 1.5rem; overflow: hidden;">
        <div class="header" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; padding: 2rem;">
          <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>
          <p style="margin: 0; color: rgba(255, 255, 255, 0.8);">Student Profile</p>
        </div>

        <div class="section" style="padding: 2rem;">
          <div class="grid-3">
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Grade</strong>
              <p style="color: white; font-weight: 500; margin: 0;"><?php echo htmlspecialchars($student['grade'] ?? 'N/A'); ?></p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Date of Birth</strong>
              <p style="color: white; font-weight: 500; margin: 0;"><?php echo date('j F Y', strtotime($student['date_of_birth'])); ?></p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Status</strong>
              <p style="color: #22c55e; font-weight: 500; margin: 0;">Active</p>
            </div>
          </div>

          <h3 style="margin-top: 2rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Parent Information
          </h3>

          <div class="grid-3" style="margin-top: 1.5rem;">
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Name</strong>
              <p style="color: white; font-weight: 500; margin: 0;">
                <?php if ($student['parent_first_name']): ?>
                  <?php echo htmlspecialchars($student['parent_first_name'] . ' ' . $student['parent_last_name']); ?>
                <?php else: ?>
                  No parent assigned
                <?php endif; ?>
              </p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Email</strong>
              <p style="color: var(--color-primary); font-weight: 500; margin: 0;">
                <?php if ($student['parent_email']): ?>
                  <a href="mailto:<?php echo htmlspecialchars($student['parent_email']); ?>" style="color: var(--color-primary); text-decoration: none;"><?php echo htmlspecialchars($student['parent_email']); ?></a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Phone</strong>
              <p style="color: var(--color-primary); font-weight: 500; margin: 0;">
                <?php if ($student['parent_phone']): ?>
                  <a href="tel:<?php echo htmlspecialchars($student['parent_phone']); ?>" style="color: var(--color-primary); text-decoration: none;"><?php echo htmlspecialchars($student['parent_phone']); ?></a>
                <?php else: ?>
                  N/A
                <?php endif; ?>
              </p>
            </div>
          </div>

          <h3 style="margin-top: 2rem; color: var(--color-primary); display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Academic Information
          </h3>

          <div class="grid-3" style="margin-top: 1.5rem;">
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Academic Year</strong>
              <p style="color: white; font-weight: 500; margin: 0;"><?php echo htmlspecialchars($student['academic_year'] ?? date('Y')); ?></p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Current Term</strong>
              <p style="color: #f59e0b; font-weight: 500; margin: 0;">Term <?php echo htmlspecialchars($student['current_term'] ?? 1); ?></p>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
              <strong style="color: var(--color-accent); display: block; margin-bottom: 0.5rem;">Progress Status</strong>
              <p style="color: #22c55e; font-weight: 500; margin: 0;">
                <?php 
                $studentModel = new Student();
                $feeStatus = $studentModel->getFeeStatus($student['id']);
                $paidTerms = array_filter($feeStatus, function($fee) { return $fee['paid']; });
                echo count($paidTerms) . ' of 3 terms paid';
                if (count($paidTerms) >= 3) {
                  echo ' - Ready for promotion';
                }
                ?>
              </p>
            </div>
          </div>
          
          <div style="margin-top: 1.5rem; text-align: center;">
            <a href="../fees/fees.php" class="btn btn-primary" style="text-decoration: none; display: inline-block; padding: 0.75rem 1.5rem;">
              Manage Student Fees
            </a>
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


