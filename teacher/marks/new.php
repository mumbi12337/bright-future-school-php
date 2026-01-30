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

$studentModel = new Student();
$examModel = new Exam();
$students = $studentModel->findAll();
$exams = $examModel->findAll();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $exam_id = trim($_POST['exam_id'] ?? '');
    $score = trim($_POST['score'] ?? '');
    $grade = trim($_POST['grade'] ?? '');

    if (empty($student_id) || empty($exam_id) || empty($score)) {
        $message = 'Student, exam, and score are required.';
        $messageType = 'error';
    } else {
        $markModel = new Mark();
        
        // Validate that the student and exam exist
        $student = $studentModel->findById($student_id);
        $exam = $examModel->findById($exam_id);
        
        if (!$student || !$exam) {
            $message = 'Invalid student or exam selected.';
            $messageType = 'error';
        } else {
            $result = $markModel->recordMark($exam_id, $student_id, floatval($score), $grade);

            if ($result) {
                $message = 'Mark added successfully!';
                $messageType = 'success';
                // Redirect to marks list after successful creation
                header('Location: marks.php');
                exit;
            } else {
                $message = 'Failed to add mark.';
                $messageType = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Mark - Bright Future School</title>
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

  <!-- ADD MARK PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Teacher Portal</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Mark</span>
        </h1>
        <p class="section-description">
          Add marks for your students
        </p>
      </div>

      <div class="card" style="padding: 2rem; max-width: 600px; margin: 0 auto;">
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>" style="margin-bottom: 1rem; padding: 0.75rem; border-radius: 8px; <?= $messageType === 'error' ? 'background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fca5a5;' : 'background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #86efac;' ?>">
          <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <form id="new-mark-form" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
          <div class="form-group">
            <label for="student_id" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Student</label>
            <select 
              id="student_id" 
              name="student_id" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Student</option>
              <?php foreach ($students as $student): ?>
              <option value="<?= $student['id'] ?>" <?= (isset($_POST['student_id']) && $_POST['student_id'] == $student['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="exam_id" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Exam</label>
            <select 
              id="exam_id" 
              name="exam_id" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Exam</option>
              <?php foreach ($exams as $exam): ?>
              <option value="<?= $exam['id'] ?>" <?= (isset($_POST['exam_id']) && $_POST['exam_id'] == $exam['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($exam['title']) ?> (<?= htmlspecialchars($exam['subject']) ?>)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="score" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Score (%)</label>
            <input 
              type="number" 
              id="score" 
              name="score" 
              placeholder="Enter score" 
              min="0" 
              max="100" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
              value="<?= isset($_POST['score']) ? htmlspecialchars($_POST['score']) : '' ?>"
            />
          </div>
          
          <div class="form-group">
            <label for="grade" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Grade</label>
            <select 
              id="grade" 
              name="grade" 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Auto Calculate</option>
              <option value="A+" <?= (isset($_POST['grade']) && $_POST['grade'] === 'A+') ? 'selected' : '' ?>>A+</option>
              <option value="A" <?= (isset($_POST['grade']) && $_POST['grade'] === 'A') ? 'selected' : '' ?>>A</option>
              <option value="B+" <?= (isset($_POST['grade']) && $_POST['grade'] === 'B+') ? 'selected' : '' ?>>B+</option>
              <option value="B" <?= (isset($_POST['grade']) && $_POST['grade'] === 'B') ? 'selected' : '' ?>>B</option>
              <option value="C+" <?= (isset($_POST['grade']) && $_POST['grade'] === 'C+') ? 'selected' : '' ?>>C+</option>
              <option value="C" <?= (isset($_POST['grade']) && $_POST['grade'] === 'C') ? 'selected' : '' ?>>C</option>
              <option value="D" <?= (isset($_POST['grade']) && $_POST['grade'] === 'D') ? 'selected' : '' ?>>D</option>
              <option value="F" <?= (isset($_POST['grade']) && $_POST['grade'] === 'F') ? 'selected' : '' ?>>F</option>
            </select>
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Save Mark
          </button>
          
          <a href="marks.php" class="btn btn-secondary" style="text-align: center; text-decoration: none; padding: 0.75rem 1.5rem; border-radius: 12px; font-weight: 600; font-size: 1rem; margin-top: 0.5rem;">
            Cancel
          </a>
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

  <script src="../../public/js/main.js"></script>
  <script>
    // Auto-grade calculator
    document.addEventListener('DOMContentLoaded', function() {
      const scoreInput = document.getElementById('score');
      const gradeSelect = document.getElementById('grade');
      
      if (scoreInput && gradeSelect) {
        scoreInput.addEventListener('input', function() {
          const score = parseFloat(this.value);
          if (!isNaN(score)) {
            let calculatedGrade;
            if (score >= 95) calculatedGrade = 'A+';
            else if (score >= 90) calculatedGrade = 'A';
            else if (score >= 85) calculatedGrade = 'B+';
            else if (score >= 80) calculatedGrade = 'B';
            else if (score >= 75) calculatedGrade = 'C+';
            else if (score >= 70) calculatedGrade = 'C';
            else if (score >= 60) calculatedGrade = 'D';
            else calculatedGrade = 'F';
            
            // Only set grade if user hasn't manually selected one
            if (!gradeSelect.value || gradeSelect.value === '') {
              gradeSelect.value = calculatedGrade;
            }
          }
        });
      }
    });
  </script>
</body>
</html>