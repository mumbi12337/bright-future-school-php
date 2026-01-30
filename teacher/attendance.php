<?php
require_once '../includes/Auth.php';
require_once '../models/Student.php';
require_once '../models/Attendance.php';
require_once '../models/Teacher.php';

$auth = new Auth();
$auth->requireRole('TEACHER');

// Get current teacher
$currentUser = $auth->getCurrentUser();
$teacherModel = new Teacher();
// Look up teacher by email since Auth uses user ID from users table
$teacher = $teacherModel->findByEmail($currentUser['email']);

// If teacher not found by email, try to find by user ID (fallback)
if (!$teacher) {
    $teacher = $teacherModel->findById($currentUser['id']);
}

// Debug: Log teacher lookup
error_log("Teacher lookup - Email: " . ($currentUser['email'] ?? 'N/A') . ", User ID: " . ($currentUser['id'] ?? 'N/A') . ", Found: " . ($teacher ? 'Yes' : 'No'));

// Get students for the teacher's grade (or all students if no grade assigned)
$studentModel = new Student();
if (!empty($teacher['grade'])) {
    // Get students in the teacher's grade
    $students = $studentModel->findBy(['grade' => $teacher['grade']]);
} else {
    // Get all students
    $students = $studentModel->findAll();
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceModel = new Attendance();
    $date = $_POST['date'] ?? date('Y-m-d');
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'student_') === 0) {
            $studentId = substr($key, 8); // Remove 'student_' prefix
            
            // Validate status
            if (in_array($value, ['present', 'absent', 'late'])) {
                try {
                    // Debug: Log the data being processed
                    error_log("Processing attendance for student $studentId with status $value on date $date");
                    error_log("Teacher ID being used: " . ($currentUser['id'] ?? 'N/A'));
                    error_log("Teacher email: " . ($currentUser['email'] ?? 'N/A'));
                    error_log("Teacher found: " . ($teacher ? 'Yes (ID: ' . $teacher['id'] . ')' : 'No'));
                    
                    // Use the actual teacher ID from the teachers table, not the users table
                    $teacherIdToUse = $teacher['id'] ?? $currentUser['id'];
                    
                    $result = $attendanceModel->markAttendance(
                        $studentId,
                        $value,
                        $date . ' ' . date('H:i:s'),
                        $teacherIdToUse
                    );
                    
                    if ($result) {
                        $success_count++;
                        error_log("Successfully saved attendance for student $studentId");
                    } else {
                        $error_count++;
                        error_log("Failed to save attendance for student $studentId");
                    }
                } catch (Exception $e) {
                    $error_count++;
                    error_log("Exception saving attendance for student $studentId: " . $e->getMessage());
                }
            } else {
                error_log("Invalid status value for student $studentId: $value");
            }
        }
    }
    
    if ($success_count > 0) {
        $success_message = "Attendance marked for $success_count student(s)";
    }
    
    if ($error_count > 0) {
        $error_message = "Failed to mark attendance for $error_count student(s)";
    }
}

// Use today's date as default
$currentDate = $_POST['date'] ?? date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance - <?php echo !empty($teacher['grade']) ? htmlspecialchars($teacher['grade']) : 'All Grades'; ?> - Bright Future School</title>
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

  <!-- ATTENDANCE PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          Mark
          <span class="gradient">Attendance</span>
        </h1>
        <p class="section-description">
          <?php echo !empty($teacher['grade']) ? htmlspecialchars($teacher['grade']) : 'All Grades'; ?> • Mark attendance for <?php echo $currentDate; ?>
        </p>
      </div>

      <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Dashboard
      </a>

      <?php if (!empty($success_message)): ?>
        <div class="notification notification-success" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: #10b981; color: white;">
          <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($error_message)): ?>
        <div class="notification notification-error" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: #ef4444; color: white;">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <div class="card" style="max-width: 800px; margin: 0 auto;">
        <form id="attendanceForm" method="POST" action="">
          <div style="margin-bottom: 1.5rem;">
            <label for="date" style="display: block; margin-bottom: 0.5rem; color: white;">Select Date:</label>
            <input type="date" id="date" name="date" value="<?php echo $currentDate; ?>" style="width: 100%; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--color-border); background-color: var(--color-surface); color: white;">
          </div>

          <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
            <thead>
              <tr style="background-color: var(--color-surface);">
                <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($students as $student): ?>
              <tr style="border-bottom: 1px solid var(--color-border);">
                <td style="padding: 1rem;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                <td style="padding: 1rem;">
                  <label style="margin-right: 1.5rem; cursor: pointer;">
                    <input type="radio" name="student_<?php echo $student['id']; ?>" value="present" style="margin-right: 0.5rem;" required>
                    Present
                  </label>
                  <label style="margin-right: 1.5rem; cursor: pointer;">
                    <input type="radio" name="student_<?php echo $student['id']; ?>" value="absent" style="margin-right: 0.5rem;" required>
                    Absent
                  </label>
                  <label style="cursor: pointer;">
                    <input type="radio" name="student_<?php echo $student['id']; ?>" value="late" style="margin-right: 0.5rem;" required>
                    Late
                  </label>
                </td>
              </tr>
              <?php endforeach; ?>
              
              <?php if (empty($students)): ?>
              <tr>
                <td colspan="2" style="padding: 1rem; text-align: center; color: var(--color-muted);">
                  No students found
                </td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>

          <div style="text-align: center; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
              Save Attendance
            </button>
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

  <script src="../public/js/main.js"></script>
  <script>
    // Attendance form handling
    document.getElementById('attendanceForm').addEventListener('submit', function(e) {
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving Attendance...';
      submitBtn.disabled = true;
      
      // Allow form submission to continue
      setTimeout(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }, 2000);
    });
    
    // Auto-hide notifications after 5 seconds
    setTimeout(function() {
      const successMsg = document.querySelector('.notification-success');
      const errorMsg = document.querySelector('.notification-error');
      
      if (successMsg) successMsg.style.display = 'none';
      if (errorMsg) errorMsg.style.display = 'none';
    }, 5000);
  </script>
</body>
</html>
