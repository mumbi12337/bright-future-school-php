<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Grade.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name)) {
        $message = 'Grade name is required.';
        $messageType = 'error';
    } else {
        $gradeModel = new Grade();
        
        // Check if grade already exists
        $existingGrade = $gradeModel->findByName($name);
        if ($existingGrade) {
            $message = 'Grade already exists.';
            $messageType = 'error';
        } else {
            $result = $gradeModel->createGrade($name, $notes);

            if ($result) {
                $message = 'Grade added successfully!';
                $messageType = 'success';
                // Redirect to grades list after successful creation
                header('Location: grades.php');
                exit;
            } else {
                $message = 'Failed to add grade.';
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Grade - Bright Future School</title>
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

        <!-- Portal Button -->
        <a href="../../logout.php" class="portal-btn">Logout</a>
      </div>
    </div>
  </header>

  <!-- ADD GRADE PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Grade</span>
        </h1>
        <p class="section-description">
          Create a new grade level for the school
        </p>
      </div>

      <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; <?= $messageType === 'error' ? 'background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5;' : 'background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #86efac;' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form id="new-grade-form" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
          <div class="form-group">
            <label for="name" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Grade Name</label>
            <input 
              type="text" 
              id="name" 
              name="name" 
              placeholder="Enter grade name (e.g., Grade 1)" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
              value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>"
            />
          </div>
          
          <div class="form-group">
            <label for="notes" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Notes (Optional)</label>
            <textarea 
              id="notes" 
              name="notes" 
              placeholder="Enter any notes about this grade" 
              rows="3"
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; resize: vertical;"
            ><?= isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : '' ?></textarea>
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Save Grade
          </button>
        </form>
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

  <script src="../../public/js/main.js"></script>
  <script>
    // Grade form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('new-grade-form');
      
      form.addEventListener('submit', function(e) {
        // Form validation happens on server side, but we can still show loading indicator
        // Just let the form submit normally since we're handling it with PHP
      });
    });
  </script>
</body>
</html>


