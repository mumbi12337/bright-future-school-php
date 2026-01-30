<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Mark.php';
require_once '../../models/Student.php';
require_once '../../models/Exam.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isTeacher())) {
    header('Location: ../../login.php');
    exit;
}

$markId = $_GET['id'] ?? null;

if (!$markId) {
    header('Location: marks.php');
    exit;
}

$markModel = new Mark();
$mark = $markModel->findById($markId);

if (!$mark) {
    header('Location: marks.php');
    exit;
}

// Get related data
$studentModel = new Student();
$examModel = new Exam();

$student = $studentModel->findById($mark['student_id']);
$exam = $examModel->findById($mark['exam_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Mark - Bright Future School</title>
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

  <!-- VIEW MARK PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          View
          <span class="gradient">Mark</span>
        </h1>
        <p class="section-description">
          Detailed view of student mark
        </p>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <a href="marks.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none;">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          ← Back to Marks
        </a>
        
        <div style="display: flex; gap: 1rem;">
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add New Mark
          </a>
        </div>
      </div>

      <div class="grid-2">
        <!-- Mark Details -->
        <div class="card">
          <h3 style="color: white; margin: 0 0 1.5rem 0; font-size: 1.5rem;">Mark Details</h3>
          
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
              <span style="color: var(--color-muted);">Student:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'] ?? 'Unknown Student'); ?>
              </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
              <span style="color: var(--color-muted);">Exam:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($exam['title'] ?? 'Unknown Exam'); ?>
              </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
              <span style="color: var(--color-muted);">Subject:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($exam['subject'] ?? 'Unknown Subject'); ?>
              </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
              <span style="color: var(--color-muted);">Score:</span>
              <span style="color: var(--color-accent); font-weight: 600; font-size: 1.25rem;">
                <?php echo htmlspecialchars($mark['score']); ?>%
              </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
              <span style="color: var(--color-muted);">Grade:</span>
              <span style="color: var(--color-accent); font-weight: 600; font-size: 1.25rem;">
                <?php echo htmlspecialchars($mark['grade']); ?>
              </span>
            </div>
            
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 12px;">
              <span style="color: var(--color-muted);">Date Recorded:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo date('F j, Y', strtotime($mark['created_at'])); ?>
              </span>
            </div>
          </div>
        </div>
        
        <!-- Performance Analysis -->
        <div class="card">
          <h3 style="color: white; margin: 0 0 1.5rem 0; font-size: 1.5rem;">Performance Analysis</h3>
          
          <div style="text-align: center; padding: 2rem 0;">
            <div style="width: 150px; height: 150px; margin: 0 auto 1.5rem; position: relative;">
              <svg viewBox="0 0 36 36" style="width: 100%; height: 100%;">
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                      fill="none" 
                      stroke="rgba(255, 255, 255, 0.1)" 
                      stroke-width="3">
                </path>
                <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                      fill="none" 
                      stroke="<?php 
                        $score = $mark['score'];
                        if ($score >= 95) echo '#10b981';
                        elseif ($score >= 90) echo '#3b82f6';
                        elseif ($score >= 85) echo '#8b5cf6';
                        elseif ($score >= 80) echo '#06b6d4';
                        elseif ($score >= 75) echo '#f59e0b';
                        elseif ($score >= 70) echo '#f97316';
                        else echo '#ef4444';
                      ?>" 
                      stroke-width="3" 
                      stroke-dasharray="<?php echo $mark['score']; ?>, 100">
                </path>
                <text x="18" y="20.5" text-anchor="middle" fill="white" font-size="8" font-weight="bold">
                  <?php echo $mark['score']; ?>%
                </text>
              </svg>
            </div>
            
            <h4 style="color: white; margin: 1rem 0 0.5rem 0;">
              <?php 
                $grade = $mark['grade'];
                switch($grade) {
                  case 'A+': echo 'Outstanding Performance'; break;
                  case 'A': echo 'Excellent Performance'; break;
                  case 'B+': echo 'Very Good Performance'; break;
                  case 'B': echo 'Good Performance'; break;
                  case 'C+': echo 'Above Satisfactory Performance'; break;
                  case 'C': echo 'Satisfactory Performance'; break;
                  case 'D': echo 'Needs Improvement'; break;
                  case 'F': echo 'Unsatisfactory Performance'; break;
                  default: echo 'Performance';
                }
              ?>
            </h4>
            <p style="color: var(--color-muted); margin: 0;">
              Grade <?php echo htmlspecialchars($mark['grade']); ?> - 
              <?php 
                $score = $mark['score'];
                if ($score >= 95) echo 'Exceptional achievement';
                elseif ($score >= 90) echo 'Outstanding achievement';
                elseif ($score >= 85) echo 'Very good work';
                elseif ($score >= 80) echo 'Well done';
                elseif ($score >= 75) echo 'Solid performance';
                elseif ($score >= 70) echo 'Good effort';
                elseif ($score >= 60) echo 'Passing grade';
                else echo 'Requires attention';
              ?>
            </p>
          </div>
          
          <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border);">
            <h4 style="color: white; margin: 0 0 1rem 0;">Actions</h4>
            <div style="display: flex; gap: 1rem;">
              <button onclick="window.location.href='marks.php'" class="btn btn-secondary" style="flex: 1; text-align: center;">
                Back to List
              </button>
              <button onclick="printReport()" class="btn btn-primary" style="flex: 1; text-align: center;">
                Print Report
              </button>
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

  <script src="../../public/js/main.js"></script>
  <script>
    function printReport() {
      window.print();
    }
    
    // Show notification on load
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(() => {
        showNotification('Mark details loaded successfully', 'success');
      }, 500);
    });
    
    // Show notification function
    function showNotification(message, type) {
      // Remove any existing notification
      const existingNotification = document.getElementById('notification-toast');
      if (existingNotification) {
        existingNotification.remove();
      }
      
      // Create notification element
      const notification = document.createElement('div');
      notification.id = 'notification-toast';
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        transform: translateX(120%);
        transition: transform 0.3s ease-in-out;
        background-color: ${type === 'success' ? '#10b981' : '#ef4444'};
      `;
      notification.textContent = message;
      
      document.body.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 100);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.style.transform = 'translateX(120%)';
          setTimeout(() => {
            if (notification.parentNode) {
              notification.remove();
            }
          }, 300);
        }
      }, 5000);
    }
  </script>
</body>
</html>