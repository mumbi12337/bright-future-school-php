<?php
require_once '../includes/db.php';
require_once '../includes/Auth.php';
require_once '../models/ParentModel.php';
require_once '../models/Student.php';
require_once '../models/Attendance.php';
require_once '../models/Mark.php';
require_once '../models/Fee.php';

$auth = new Auth();

// Check if user is logged in and is a parent
if (!$auth->isLoggedIn() || strtoupper($auth->getCurrentUser()['role']) !== 'PARENT') {
    header('Location: ../login.php');
    exit;
}

$user = $auth->getCurrentUser();
$parentModel = new ParentModel();
$studentModel = new Student();
$attendanceModel = new Attendance();
$markModel = new Mark();
$feeModel = new Fee();

// Get parent details
$parents = $parentModel->findBy(['email' => $user['email']]);
$parent = !empty($parents) ? $parents[0] : null;

// If no parent record exists, create one
if (!$parent) {
    // Get user details from users table
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $userStmt->execute([$user['id']]);
    $userDetails = $userStmt->fetch();
    
    if ($userDetails) {
        $parentData = [
            'first_name' => $userDetails['first_name'] ?? 'Parent',
            'last_name' => $userDetails['last_name'] ?? 'User',
            'email' => $userDetails['email'],
            'phone' => '',
            'address' => ''
        ];
        
        $parentId = $parentModel->create($parentData);
        if ($parentId) {
            $parent = $parentModel->findById($parentId);
        }
    }
    
    // If still no parent, redirect to login
    if (!$parent) {
        header('Location: ../login.php');
        exit;
    }
}

// Get linked students
$students = $studentModel->findBy(['parent_id' => $parent['id']]);

// Get attendance records for linked students
$attendances = [];
foreach ($students as $student) {
    $studentAttendances = $attendanceModel->getRecentByStudentId($student['id'], 5);
    $attendances = array_merge($attendances, $studentAttendances);
}

// Get recent grades for linked students
$grades = [];
foreach ($students as $student) {
    $studentGrades = $markModel->getByStudentId($student['id']);
    $grades = array_merge($grades, $studentGrades);
}

// Get fees for linked students
$fees = [];
foreach ($students as $student) {
    $studentFees = $feeModel->getByStudentId($student['id']);
    $fees = array_merge($fees, $studentFees);
}

