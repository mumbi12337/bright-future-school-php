<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/ParentModel.php';
require_once '../../models/Student.php';
require_once '../../models/Grade.php'; // Added to get grade information

$auth = new Auth();
$auth->requireRole('ADMIN');

$currentUser = $auth->getCurrentUser();

$parentId = $_GET['id'] ?? null;

if (!$parentId) {
    header('Location: parent.php'); // Changed to PHP file
    exit;
}

$parentModel = new ParentModel();
$parent = $parentModel->findById($parentId);

if (!$parent) {
    header('Location: parent.php'); // Changed to PHP file
    exit;
}

// Get linked students
$studentModel = new Student();
$linkedStudents = $studentModel->getByParentId($parentId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>View Parent - Bright Future School</title>
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

  <!-- PARENT DETAILS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Parent
          <span class="gradient">Details</span>
        </h1>
        <p class="section-description">
          View and manage parent account information
        </p>
      </div>

      <!-- Header -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
          <h1 style="color: var(--color-primary); font-size: 2rem; margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']); ?></h1>
          <p style="color: var(--color-text); margin: 0;">Parent Account</p>
        </div>

        <div style="display:flex;gap:10px;">
          <a href="link.php?id=<?php echo $parent['id']; ?>" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            Link Student
          </a>
          <a href="parent.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
          </a>
        </div>
      </div>

      <div class="grid-2">

        <!-- Parent Info -->
        <div class="card" style="padding: 1.5rem;">
          <h3 style="color: white; margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600;">Parent Information</h3>
          <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text);">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span style="color: var(--color-text); font-size: 0.875rem;"><?php echo htmlspecialchars($parent['email']); ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text);">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <span style="color: var(--color-text); font-size: 0.875rem;"><?php echo htmlspecialchars($parent['phone']); ?></span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--color-text);">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
              <span style="color: var(--color-text); font-size: 0.875rem;"><?php echo htmlspecialchars($parent['address'] ?? 'N/A'); ?></span>
            </div>
          </div>

          <div style="border-top: 1px solid var(--color-border); padding-top: 1rem; margin-top: 1rem;">
            <p style="color: var(--color-muted); font-size: 0.875rem; margin: 0.25rem 0;">Account Created: <?php echo date('j M Y', strtotime($parent['created_at'])); ?></p>
            <p style="color: var(--color-muted); font-size: 0.875rem; margin: 0.25rem 0;">Parent ID: <?php echo $parent['id']; ?></p>
          </div>
        </div>

        <!-- Linked Students -->
        <div class="card" style="padding: 1.5rem;">
          <h3 style="color: white; margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600;">Linked Students</h3>

          <?php if (!empty($linkedStudents)): ?>
            <?php foreach ($linkedStudents as $student): ?>
              <div style="border-bottom: 1px solid var(--color-border); padding-bottom: 1rem; margin-bottom: 1rem;">
                <strong style="color: white; display: block; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                <p style="color: var(--color-text); margin: 0; font-size: 0.875rem;">
                  <?php
                  // Get grade info for the student
                  if (isset($student['grade']) && $student['grade']) {
                    echo 'Grade ' . htmlspecialchars($student['grade']);
                  } else {
                    echo 'Grade Unknown';
                  }
                  ?>
                </p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color: var(--color-text); margin: 0; font-size: 0.875rem;">No students linked to this parent</p>
          <?php endif; ?>

          <div style="border-top: 1px solid var(--color-border); padding-top: 1rem; margin-top: 1rem;">
            <p style="color: var(--color-muted); font-size: 0.875rem; margin: 0;">Total Students: <?php echo count($linkedStudents); ?></p>
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


