<?php
require_once 'includes/Auth.php';

$auth = new Auth();

// If already logged in, redirect to appropriate dashboard
if ($auth->isLoggedIn()) {
    $auth->redirectUser();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        $result = $auth->login($email, $password);
        
        if ($result) {
            // Redirect based on role
            $auth->redirectUser();
        } else {
            $error_message = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Bright Future Primary School</title>
  <link rel="stylesheet" href="public/css/styles.css" />
</head>
<body>
  <!-- HEADER -->
  <header id="header">
    <div class="header-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo" onclick="window.location.href='index.php'">
          <div class="logo-icon">BF</div>
          <div class="logo-text">
            <h1>BRIGHT FUTURE</h1>
            <p>Primary School</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav id="nav">
          <button data-section="home" class="nav-btn" onclick="window.location.href='index.php'">Home</button>
          <button data-section="about" class="nav-btn" onclick="window.location.href='index.php#about'">About</button>
          <button data-section="admissions" class="nav-btn" onclick="window.location.href='index.php#admissions'">Admissions</button>
          <button data-section="academics" class="nav-btn" onclick="window.location.href='index.php#academics'">Academics</button>
          <button data-section="contact" class="nav-btn" onclick="window.location.href='index.php#contact'">Contact</button>
        </nav>

        <!-- Portal Button -->
        <a href="index.php" class="portal-btn">Back to Website</a>
      </div>
    </div>
  </header>

  <!-- LOGIN SECTION -->
  <section id="home" style="min-height: 100vh; display: flex; align-items: center; padding-top: 5rem;">
    <div class="container">
      <div class="grid-2" style="align-items: center; gap: 4rem;">
        
        <!-- Left side - Branding -->
        <div>
          <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
            <div class="section-badge">
              <div class="badge-dot"></div>
              <span class="badge-text">Welcome Back</span>
            </div>
            <h1 class="section-title" style="font-size: 2.5rem; margin-bottom: 1rem;">
              Sign In to Your
              <span class="gradient">Account</span>
            </h1>
            <p class="section-description" style="text-align: left; max-width: 100%;">
              Access your Bright Future School portal to manage academics, view reports, and stay connected.
            </p>
          </div>

          <div class="card">
            <h3>Portal Access</h3>
            <ul class="about-list">
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Student academic records</span></li>
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Attendance tracking</span></li>
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Parent-teacher communication</span></li>
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Fee payment system</span></li>
            </ul>
          </div>
        </div>

        <!-- Right side - Login Form -->
        <div>
          <div class="card" style="padding: 2rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
              <div class="logo-icon" style="margin: 0 auto 1rem; width: 64px; height: 64px; font-size: 1.5rem;">🔒</div>
              <h2 style="color: white; margin-bottom: 0.5rem;">Sign In</h2>
              <p style="color: var(--color-muted);">Enter your credentials to continue</p>
            </div>

            <form class="contact-form" id="loginForm" method="POST" action="">
              <div class="form-group">
                <input type="email" id="email" name="email" placeholder="Email Address" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
              </div>
              <div class="form-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
              </div>
              
              <div id="errorMessage" style="color: #ef4444; text-align: center; margin: 1rem 0; min-height: 1.5rem;">
                <?php if (!empty($error_message)): ?>
                  <?php echo htmlspecialchars($error_message); ?>
                <?php endif; ?>
              </div>
              
              <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Sign In
              </button>
              
              <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
                <p style="color: var(--color-muted); font-size: 0.875rem;">
                  Don't have an account? 
                  <a href="#" style="color: var(--color-primary); text-decoration: none;">Contact School Admin</a>
                </p>
              </div>
            </form>
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

  <script src="public/js/api.js"></script>
  <script src="public/js/main.js"></script>
  <script>
    // Auto-hide error message after 5 seconds
    setTimeout(function() {
      const errorMsg = document.getElementById('errorMessage');
      if (errorMsg && errorMsg.textContent.trim() !== '') {
        errorMsg.textContent = '';
      }
    }, 5000);
  </script>
</body>
</html>