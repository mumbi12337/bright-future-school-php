<?php
require_once 'includes/session.php';
require_once 'models/Student.php';
require_once 'models/ParentModel.php';

// The $auth and $currentUser variables are initialized in session.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Application Form - Bright Future School</title>
  <link rel="stylesheet" href="public/css/styles.css">
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
          <button data-section="admissions" class="nav-btn active" onclick="window.location.href='index.php#admissions'">Admissions</button>
          <button data-section="academics" class="nav-btn" onclick="window.location.href='index.php#academics'">Academics</button>
          <button data-section="contact" class="nav-btn" onclick="window.location.href='index.php#contact'">Contact</button>
        </nav>

        <?php if ($currentUser): ?>
        <!-- User Menu -->
        <div class="user-menu">
          <button class="user-btn" onclick="toggleDropdown()">
            <span><?php echo htmlspecialchars($currentUser['email']); ?></span>
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
              <path d="M7 10l5 5 5-5z"/>
            </svg>
          </button>
          <div class="dropdown-menu" id="dropdownMenu">
            <?php if ($auth->isAdmin()): ?>
              <a href="admin/index.php" class="dropdown-item">Admin Dashboard</a>
            <?php elseif ($auth->isTeacher()): ?>
              <a href="teacher/dashboard.php" class="dropdown-item">Teacher Dashboard</a>
            <?php elseif ($auth->isParent()): ?>
              <a href="parent/parent.php" class="dropdown-item">Parent Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="dropdown-item">Logout</a>
          </div>
        </div>
        <?php else: ?>
        <!-- Portal Button -->
        <a href="login.php" class="portal-btn">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- APPLICATION SECTION -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Admissions</span>
        </div>
        <h1 class="section-title">
          Student
          <span class="gradient">Application Form</span>
        </h1>
        <p class="section-description">
          Begin your child's educational journey with Bright Future Primary School. Please fill out all required fields marked with *.
        </p>
      </div>

      <div class="card" style="max-width: 800px; margin: 0 auto;">
        <form id="applicationForm" class="contact-form" action="api/applications.php" method="POST">
          
          <!-- Student Info -->
          <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid var(--color-border);">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: white; margin-bottom: 1.5rem;">
              Student Information
            </h2>
            <div class="grid-2">
              <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="firstName" required placeholder="Enter first name">
              </div>
              <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="lastName" required placeholder="Enter last name">
              </div>
              <div class="form-group">
                <label>Date of Birth *</label>
                <input type="date" name="dateOfBirth" required>
              </div>
              <div class="form-group">
                <label>Grade Applying For *</label>
                <select name="gradeId" required style="appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;12&quot; height=&quot;12&quot; viewBox=&quot;0 0 12 12&quot;><path fill=&quot;%239ca3af&quot; d=&quot;M6 9L1 4h10z&quot;/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                  <option value="">Select Grade</option>
                  <?php
                  require_once 'models/Grade.php';
                  $gradeModel = new Grade($pdo);
                  $grades = $gradeModel->findAll();
                  foreach ($grades as $grade) {
                      echo '<option value="' . $grade['id'] . '">' . htmlspecialchars($grade['name']) . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Parent Info -->
          <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid var(--color-border);">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: white; margin-bottom: 1.5rem;">
              Parent/Guardian Information
            </h2>
            <div class="grid-2">
              <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="parentFirstName" required placeholder="Enter parent's first name">
              </div>
              <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="parentLastName" required placeholder="Enter parent's last name">
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="parentEmail" required placeholder="parent@example.com">
              </div>
              <div class="form-group">
                <label>Phone Number *</label>
                <input type="tel" name="parentPhone" required placeholder="+260 123 456 789">
              </div>
              <div class="form-group" style="grid-column: 1 / -1;">
                <label>Home Address</label>
                <input type="text" name="parentAddress" placeholder="Full home address">
              </div>
            </div>
          </div>

          <!-- Emergency Contact -->
          <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid var(--color-border);">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: white; margin-bottom: 1.5rem;">
              Emergency Contact
            </h2>
            <div class="grid-2">
              <div class="form-group">
                <label>Name</label>
                <input type="text" name="emergencyContactName" placeholder="Emergency contact name">
              </div>
              <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="emergencyContactPhone" placeholder="+260 123 456 789">
              </div>
            </div>
          </div>

          <!-- Additional Info -->
          <div style="margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid var(--color-border);">
            <h2 style="font-size: 1.5rem; font-weight: bold; color: white; margin-bottom: 1.5rem;">
              Additional Information
            </h2>
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
              <div class="form-group">
                <label>Previous School Attended</label>
                <input type="text" name="previousSchool" placeholder="Previous school name">
              </div>
              <div class="form-group">
                <label>Medical Conditions or Special Needs</label>
                <textarea name="medicalConditions" placeholder="Please list any medical conditions or special needs..."></textarea>
              </div>
              <div class="form-group">
                <label>Additional Notes</label>
                <textarea name="additionalNotes" placeholder="Any additional information you'd like to share..."></textarea>
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
              Submit Application
            </button>
            <a href="index.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; display: block;">
              Cancel & Return Home
            </a>
          </div>

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

  <script src="public/js/main.js"></script>
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

    // Application form handling
    document.getElementById('applicationForm').addEventListener('submit', async function(e) {
      e.preventDefault();
      
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Submitting Application...';
      submitBtn.disabled = true;
      
      try {
        // Get form data
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        // In a real application, you would send this to your server
        console.log('Application Data:', data);
        
        // Send data to server
        const response = await fetch('api/applications.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          // Show success notification
          showNotification(result.message || 'Application submitted successfully! We will contact you soon.', 'success');
          
          // Reset form
          this.reset();
        } else {
          throw new Error(result.error || result.message || 'Failed to submit application');
        }
        
      } catch (error) {
        console.error('Submission error:', error);
        showNotification(error.message || 'Error submitting application. Please try again.', 'error');
      } finally {
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
    
    // Form validation feedback
    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
      input.addEventListener('blur', function() {
        if (this.value.trim() === '' && this.hasAttribute('required')) {
          this.style.borderColor = '#ef4444';
        } else {
          this.style.borderColor = 'var(--color-border)';
        }
      });
    });
  </script>
</body>
</html>