<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Student.php';
require_once '../../models/Grade.php';
require_once '../../models/ParentModel.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

$currentUser = $auth->getCurrentUser();

$studentModel = new Student();
$students = $studentModel->findAllWithDetails();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Students - Bright Future School</title>
  <link rel="stylesheet" href="../../public/css/styles.css">
</head>

<body>
  <!-- STUDENTS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Manage
          <span class="gradient">Students</span>
        </h1>
        <p class="section-description">
          View, edit, and manage student records
        </p>
      </div>

      <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 10px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <input type="text" id="searchInput" class="form-group" placeholder="Search students..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
          </div>
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Student
          </a>
        </div>
      </div>

      <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background-color: var(--color-surface);">
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Name</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Grade</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Parent</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Contact</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Actions</th>
            </tr>
          </thead>
          <tbody id="studentsTableBody">
            <?php foreach ($students as $student): ?>
            <tr style="border-bottom: 1px solid var(--color-border);">
              <td style="padding: 1rem;">
                <strong style="color: white;"><?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?></strong><br />
                <small style="color: var(--color-muted);"><?php echo date('j F Y', strtotime($student['date_of_birth'])); ?></small>
              </td>
              <td style="padding: 1rem; color: var(--color-accent); font-weight: 500;"><?php echo htmlspecialchars($student['grade_name'] ?? 'N/A'); ?></td>
              <td style="padding: 1rem; color: var(--color-text);">
                <?php if ($student['parent_first_name']): ?>
                  <?php echo htmlspecialchars(($student['parent_first_name'] ?? '') . ' ' . ($student['parent_last_name'] ?? '')); ?>
                <?php else: ?>
                  No parent assigned
                <?php endif; ?>
              </td>
              <td style="padding: 1rem;">
                <?php if ($student['parent_email']): ?>
                  <div style="color: var(--color-muted);"><?php echo htmlspecialchars($student['parent_email'] ?? 'N/A'); ?></div>
                  <div style="color: var(--color-muted);"><?php echo htmlspecialchars($student['parent_phone'] ?? 'N/A'); ?></div>
                <?php else: ?>
                  <div style="color: var(--color-muted);">N/A</div>
                <?php endif; ?>
              </td>
              <td style="padding: 1rem;">
                <div class="actions" style="display: flex; gap: 0.75rem;">
                  <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; min-width: auto;">View</a>
                  <button class="btn btn-secondary delete-btn" data-id="<?php echo $student['id']; ?>" style="padding: 0.5rem 1rem; font-size: 0.875rem; min-width: auto; background: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: #ef4444;">
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <script>
    // Toggle dropdown menu (if dropdown exists)
    function toggleDropdown() {
      const dropdown = document.getElementById('dropdownMenu');
      if (dropdown) {
        dropdown.classList.toggle('show');
      }
    }
    
    // Close dropdown when clicking outside (if dropdown exists)
    window.onclick = function(event) {
      if (!event.target.matches('.user-btn') && !event.target.closest('.user-menu')) {
        const dropdown = document.getElementById('dropdownMenu');
        if (dropdown && dropdown.classList.contains('show')) {
          dropdown.classList.remove('show');
        }
      }
    };

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const rows = document.querySelectorAll('#studentsTableBody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // Delete functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
      button.addEventListener('click', function() {
        const studentId = this.getAttribute('data-id');
        if (confirm('Are you sure you want to delete this student?')) {
          fetch(`../../api/students.php?action=delete&id=${studentId}`, {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
          })
          .then(response => {
            if (!response.ok) {
              throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              showNotification('Student deleted successfully!', 'success');
              // Reload the page to update the table
              location.reload();
            } else {
              showNotification(data.message || 'Error deleting student', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('Error deleting student: ' + error.message, 'error');
          });
        }
      });
    });
    
    // Show notification function
    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateX(100%);
        transition: transform 0.3s ease;
      `;
      
      // Set colors based on type
      switch(type) {
        case 'success':
          notification.style.background = '#10b981';
          break;
        case 'error':
          notification.style.background = '#ef4444';
          break;
        case 'warning':
          notification.style.background = '#f59e0b';
          break;
        default:
          notification.style.background = '#3b82f6';
      }
      
      notification.textContent = message;
      document.body.appendChild(notification);
      
      // Animate in
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 10);
      
      // Remove after 3 seconds
      setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }, 3000);
    }
    
  </script>
</body>
</html>


