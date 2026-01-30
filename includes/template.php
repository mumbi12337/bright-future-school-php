<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo isset($page_title) ? $page_title : 'Bright Future Primary School'; ?></title>
  
  <!-- CSS -->
  <link rel="stylesheet" href="/public/css/styles.css">
  
  <?php if (isset($extra_css)): ?>
    <?php foreach ($extra_css as $css): ?>
      <link rel="stylesheet" href="<?php echo $css; ?>">
    <?php endforeach; ?>
  <?php endif; ?>
</head>
<body>
  <!-- HEADER -->
  <header id="header">
    <div class="header-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo" onclick="scrollToSection('home')">
          <div class="logo-icon">BF</div>
          <div class="logo-text">
            <h1>BRIGHT FUTURE</h1>
            <p>Primary School</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav id="nav">
          <button data-section="home" class="nav-btn<?php echo (!isset($active_section) || $active_section === 'home') ? ' active' : ''; ?>" onclick="scrollToSection('home')">Home</button>
          <button data-section="about" class="nav-btn<?php echo (isset($active_section) && $active_section === 'about') ? ' active' : ''; ?>" onclick="scrollToSection('about')">About</button>
          <button data-section="admissions" class="nav-btn<?php echo (isset($active_section) && $active_section === 'admissions') ? ' active' : ''; ?>" onclick="scrollToSection('admissions')">Admissions</button>
          <button data-section="academics" class="nav-btn<?php echo (isset($active_section) && $active_section === 'academics') ? ' active' : ''; ?>" onclick="scrollToSection('academics')">Academics</button>
          <button data-section="contact" class="nav-btn<?php echo (isset($active_section) && $active_section === 'contact') ? ' active' : ''; ?>" onclick="scrollToSection('contact')">Contact</button>
        </nav>

        <!-- Portal Button -->
        <a href="/auth/login.php" class="portal-btn">Portal</a>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main>
    <?php echo $content ?? ''; ?>
  </main>

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
        &copy; <?php echo date('Y'); ?> Bright Future School. All rights reserved.
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

  <!-- JavaScript -->
  <script src="/public/js/api.js"></script>
  <script src="/public/js/main.js"></script>
  
  <?php if (isset($extra_js)): ?>
    <?php foreach ($extra_js as $js): ?>
      <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
  <?php endif; ?>
  
  <?php if (isset($inline_js)): ?>
    <script>
      <?php echo $inline_js; ?>
    </script>
  <?php endif; ?>
</body>
</html>