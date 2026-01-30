<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Fee.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = trim($_POST['grade'] ?? '');
    $term = trim($_POST['term'] ?? '');
    $amount = trim($_POST['amount'] ?? '');

    if (empty($grade) || empty($term) || empty($amount)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } else {
        $feeModel = new Fee();
        
        // Check if fee already exists for this grade and term
        $existingFee = $feeModel->findByGradeNameAndTerm($grade, $term);
        if ($existingFee) {
            $message = 'Fee already exists for this grade and term.';
            $messageType = 'error';
        } else {
            $result = $feeModel->createByGradeName($grade, $term, floatval($amount));

            if ($result) {
                $message = 'Fee added successfully!';
                $messageType = 'success';
                // Redirect to fees list after successful creation
                header('Location: fees.php');
                exit;
            } else {
                $message = 'Failed to add fee.';
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
  <title>Add Fee - Bright Future School</title>
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

  <!-- ADD FEE PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Fee</span>
        </h1>
        <p class="section-description">
          Set tuition fees for a specific grade and term
        </p>
      </div>

      <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; <?= $messageType === 'error' ? 'background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5;' : 'background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #86efac;' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form id="new-fee-form" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
          <div class="form-group">
            <label for="grade" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Grade</label>
            <select 
              id="grade" 
              name="grade" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Grade</option>
              <option value="Grade 1" <?= (isset($_POST['grade']) && $_POST['grade'] === 'Grade 1') ? 'selected' : '' ?>>Grade 1</option>
              <option value="Grade 2" <?= (isset($_POST['grade']) && $_POST['grade'] === 'Grade 2') ? 'selected' : '' ?>>Grade 2</option>
              <option value="Grade 3" <?= (isset($_POST['grade']) && $_POST['grade'] === 'Grade 3') ? 'selected' : '' ?>>Grade 3</option>
              <option value="Grade 4" <?= (isset($_POST['grade']) && $_POST['grade'] === 'Grade 4') ? 'selected' : '' ?>>Grade 4</option>
              <option value="Grade 5" <?= (isset($_POST['grade']) && $_POST['grade'] === 'Grade 5') ? 'selected' : '' ?>>Grade 5</option>
              <option value="Grade 6" <?= (isset($_POST['grade']) && $_POST['grade'] === 'Grade 6') ? 'selected' : '' ?>>Grade 6</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="term" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Term</label>
            <select 
              id="term" 
              name="term" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Term</option>
              <option value="Term 1" <?= (isset($_POST['term']) && $_POST['term'] === 'Term 1') ? 'selected' : '' ?>>Term 1</option>
              <option value="Term 2" <?= (isset($_POST['term']) && $_POST['term'] === 'Term 2') ? 'selected' : '' ?>>Term 2</option>
              <option value="Term 3" <?= (isset($_POST['term']) && $_POST['term'] === 'Term 3') ? 'selected' : '' ?>>Term 3</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="amount" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Amount (ZMW)</label>
            <input 
              type="number" 
              id="amount" 
              name="amount" 
              placeholder="Enter amount" 
              required 
              step="0.01"
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
              value="<?= isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : '' ?>"
            />
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Save Fee
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

  <script src="/public/js/main.js"></script>
  <script>
    // Fee form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('new-fee-form');
      
      form.addEventListener('submit', function(e) {
        // Form validation happens on server side, but we can still show loading indicator
        // Just let the form submit normally since we're handling it with PHP
      });
    });
  </script>
</body>
</html>


