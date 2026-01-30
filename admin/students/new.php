<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Student.php';
require_once '../../models/Grade.php';
require_once '../../models/ParentModel.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

$currentUser = $auth->getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Student - Bright Future School</title>
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
            <a href="../index.php" class="dropdown-item">Admin Dashboard</a>
            <a href="../../logout.php" class="dropdown-item">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ADD STUDENT PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Student</span>
        </h1>
        <p class="section-description">
          Register a new student in the system
        </p>
      </div>

      <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <form id="add-student-form" action="../../api/students.php?action=create" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
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
            />
          </div>
          
          <div class="form-group">
            <label for="dateOfBirth" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Date of Birth</label>
            <input 
              type="date" 
              id="dateOfBirth" 
              name="dateOfBirth" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            />
          </div>
          
          <div class="form-group">
            <label for="gradeId" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Grade</label>
            <select 
              id="gradeId" 
              name="gradeId" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Grade</option>
              <?php
              $gradeModel = new Grade();
              $grades = $gradeModel->findAll();
              foreach ($grades as $grade) {
                  echo '<option value="' . $grade['id'] . '">' . htmlspecialchars($grade['name']) . '</option>';
              }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="parentId" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Select Parent (Optional)</label>
            <select 
              id="parentId" 
              name="parentId" 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Parent (Optional)</option>
              <?php
              $parentModel = new ParentModel();
              $parents = $parentModel->findAll();
              foreach ($parents as $parent) {
                  echo '<option value="' . $parent['id'] . '">' . htmlspecialchars($parent['firstName'] . ' ' . $parent['lastName'] . ' (' . $parent['email'] . ')') . '</option>';
              }
              ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="photo" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Upload Photo (Optional)</label>
            <input 
              type="file" 
              id="photo" 
              name="photo" 
              accept="image/*" 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            />
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Save Student
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

    // Student form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('add-student-form');
      
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving Student...';
        submitBtn.disabled = true;
        
        // Prepare form data
        const formData = new FormData(form);
        
        // Prepare data for API
        const formDataObj = {};
        for (let [key, value] of formData.entries()) {
          // Map form field names to API field names
          let fieldName = key;
                    
          // Handle specific field mappings
          switch(key) {
            case 'firstName':
              fieldName = 'first_name';
              break;
            case 'lastName':
              fieldName = 'last_name';
              break;
            case 'dateOfBirth':
              fieldName = 'date_of_birth';
              break;
            case 'gradeId':
              // Get the selected grade name from the dropdown
              const gradeSelect = document.getElementById('gradeId');
              const selectedOption = gradeSelect.options[gradeSelect.selectedIndex];
              fieldName = 'grade';
              value = selectedOption.text; // Use the text (grade name)
              break;
            case 'parentId':
              fieldName = 'parent_id';
              // Convert empty string to null
              if (value === '') value = null;
              break;
            case 'photo':
              fieldName = 'photo_url';
              // Handle file upload - for now just set to null
              value = null;
              break;
          }
                    
          formDataObj[fieldName] = value;
        }
        
        fetch('../../api/students.php?action=create', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(formDataObj)
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showNotification('Student added successfully!', 'success');
            
            // Reset form after successful submission
            form.reset();
          } else {
            showNotification(data.message || 'Error adding student', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotification('Error adding student: ' + error.message, 'error');
        })
        .finally(() => {
          // Reset button
          submitBtn.textContent = originalText;
          submitBtn.disabled = false;
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


