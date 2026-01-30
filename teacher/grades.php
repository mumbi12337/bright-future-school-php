<?php
require_once '../includes/Auth.php';
require_once '../models/Teacher.php';
require_once '../models/Student.php';
require_once '../models/Exam.php';
require_once '../models/Mark.php';

$auth = new Auth();
$auth->requireRole('TEACHER');

$teacherModel = new Teacher($pdo);
$studentModel = new Student($pdo);
$examModel = new Exam($pdo);
$markModel = new Mark($pdo);

// Get teacher information
$currentUser = $auth->getCurrentUser();
$teacher = $teacherModel->findById($currentUser['id']);

// Get students in teacher's assigned class/grade
$studentsInClass = [];
$assignedGrade = '';
if ($teacher) {
    $assignedGrade = $teacher['grade'] ?? 'N/A';
    $studentsInClass = $studentModel->getByGradeLevel($assignedGrade);
}

// Get available exams for this grade
$exams = $examModel->getByGradeName($assignedGrade);

// Handle form submission for saving marks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marksData = $_POST['marks'] ?? [];
    $examId = $_POST['exam_id'] ?? null;
    
    if ($examId && !empty($marksData)) {
        $success = true;
        foreach ($marksData as $studentId => $subjectMarks) {
            foreach ($subjectMarks as $subject => $score) {
                if (!empty($score)) {
                    // Create or update mark record
                    $existingMark = $markModel->findByExamAndStudent($examId, $studentId);
                    
                    // Calculate grade based on score
                    $calculatedGrade = '';
                    $scoreFloat = (float)$score;
                    if ($scoreFloat >= 90) $calculatedGrade = 'A';
                    elseif ($scoreFloat >= 80) $calculatedGrade = 'B';
                    elseif ($scoreFloat >= 70) $calculatedGrade = 'C';
                    elseif ($scoreFloat >= 60) $calculatedGrade = 'D';
                    else $calculatedGrade = 'F';
                    
                    if ($existingMark) {
                        // Update existing mark
                        $markModel->update($existingMark['id'], [
                            'score' => $score,
                            'grade' => $calculatedGrade // Calculate letter grade based on score
                        ]);
                    } else {
                        // Create new mark
                        $markModel->create([
                            'exam_id' => $examId,
                            'student_id' => $studentId,
                            'score' => $score,
                            'grade' => $calculatedGrade // Calculate letter grade based on score
                        ]);
                    }
                }
            }
        }
        
        if ($success) {
            $message = "Marks saved successfully!";
            $messageType = "success";
        } else {
            $message = "Error saving marks. Please try again.";
            $messageType = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grades - <?php echo htmlspecialchars($assignedGrade); ?> - Bright Future School</title>
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

  <!-- GRADES PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          Enter
          <span class="gradient">Grades</span>
        </h1>
        <p class="section-description">
          <?php echo htmlspecialchars($assignedGrade); ?> • Enter student marks
        </p>
      </div>

      <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Dashboard
      </a>

      <?php if (isset($message)): ?>
        <div class="notification <?php echo $messageType; ?>" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 8px; <?php echo $messageType === 'success' ? 'background: var(--color-success-bg); color: var(--color-success);' : 'background: var(--color-error-bg); color: var(--color-error);'; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($exams)): ?>
        <div class="card" style="max-width: 800px; margin: 0 auto;">
          <form id="gradesForm" method="POST" action="">
            <div style="margin-bottom: 1.5rem;">
              <label for="exam_id" style="display: block; margin-bottom: 0.5rem; color: white;">Select Exam:</label>
              <select name="exam_id" id="exam_id" required style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;">
                <option value="">-- Select an Exam --</option>
                <?php foreach ($exams as $exam): ?>
                  <option value="<?php echo $exam['id']; ?>"><?php echo htmlspecialchars($exam['title'] . ' (' . $exam['subject'] . ') - ' . date('M j, Y', strtotime($exam['date']))); ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
              <thead>
                <tr style="background-color: var(--color-surface);">
                  <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
                  <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Subject</th>
                  <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Score</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($studentsInClass)): ?>
                  <?php foreach ($studentsInClass as $student): ?>
                    <tr style="border-bottom: 1px solid var(--color-border);">
                      <td style="padding: 1rem;"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                      <td style="padding: 1rem;">Mathematics</td>
                      <td style="padding: 1rem;">
                        <input type="number" name="marks[<?php echo $student['id']; ?>][mathematics]" min="0" max="100" placeholder="Enter marks" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;">
                      </td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--color-border);">
                      <td style="padding: 1rem;"></td>
                      <td style="padding: 1rem;">English</td>
                      <td style="padding: 1rem;">
                        <input type="number" name="marks[<?php echo $student['id']; ?>][english]" min="0" max="100" placeholder="Enter marks" style="width: 100%; padding: 0.75rem; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;">
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" style="padding: 1rem; text-align: center; color: var(--color-muted);">No students found in your assigned class</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>

            <div style="text-align: center; margin-top: 2rem;">
              <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                Save Marks
              </button>
            </div>
          </form>
        </div>
      <?php else: ?>
        <div class="card" style="max-width: 800px; margin: 0 auto; text-align: center; padding: 2rem;">
          <h3>No Exams Available</h3>
          <p>Please create an exam first before entering grades.</p>
          <a href="../admin/exams/new.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Create New Exam</a>
        </div>
      <?php endif; ?>
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
    // Grades form handling
    document.getElementById('gradesForm').addEventListener('submit', function(e) {
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving Marks...';
      submitBtn.disabled = true;
      
      // Allow form to submit normally since we're using server-side processing
      setTimeout(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }, 1500);
    });
  </script>
</body>
</html>
