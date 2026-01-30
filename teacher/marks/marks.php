<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Mark.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isTeacher())) {
    header('Location: ../../login.php');
    exit;
}

$markModel = new Mark();
$mymarks = $markModel->getByTeacher($auth->getCurrentUser()['id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Marks - Bright Future School</title>
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

  <!-- MARKS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          My
          <span class="gradient">Marks</span>
        </h1>
        <p class="section-description">
          View and manage marks for your students
        </p>
      </div>

      <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 10px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <input type="text" class="form-group" placeholder="Search marks..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
          </div>
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Mark
          </a>
        </div>
      </div>

      <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background-color: var(--color-surface);">
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Exam</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Subject</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Score</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Grade</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Date</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Actions</th>
            </tr>
          </thead>
          <tbody id="marks-tbody">
            <?php foreach ($mymarks as $mark): ?>
            <tr style="border-bottom: 1px solid var(--color-border);">
              <td style="padding: 1rem; color: white; font-weight: 500;"><?= htmlspecialchars($mark['student_name'] ?? $mark['student_id']) ?></td>
              <td style="padding: 1rem; color: var(--color-text);"><?= htmlspecialchars($mark['exam_title'] ?? $mark['exam_id']) ?></td>
              <td style="padding: 1rem; color: var(--color-text);"><?= htmlspecialchars($mark['subject'] ?? '') ?></td>
              <td style="padding: 1rem; color: var(--color-accent); font-weight: 500;"><?= htmlspecialchars($mark['score']) ?>%</td>
              <td style="padding: 1rem; color: var(--color-accent); font-weight: 500;"><?= htmlspecialchars($mark['grade']) ?></td>
              <td style="padding: 1rem; color: var(--color-text);"><?= htmlspecialchars(date('M j, Y', strtotime($mark['created_at']))) ?></td>
              <td style="padding: 1rem;">
                <div class="actions" style="display: flex; gap: 0.75rem;">
                  <a href="view.php?id=<?= $mark['id'] ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; min-width: auto;">View</a>
                  <button class="btn btn-secondary delete-mark" data-id="<?= $mark['id'] ?>" style="padding: 0.5rem 1rem; font-size: 0.875rem; min-width: auto; background: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: #ef4444;">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($mymarks)): ?>
            <tr>
              <td colspan="7" style="padding: 1rem; text-align: center; color: var(--color-text);">No marks found</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
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
    // Marks management functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Teacher marks management loaded');
      
      // Add event listeners for delete buttons
      document.querySelectorAll('.delete-mark').forEach(button => {
        button.addEventListener('click', function() {
          const markId = this.getAttribute('data-id');
          if (confirm('Are you sure you want to delete this mark?')) {
            // Send AJAX request to delete the mark
            fetch('../../api/marks.php', {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({ id: markId })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showNotification('Mark deleted successfully!', 'success');
                // Remove the row from the table
                this.closest('tr').remove();
              } else {
                showNotification('Error deleting mark: ' + (data.message || 'Unknown error'), 'error');
              }
            })
            .catch(error => {
              showNotification('Error deleting mark: ' + error.message, 'error');
            });
          }
        });
      });
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