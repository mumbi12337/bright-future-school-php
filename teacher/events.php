<?php
require_once '../includes/Auth.php';
require_once '../models/Event.php';
require_once '../models/Teacher.php';

$auth = new Auth();
$auth->requireRole('TEACHER');

$eventModel = new Event($pdo);
$teacherModel = new Teacher($pdo);

// Get teacher information
$currentUser = $auth->getCurrentUser();
$teacher = $teacherModel->findById($currentUser['id']);

// Get upcoming events (events from today onwards)
$upcomingEvents = $eventModel->getUpcomingEvents();

// Get all events for the calendar view
$allEvents = $eventModel->findAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Events - Bright Future School</title>
  <link rel="stylesheet" href="../public/css/styles.css">
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

        <!-- Portal Button -->
        <a href="../logout.php" class="portal-btn">Logout</a>
      </div>
    </div>
  </header>

  <!-- EVENTS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          School
          <span class="gradient">Events</span>
        </h1>
        <p class="section-description">
          Read-only events list
        </p>
      </div>

      <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Dashboard
      </a>

      <div class="grid-2">
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Upcoming Events</h3>
          
          <?php if (!empty($upcomingEvents)): ?>
            <?php foreach ($upcomingEvents as $index => $event): ?>
              <div class="step-card" style="margin-bottom: 1.5rem;">
                <div class="step-number"><?php printf("%02d", $index + 1); ?></div>
                <div class="step-content">
                  <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                  <p style="color: var(--color-muted);">
                    <?php echo date('l, j F Y', strtotime($event['date'])); ?>
                  </p>
                  <p style="margin-top: 0.75rem; color: var(--color-text);">
                    <?php echo htmlspecialchars($event['description'] ?: 'No description provided'); ?>
                  </p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="step-card" style="margin-bottom: 1.5rem;">
              <div class="step-content">
                <h4>No upcoming events</h4>
                <p style="color: var(--color-muted);">There are no upcoming events scheduled.</p>
              </div>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Event Details</h3>
          <div style="display: grid; gap: 1rem;">
            <div style="padding: 1.5rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <h4 style="margin-bottom: 1rem; color: white;">Event Guidelines</h4>
              <ul class="about-list">
                <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Check event details regularly</span></li>
                <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Inform parents about upcoming events</span></li>
                <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Prepare students for event participation</span></li>
                <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Maintain event attendance records</span></li>
              </ul>
            </div>
            
            <div style="padding: 1.5rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <h4 style="margin-bottom: 1rem; color: white;">Quick Actions</h4>
              <div style="display: grid; gap: 1rem;">
                <button class="btn btn-primary" style="text-align: center; display: block; text-decoration: none;" onclick="window.print()">
                  View Calendar
                </button>
                <button class="btn btn-secondary" style="text-align: center; display: block; text-decoration: none;" onclick="window.print()">
                  Print Schedule
                </button>
                <button class="btn btn-primary" style="text-align: center; display: block; text-decoration: none; background: linear-gradient(to right, var(--color-warning), #f59e0b);">
                  Send Reminders
                </button>
              </div>
            </div>
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

  <script src="../public/js/main.js"></script>
</body>
</html>