// Calculate statistics
$studentCount = count($students);
$recentAttendanceRate = 0; // Placeholder calculation
$avgGrade = 'A/B'; // Placeholder calculation
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Parent Dashboard - Bright Future School</title>
  <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>


  <!-- LOGOUT BUTTON -->
  <div style="position: absolute; top: 1rem; right: 1rem; z-index: 1000;">
    <a href="../logout.php" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; border-radius: 12px; text-decoration: none; font-weight: 500; transition: all 0.2s;">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
      </svg>
      Logout
    </a>
  </div>

  <!-- PARENT DASHBOARD -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Parent Portal</span>
        </div>
        <h1 class="section-title">
          Parent
          <span class="gradient">Dashboard</span>
        </h1>
        <p class="section-description">
          Welcome back, <strong style="color: var(--color-primary); font-weight: 600;"><?= htmlspecialchars(($parent['first_name'] ?? 'Parent') . ' ' . ($parent['last_name'] ?? 'User')) ?></strong> • <?= date('l, F j, Y') ?> • Track your child's academic journey
        </p>
      </div>

      <!-- QUICK STATS -->
      <div class="grid-3" style="margin-bottom: 3rem;">
        <div class="academic-card blue">
          <div class="academic-icon" style="font-size: 3rem; margin-bottom: 1rem;">
            👨‍🎓
          </div>
          <h4>My Students</h4>
          <div class="stat-value" style="font-size: 3rem; margin: 1rem 0; color: white;"><?= $studentCount ?></div>
          <p>Children enrolled</p>
        </div>

        <div class="academic-card purple">
          <div class="academic-icon" style="font-size: 3rem; margin-bottom: 1rem;">
            ✅
          </div>
          <h4>Recent Attendance</h4>
          <div class="stat-value" style="font-size: 3rem; margin: 1rem 0; color: white;">95%</div>
          <p>Attendance rate</p>
        </div>

        <div class="academic-card green">
          <div class="academic-icon" style="font-size: 3rem; margin-bottom: 1rem;">
            📚
          </div>
          <h4>Recent Grades</h4>
          <div class="stat-value" style="font-size: 3rem; margin: 1rem 0; color: white;"><?= $avgGrade ?></div>
          <p>Average performance</p>
        </div>
      </div>

      <div class="grid-2">
        <!-- STUDENTS -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">My Students</h3>
          <div class="requirement-list">
            <?php foreach ($students as $student): ?>
            <div class="requirement-item">
              <div class="requirement-check">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <span style="color: var(--color-text); font-weight: 500; flex: 1;"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?> - Grade <?= htmlspecialchars($student['grade']) ?></span>
              <span style="color: var(--color-accent); font-weight: 500; background: rgba(96, 165, 250, 0.2); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem;">Active</span>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($students)): ?>
            <div class="requirement-item">
              <div class="requirement-check">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
              <span style="color: var(--color-text); font-weight: 500; flex: 1;">No students linked to your account</span>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ATTENDANCE -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Recent Attendance</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background-color: var(--color-surface);">
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Grade</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($attendances as $attendance): ?>
              <tr style="border-bottom: 1px solid var(--color-border);">
                <td style="padding: 0.75rem;"><?= htmlspecialchars($attendance['student_name'] ?? 'Unknown') ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($attendance['student_grade'] ?? 'Unknown') ?></td>
                <td style="padding: 0.75rem; color: <?= $attendance['status'] === 'PRESENT' ? '#10b981' : '#ef4444'; ?>; font-weight: 500;"><?= htmlspecialchars($attendance['status']) ?></td>
              </tr>
              <?php endforeach; ?>
              
              <?php if (empty($attendances)): ?>
              <tr>
                <td colspan="3" style="padding: 0.75rem; text-align: center; color: var(--color-muted);">No recent attendance records</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="grid-2" style="margin-top: 2rem;">
        <!-- GRADES -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Recent Grades</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background-color: var(--color-surface);">
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Subject</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Grade</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($grades as $grade): ?>
              <tr style="border-bottom: 1px solid var(--color-border);">
                <td style="padding: 0.75rem;"><?= htmlspecialchars($grade['student_name'] ?? 'Unknown') ?></td>
                <td style="padding: 0.75rem;"><?= htmlspecialchars($grade['subject'] ?? 'Unknown') ?></td>
                <td style="padding: 0.75rem; color: #10b981; font-weight: 500;"><?= htmlspecialchars($grade['grade']) ?></td>
              </tr>
              <?php endforeach; ?>
              
              <?php if (empty($grades)): ?>
              <tr>
                <td colspan="3" style="padding: 0.75rem; text-align: center; color: var(--color-muted);">No recent grades</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- FEES -->
        <div class="card">
          <h3 style="margin-bottom: 1.5rem;">Recent Fees</h3>
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background-color: var(--color-surface);">
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Term</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 1px solid var(--color-border);">Amount</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($fees as $fee): ?>
              <tr style="border-bottom: 1px solid var(--color-border);">
                <td style="padding: 0.75rem;">
                  <?php 
                    $studentName = isset($fee['first_name']) && isset($fee['last_name']) ? 
                      htmlspecialchars($fee['first_name'] . ' ' . $fee['last_name']) : 
                      (isset($fee['student_id']) ? 'Student ' . $fee['student_id'] : 'Unknown');
                    echo $studentName;
                  ?>
                </td>
                <td style="padding: 0.75rem;">
                  <?= htmlspecialchars($fee['term']) ?>
                </td>
                <td style="padding: 0.75rem; color: #10b981; font-weight: 500;">
                  K<?= number_format($fee['amount'], 2) ?>
                </td>
              </tr>
              <?php endforeach; ?>
              
              <?php if (empty($fees)): ?>
              <tr>
                <td colspan="3" style="padding: 0.75rem; text-align: center; color: var(--color-muted);">No fee records</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>



  <script src="/public/js/main.js"></script>
  <script>
    // Parent dashboard specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
      // Show welcome notification
      setTimeout(() => {
        showNotification('Welcome back, Parent!', 'success');
      }, 1000);
    });
  </script>
</body>
</html>