<?php
require_once '../includes/Auth.php';
require_once '../models/Teacher.php';
require_once '../models/Student.php';
require_once '../models/Attendance.php';
require_once '../models/Mark.php';

$auth = new Auth();
$auth->requireRole('TEACHER');

$teacherModel = new Teacher($pdo);
$studentModel = new Student($pdo);
$attendanceModel = new Attendance($pdo);
$markModel = new Mark($pdo);

// Get teacher information
$currentUser = $auth->getCurrentUser();
$teacher = $teacherModel->findByEmail($currentUser['email']);

// If no teacher record exists, create one
if (!$teacher) {
    $userModel = new User($pdo);
    $user = $userModel->findById($currentUser['id']);
    
    if ($user) {
        $teacherData = [
            'first_name' => $user['first_name'] ?? 'Teacher',
            'last_name' => $user['last_name'] ?? 'User',
            'email' => $user['email'],
            'phone' => '',
            'subjects' => 'General',
            'grade' => 'Grade 1'
        ];
        
        $teacherId = $teacherModel->create($teacherData);
        if ($teacherId) {
            $teacher = $teacherModel->findById($teacherId);
        }
    }
}

// Get stats for the teacher
$studentsInClass = [];
$assignedGrade = '';
if ($teacher) {
    $assignedGrade = $teacher['grade'] ?? 'N/A';
    $studentsInClass = $studentModel->getByGradeLevel($assignedGrade);
}

// Calculate stats
$totalStudents = count($studentsInClass);
$avgScore = 0;
$attendanceRate = 0;

if ($totalStudents > 0) {
    // Calculate average score for students in class
    $totalScores = 0;
    $scoreCount = 0;
    
    foreach ($studentsInClass as $student) {
        $marks = $markModel->getByStudentId($student['id']);
        foreach ($marks as $mark) {
            $totalScores += $mark['score'];
            $scoreCount++;
        }
    }
    
    $avgScore = $scoreCount > 0 ? round($totalScores / $scoreCount, 1) : 0;
    
    // Calculate attendance rate for the class
    $attendanceRecords = $attendanceModel->getByGradeLevel($assignedGrade);
    if (!empty($attendanceRecords)) {
        $presentCount = 0;
        foreach ($attendanceRecords as $record) {
            if (strtolower($record['status']) === 'present') {
                $presentCount++;
            }
        }
        $attendanceRate = round(($presentCount / count($attendanceRecords)) * 100, 1);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Profile - Bright Future School</title>
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

  <!-- PROFILE PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          My
          <span class="gradient">Profile</span>
        </h1>
        <p class="section-description">
          Personal information and teaching details
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
          <div style="text-align: center; padding: 2rem 0;">
            <div class="about-avatar" style="width: 120px; height: 120px; margin: 0 auto 1.5rem; font-size: 3rem;">
              <?php echo $teacher ? strtoupper(substr($teacher['first_name'] ?? 'T', 0, 1) . substr($teacher['last_name'] ?? 'U', 0, 1)) : 'TU'; ?>
            </div>
            <h2 style="color: white; font-size: 1.875rem; margin-bottom: 0.5rem;">
              <?php echo htmlspecialchars(($teacher['first_name'] ?? 'Teacher') . ' ' . ($teacher['last_name'] ?? 'User')); ?>
            </h2>
            <p style="color: var(--color-accent); font-size: 1.125rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 1rem;">
              <?php echo htmlspecialchars($assignedGrade); ?> Teacher
            </p>
            <p style="color: var(--color-muted); margin-bottom: 2rem;">
              <?php echo htmlspecialchars($teacher['email'] ?? 'No email'); ?>
            </p>
          </div>
          
          <div style="display: grid; gap: 1.5rem; margin-top: 2rem;">
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <span style="color: var(--color-muted);">Name:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars(($teacher['first_name'] ?? 'Teacher') . ' ' . ($teacher['last_name'] ?? 'User')); ?>
              </span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <span style="color: var(--color-muted);">Email:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($teacher['email'] ?? 'No email'); ?>
              </span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <span style="color: var(--color-muted);">Phone:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($teacher['phone'] ?? 'Not provided'); ?>
              </span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <span style="color: var(--color-muted);">Assigned Grade:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($assignedGrade); ?>
              </span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 1rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
              <span style="color: var(--color-muted);">Subjects:</span>
              <span style="color: white; font-weight: 500;">
                <?php echo htmlspecialchars($teacher['subjects'] ?? 'Not specified'); ?>
              </span>
            </div>
          </div>
        </div>
        
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Account Settings</h3>
          <div style="display: grid; gap: 1rem;">
            <a href="#" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none;">
              Change Password
            </a>
            <a href="#" class="btn btn-secondary" style="text-align: center; display: block; text-decoration: none;">
              Update Profile
            </a>
            <a href="#" class="btn btn-primary" style="text-align: center; display: block; text-decoration: none; background: linear-gradient(to right, var(--color-warning), #f59e0b);">
              Update Contact Info
            </a>
          </div>
          
          <div style="margin-top: 2rem; padding: 1.5rem; background: rgba(26, 31, 53, 0.3); border-radius: 12px;">
            <h4 style="margin-bottom: 1rem; color: white;">Teaching Statistics</h4>
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-value"><?php echo $totalStudents; ?></div>
                <div class="stat-label">Students</div>
              </div>
              <div class="stat-card">
                <div class="stat-value"><?php echo $avgScore; ?>%</div>
                <div class="stat-label">Avg. Score</div>
              </div>
              <div class="stat-card">
                <div class="stat-value"><?php echo $attendanceRate; ?>%</div>
                <div class="stat-label">Attendance</div>
              </div>
              <div class="stat-card">
                <div class="stat-value">-</div>
                <div class="stat-label">Years Exp.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div style="text-align: center; margin-top: 3rem;">
        <a href="dashboard.php" class="btn btn-secondary" style="padding: 0.75rem 2rem; text-decoration: none;">
          Back to Dashboard
        </a>
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
