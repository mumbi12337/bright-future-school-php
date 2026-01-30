<?php
require_once '../../includes/db.php';
require_once '../../includes/Auth.php';
require_once '../../models/Student.php';
require_once '../../models/Fee.php';
require_once '../../models/StudentFee.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !($auth->isAdmin())) {
    header('Location: ../../login.php');
    exit;
}

$studentModel = new Student();
$feeModel = new Fee();
$studentFeeModel = new StudentFee();
$students = $studentModel->getAllStudentsWithParents();

// Get fee structure information
$feeStructure = $feeModel->findAll();

// Get fee summary by grade
$feeSummary = $studentFeeModel->getFeeSummaryByGrade();

// Handle fee payment
$notification = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_fee') {
    try {
        $studentId = $_POST['student_id'];
        $term = $_POST['term'];
            
        // Debug: Log the payment attempt
        error_log("Processing fee payment for student ID: {$studentId}, term: {$term}");
            
        // Use the new synchronized fee payment system
        $result = $studentModel->processFeePayment($studentId, $term);
            
        // Debug: Log the result
        error_log("Payment result: " . print_r($result, true));
            
        $notificationType = $result['graduated'] ?? false ? 'graduation' : 'success';
        $bgColor = $notificationType === 'graduation' ? '#8b5cf6' : '#10b981';
        $notification = '<div class="notification notification-' . $notificationType . '" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: ' . $bgColor . '; color: white;">' . $result['message'] . '</div>';
            
        // Refresh student data
        $students = $studentModel->getAllStudentsWithParents();
        $feeSummary = $studentFeeModel->getFeeSummaryByGrade();
    } catch (Exception $e) {
        error_log("Fee payment error: " . $e->getMessage());
        $notification = '<div class="notification notification-error" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: #ef4444; color: white;">Error: ' . $e->getMessage() . '</div>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fee Management - Bright Future School</title>
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

  <!-- FEES PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Fee
          <span class="gradient">Management</span>
        </h1>
        <p class="section-description">
          Manage student fees, track academic progression, and view fee structure
        </p>
      </div>

      <?= $notification ?>
      
      <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <h3 style="color: white; margin-bottom: 1rem;">Fee Management System</h3>
        <p style="color: var(--color-text); margin-bottom: 1rem;">
          Students automatically advance to the next grade after paying fees for all 3 terms. 
          Each academic year consists of 3 terms, and students must complete all terms before grade promotion.
        </p>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;">
          <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <input type="text" id="search-students" placeholder="Search students..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
            <select id="filter-grade" style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 180px;">
              <option value="">All Grades</option>
              <option>Grade 1</option>
              <option>Grade 2</option>
              <option>Grade 3</option>
              <option>Grade 4</option>
              <option>Grade 5</option>
              <option>Grade 6</option>
              <option>Grade 7</option>
              <option>Grade 8</option>
              <option>Grade 9</option>
              <option>Grade 10</option>
              <option>Grade 11</option>
              <option>Grade 12</option>
            </select>
          </div>
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Manual Fee
          </a>
        </div>
      </div>

      <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background-color: var(--color-surface);">
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Grade</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Current Term</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Fee Status</th>
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Actions</th>
            </tr>
          </thead>
          <tbody id="students-tbody">
            <?php foreach ($students as $student): 
                $feeStatus = $studentModel->getFeeStatus($student['id']);
                $paidTerms = array_filter($feeStatus, function($fee) { return $fee['paid']; });
                $currentTerm = $student['current_term'] ?? 1;
                $academicYear = $student['academic_year'] ?? date('Y');
            ?>
            <tr style="border-bottom: 1px solid var(--color-border);" data-grade="<?= htmlspecialchars($student['grade']) ?>" data-name="<?= strtolower(htmlspecialchars($student['first_name'] . ' ' . $student['last_name'])) ?>">
              <td style="padding: 1rem; color: white; font-weight: 500;">
                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                <div style="font-size: 0.875rem; color: var(--color-text);">
                  Academic Year: <?= $academicYear ?>
                </div>
              </td>
              <td style="padding: 1rem; color: var(--color-text);"><?= htmlspecialchars($student['grade']) ?></td>
              <td style="padding: 1rem; color: var(--color-accent); font-weight: 500;">
                Term <?= $currentTerm ?>
                <?php 
                $hasGraduated = $studentModel->hasGraduated($student['id']);
                if ($hasGraduated): ?>
                  <div style="font-size: 0.875rem; color: #8b5cf6;">🎓 Graduated</div>
                <?php elseif (count($paidTerms) >= 3): ?>
                  <div style="font-size: 0.875rem; color: #10b981;">✓ Ready for Promotion</div>
                <?php endif; ?>
              </td>
              <td style="padding: 1rem;">
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                  <?php for ($term = 1; $term <= 3; $term++): 
                    $termFee = array_filter($feeStatus, function($f) use ($term) { return $f['term'] == $term; });
                    $termFee = reset($termFee);
                    $isPaid = $termFee && $termFee['paid'];
                  ?>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                      <span style="color: var(--color-text);">Term <?= $term ?>:</span>
                      <?php if ($isPaid): ?>
                        <span style="color: #10b981; font-weight: 500;">Paid</span>
                        <span style="color: var(--color-text); font-size: 0.75rem;">
                          (<?= date('M j', strtotime($termFee['payment_date'])) ?>)
                        </span>
                      <?php else: ?>
                        <span style="color: #f59e0b; font-weight: 500;">Pending</span>
                        <span style="color: var(--color-text);">
                          (ZMW <?= number_format($termFee['amount'] ?? 500, 2) ?>)
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endfor; ?>
                </div>
              </td>
              <td style="padding: 1rem;">
                <div class="actions" style="display: flex; flex-direction: column; gap: 0.5rem;">
                  <?php if ($hasGraduated): ?>
                    <div style="font-size: 0.875rem; color: #8b5cf6; text-align: center; padding: 0.5rem; background: rgba(139, 92, 246, 0.1); border-radius: 8px;">
                      🎓 Student has graduated
                    </div>
                  <?php else: ?>
                    <?php for ($term = 1; $term <= 3; $term++): 
                      $termFee = array_filter($feeStatus, function($f) use ($term) { return $f['term'] == $term; });
                      $termFee = reset($termFee);
                      $isPaid = $termFee && $termFee['paid'];
                      if (!$isPaid): ?>
                        <form method="POST" style="margin: 0;" onsubmit="handleFeePayment(event, <?= $student['id'] ?>, <?= $term ?>);">
                          <input type="hidden" name="action" value="pay_fee">
                          <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                          <input type="hidden" name="term" value="<?= $term ?>">
                          <button type="submit" class="btn btn-primary pay-term-btn" data-student-id="<?= $student['id'] ?>" data-term="<?= $term ?>" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%;">
                            Pay Term <?= $term ?>
                          </button>
                        </form>
                      <?php else: ?>
                        <div class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; width: 100%; cursor: not-allowed; opacity: 0.6; text-align: center;">
                          Term <?= $term ?> Paid
                        </div>
                      <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if (count($paidTerms) >= 3): ?>
                      <div style="font-size: 0.75rem; color: #10b981; text-align: center; margin-top: 0.5rem;">
                        ✓ All terms paid - Ready for promotion
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            
            <?php if (empty($students)): ?>
            <tr>
              <td colspan="5" style="padding: 1rem; text-align: center; color: var(--color-text);">No students found</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Fee Structure Section -->
      <div class="card" style="margin-top: 2rem; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h3 style="color: white; margin: 0;">Fee Structure Overview</h3>
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add New Fee
          </a>
        </div>
        
        <?php if (!empty($feeStructure)): ?>
          <div class="grid grid-3" style="gap: 1rem;">
            <?php foreach ($feeStructure as $fee): ?>
              <div class="card" style="padding: 1rem; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border);">
                <h4 style="color: white; margin: 0 0 0.5rem 0; font-size: 1.1rem;">
                  <?= htmlspecialchars($fee['grade_name'] ?? 'Grade ' . $fee['grade_id']) ?>
                </h4>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                  <span style="color: var(--color-text); font-size: 0.875rem;">Term <?= htmlspecialchars($fee['term']) ?></span>
                  <span style="color: var(--color-primary); font-weight: 600; font-size: 1.1rem;">
                    ZMW <?= number_format($fee['amount'], 2) ?>
                  </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem; color: var(--color-text);">
                  <span>Created: <?= date('M j, Y', strtotime($fee['created_at'])) ?></span>
                  <span>ID: <?= $fee['id'] ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div style="text-align: center; padding: 2rem; color: var(--color-text);">
            <p>No fee structure found. Add fees using the "Add New Fee" button above.</p>
          </div>
        <?php endif; ?>
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

  <style>
    .loading-spinner {
      display: inline-block;
      width: 12px;
      height: 12px;
      border: 2px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
      margin-right: 6px;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
  <script src="../../public/js/api.js"></script>
  <script src="../../public/js/main.js"></script>
  <script>
    // Fee management functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Search functionality
      const searchInput = document.getElementById('search-students');
      const gradeFilter = document.getElementById('filter-grade');
      const tbody = document.getElementById('students-tbody');
      const rows = tbody.querySelectorAll('tr');
      
      function filterStudents() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const gradeFilterValue = gradeFilter.value;
        
        rows.forEach(row => {
          const studentName = row.getAttribute('data-name') || '';
          const studentGrade = row.getAttribute('data-grade') || '';
          
          const matchesSearch = studentName.includes(searchTerm);
          const matchesGrade = !gradeFilterValue || studentGrade === gradeFilterValue;
          
          row.style.display = (matchesSearch && matchesGrade) ? '' : 'none';
        });
      }
      
      searchInput.addEventListener('input', filterStudents);
      gradeFilter.addEventListener('change', filterStudents);
      
      // Handle form submission with loading state
      document.querySelectorAll('form[method="POST"]').forEach(form => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton && submitButton.classList.contains('pay-term-btn')) {
          form.addEventListener('submit', function(e) {
            // Prevent default submission to show loading state first
            e.preventDefault();
                
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.textContent;
                
            // Show loading state
            button.disabled = true;
            button.innerHTML = '<span class="loading-spinner"></span> Processing...';
                
            // Submit the form after a brief delay to show the loading state
            setTimeout(() => {
              this.submit();
            }, 100);
          });
        }
      });
    });
  </script>
</body>
</html>