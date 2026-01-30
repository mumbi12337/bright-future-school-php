<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Exam.php';
require_once '../../models/Mark.php';
require_once '../../models/Student.php';
require_once '../../models/Grade.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

$currentUser = $auth->getCurrentUser();

$examId = $_GET['id'] ?? null;

if (!$examId) {
    header('Location: exams.php');
    exit;
}

$examModel = new Exam();
$exam = $examModel->findById($examId);

if (!$exam) {
    header('Location: exams.php');
    exit;
}

$markModel = new Mark();
$marks = $markModel->getByExamId($examId);

$studentModel = new Student();
$gradeModel = new Grade();

$totalStudents = count($marks);
$completedMarks = count(array_filter($marks, function($mark) {
    return $mark['score'] !== null;
}));
$averageScore = $totalStudents > 0 ? round(array_sum(array_column($marks, 'score')) / $totalStudents, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exam Details - Bright Future School</title>
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
            <a href="../../admin/index.php" class="dropdown-item">Admin Dashboard</a>
            <a href="../../logout.php" class="dropdown-item">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- EXAM DETAILS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Exam
          <span class="gradient">Details</span>
        </h1>
        <p class="section-description">
          View and manage exam information
        </p>
      </div>

      <a href="exams.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Exams
      </a>

      <div class="card" style="margin-top: 1.5rem; overflow: hidden;">
        <div class="header" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; padding: 2rem;">
          <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo htmlspecialchars($exam['title'] ?? 'Exam Details'); ?></h1>
          <p style="margin: 0; color: rgba(255, 255, 255, 0.8); font-size: 1.1rem;"><?php echo htmlspecialchars(($exam['subject'] ?? 'Unknown') . ' • Grade ' . ($exam['grade_name'] ?? 'Unknown')); ?></p>
          <p style="margin: 0.5rem 0 0 0; color: rgba(255, 255, 255, 0.8); font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <?php echo date('j F Y', strtotime($exam['date'])); ?>
          </p>
        </div>

        <div class="section" style="padding: 2rem;">
          <div class="grid-3" style="margin-bottom: 2rem;">
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); text-align: center;">
              <div style="font-size: 2rem; font-weight: 600; color: var(--color-primary); margin-bottom: 0.5rem;"><?php echo $totalStudents; ?></div>
              <div style="color: var(--color-text);">Total Students</div>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); text-align: center;">
              <div style="font-size: 2rem; font-weight: 600; color: #22c55e; margin-bottom: 0.5rem;"><?php echo $completedMarks; ?></div>
              <div style="color: var(--color-text);">Marks Entered</div>
            </div>
            <div class="card" style="padding: 1.5rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); text-align: center;">
              <div style="font-size: 2rem; font-weight: 600; color: #f59e0b; margin-bottom: 0.5rem;"><?php echo $averageScore; ?>%</div>
              <div style="color: var(--color-text);">Average Score</div>
            </div>
          </div>

          <h3 style="color: var(--color-primary); margin: 2rem 0 1.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Student Marks
          </h3>

          <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
              <thead>
                <tr style="background-color: var(--color-surface);">
                  <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                  <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Score</th>
                  <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Grade</th>
                  <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($marks as $mark): ?>
                  <?php
                  $student = $studentModel->findById($mark['student_id']);
                  $gradeLetter = '';
                  if ($mark['score'] >= 95) {
                      $gradeLetter = 'A+';
                  } elseif ($mark['score'] >= 90) {
                      $gradeLetter = 'A';
                  } elseif ($mark['score'] >= 85) {
                      $gradeLetter = 'B+';
                  } elseif ($mark['score'] >= 80) {
                      $gradeLetter = 'B';
                  } elseif ($mark['score'] >= 75) {
                      $gradeLetter = 'C+';
                  } elseif ($mark['score'] >= 70) {
                      $gradeLetter = 'C';
                  } elseif ($mark['score'] >= 60) {
                      $gradeLetter = 'D';
                  } else {
                      $gradeLetter = 'F';
                  }
                  
                  $status = $mark['score'] !== null ? 'Completed' : 'Pending';
                  $statusClass = $mark['score'] !== null ? 'rgba(34, 197, 94, 0.2)' : 'rgba(245, 158, 11, 0.2)';
                  $statusColor = $mark['score'] !== null ? '#22c55e' : '#f59e0b';
                  ?>
                  <tr style="border-bottom: 1px solid var(--color-border);">
                    <td style="padding: 1rem; color: white; font-weight: 500;"><?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?></td>
                    <td style="padding: 1rem; text-align: center; color: var(--color-text);"><?php echo $mark['score'] !== null ? $mark['score'] : 'N/A'; ?></td>
                    <td style="padding: 1rem; text-align: center; color: <?php echo $mark['score'] !== null ? ($mark['score'] >= 80 ? '#22c55e' : ($mark['score'] >= 70 ? '#f59e0b' : '#ef4444')) : '#9ca3af'; ?>; font-weight: 500;"><?php echo htmlspecialchars($mark['grade'] ?? $gradeLetter); ?></td>
                    <td style="padding: 1rem; text-align: center;">
                      <span class="badge" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 999px; background: <?php echo $statusClass; ?>; color: <?php echo $statusColor; ?>; border: 1px solid <?php echo $mark['score'] !== null ? 'rgba(34, 197, 94, 0.3)' : 'rgba(245, 158, 11, 0.3)'; ?>;"><?php echo $status; ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
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

    // Exam details functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Exam details loaded');
      
      // In a real implementation, we would fetch the exam details from the server
      // For now, we'll use the static content we already have
    });
  </script>
</body>
</html>


