<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/ParentModel.php';
require_once '../../models/User.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
        $message = 'First name, last name, email, and phone number are required.';
        $messageType = 'error';
    } else {
        $parentModel = new ParentModel();
        $userModel = new User();
        
        // Check if parent with this email already exists
        $existingParent = $parentModel->findByEmail($email);
        if ($existingParent) {
            $message = 'A parent with this email already exists.';
            $messageType = 'error';
        } else {
            // Check if user with this email already exists
            $existingUser = $userModel->findBy(['email' => $email]);
            if ($existingUser) {
                $message = 'A user with this email already exists.';
                $messageType = 'error';
            } else {
                // Create parent record
                $parentResult = $parentModel->createParent($firstName, $lastName, $email, $phone, $address);
                
                if ($parentResult) {
                    // Create user account for the parent
                    $hashedPassword = password_hash('parent123', PASSWORD_DEFAULT);
                    $userResult = $userModel->create([
                        'email' => $email,
                        'password' => $hashedPassword,
                        'role' => 'PARENT'
                    ]);
                    
                    if ($userResult) {
                        $message = 'Parent account created successfully!';
                        $messageType = 'success';
                        
                        // Redirect to parents list after successful creation
                        header('Location: parent.php');
                        exit;
                    } else {
                        $message = 'Failed to create user account for parent.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Failed to create parent record.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Parent - Bright Future School</title>
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

  <!-- ADD PARENT PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Parent</span>
        </h1>
        <p class="section-description">
          Create a parent account that can be linked to students later
        </p>
      </div>

      <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; <?= $messageType === 'error' ? 'background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5;' : 'background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #86efac;' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form id="add-parent-form" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
          <div class="grid grid-2">
            <div class="form-group">
              <label for="firstName" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">First Name</label>
              <input 
                type="text" 
                id="firstName" 
                name="firstName" 
                placeholder="Enter first name" 
                required 
                class="form-control"
                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
                value="<?= isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : '' ?>"
              />
            </div>
            <div class="form-group">
              <label for="lastName" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Last Name</label>
              <input 
                type="text" 
                id="lastName" 
                name="lastName" 
                placeholder="Enter last name" 
                required 
                class="form-control"
                style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
                value="<?= isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : '' ?>"
              />
            </div>
          </div>
          
          <div class="form-group">
            <label for="email" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Email (Login Email)</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="Enter email address" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
              value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
            />
          </div>
          
          <div class="form-group">
            <label for="phone" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Phone Number</label>
            <input 
              type="tel" 
              id="phone" 
              name="phone" 
              placeholder="Enter phone number" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
              value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"
            />
          </div>
          
          <div class="form-group">
            <label for="address" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Address (Optional)</label>
            <textarea 
              id="address" 
              name="address" 
              placeholder="Enter address" 
              rows="3"
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; resize: vertical;"
            ><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : '' ?></textarea>
          </div>
          
          <div class="card" style="background: rgba(34, 197, 94, 0.1); padding: 1.5rem; border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 12px; margin: 1.5rem 0;">
            <h4 style="color: white; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px; color: #22c55e;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Account Information
            </h4>
            <ul style="color: var(--color-text); margin: 0; padding-left: 1.5rem;">
              <li style="margin-bottom: 0.5rem;">Role: <strong style="color: white;">PARENT</strong></li>
              <li style="margin-bottom: 0.5rem;">Default password: <strong style="color: white;">parent123</strong></li>
              <li>Can link multiple students</li>
            </ul>
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Create Parent Account
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
    // Parent form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('add-parent-form');
      
      form.addEventListener('submit', function(e) {
        // Form validation happens on server side, but we can still show loading indicator
        // Just let the form submit normally since we're handling it with PHP
      });
    });
  </script>
</body>
</html>


