<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/ParentModel.php';
require_once '../../models/Student.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$parentId = $_GET['id'] ?? null;

if (!$parentId) {
    header('Location: parents.php');
    exit;
}

$parentModel = new ParentModel();
$studentModel = new Student();

$parent = $parentModel->findById($parentId);
$allStudents = $studentModel->findAll();
$linkedStudents = $parentModel->getLinkedStudents($parentId);

if (!$parent) {
    header('Location: parents.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedStudents = $_POST['students'] ?? [];
    
    // Update parent's linked students
    try {
        $pdo = $parentModel->getPdo();
        
        // Remove all existing links for this parent
        $stmt = $pdo->prepare("UPDATE students SET parent_id = NULL WHERE parent_id = ?");
        $stmt->execute([$parentId]);
        
        // Link selected students to this parent
        if (!empty($selectedStudents)) {
            $placeholders = str_repeat('?,', count($selectedStudents) - 1) . '?';
            $stmt = $pdo->prepare("UPDATE students SET parent_id = ? WHERE id IN ($placeholders)");
            $params = array_merge([$parentId], $selectedStudents);
            $stmt->execute($params);
        }
        
        $message = 'Students linked successfully!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = 'Error linking students: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Refresh linked students after potential update
$linkedStudents = $parentModel->getLinkedStudents($parentId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Link Students</title>
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

  <div class="container" style="max-width: 600px; margin: 5rem auto; padding: 2rem;">
    <div class="section-header" style="margin-bottom: 3rem;">
      <div class="section-badge">
        <div class="badge-dot"></div>
        <span class="badge-text">Administration</span>
      </div>
      <h1 class="section-title">
        Link Students to
        <span class="gradient"><?= htmlspecialchars($parent['first_name'] . ' ' . $parent['last_name']) ?></span>
      </h1>
      <p class="section-description">
        Select students to link to this parent
      </p>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; <?= $messageType === 'error' ? 'background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5;' : 'background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #86efac;' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <form method="POST">
        <?php foreach ($allStudents as $student): ?>
        <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 0.75rem; border: 1px solid var(--color-border); border-radius: 8px;">
          <input 
            type="checkbox" 
            name="students[]" 
            value="<?= $student['id'] ?>"
            <?= in_array($student['id'], array_column($linkedStudents, 'id')) ? 'checked' : '' ?>
            style="width: 18px; height: 18px;"
          />
          <div>
            <div style="font-weight: 500; color: white;"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></div>
            <div style="font-size: 0.875rem; color: var(--color-muted);">Grade <?= htmlspecialchars($student['grade']) ?></div>
          </div>
        </label>
        <?php endforeach; ?>

        <div style="margin-top: 20px;">
          <button type="submit" style="width: 100%; padding: 14px; background: var(--color-primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
            Save Links
          </button>
        </div>
      </form>
    </div>
  </div>

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
</body>
</html>


