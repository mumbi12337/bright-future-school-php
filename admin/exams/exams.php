<?php
require_once '../../includes/Auth.php';
require_once '../../models/Exam.php';
require_once '../../models/Grade.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

// Get exams from the database
$examModel = new Exam();
$gradeModel = new Grade();

$exams = $examModel->findAllWithGrade();
$grades = $gradeModel->findAll();

// Handle delete action
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exam'])) {
    $examId = $_POST['exam_id'] ?? '';
    
    if ($examId) {
        try {
            $result = $examModel->delete($examId);
            
            if ($result) {
                $message = 'Exam deleted successfully.';
                // Refresh exams list
                $exams = $examModel->findAllWithGrade();
            } else {
                $message = 'Failed to delete exam.';
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exams - Bright Future School</title>
  <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>

  <!-- EXAMS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Manage
          <span class="gradient">Exams</span>
        </h1>
        <p class="section-description">
          View, create, and manage school examinations
        </p>
      </div>

      <?php if ($message): ?>
        <div class="notification notification-success" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: #10b981; color: white;">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 10px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <input type="text" id="searchInput" class="form-group" placeholder="Search exams..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
            <select id="gradeFilter" class="form-group" style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 180px;">
              <option value="">All Grades</option>
              <?php foreach ($grades as $grade): ?>
              <option value="<?php echo htmlspecialchars($grade['name']); ?>"><?php echo htmlspecialchars($grade['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Exam
          </a>
        </div>
      </div>

      <div class="grid grid-2" id="exams-container">
        <?php foreach ($exams as $exam): ?>
        <div class="card" style="padding: 1.5rem;" data-grade="<?php echo htmlspecialchars($exam['grade_name'] ?? ''); ?>" data-subject="<?php echo htmlspecialchars($exam['subject'] ?? ''); ?>">
          <form method="POST" action="" style="margin: 0;">
            <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
            
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom: 1rem;">
              <div>
                <h3 style="color: white; margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 600;"><?php echo htmlspecialchars($exam['title']); ?></h3>
                <p style="color: var(--color-text); margin: 0.25rem 0; font-size: 0.875rem;">
                  <?php echo htmlspecialchars($exam['subject'] ?? ''); ?> • <?php echo htmlspecialchars($exam['grade_name'] ?? ''); ?>
                </p>
                <p style="color: var(--color-muted); margin: 0; font-size: 0.875rem;">
                  <?php echo htmlspecialchars(date('j F Y', strtotime($exam['date']))); ?>
                </p>
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <a href="view.php?id=<?php echo $exam['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; min-width: auto;">
                  View
                </a>
                <button type="submit" name="delete_exam" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; min-width: auto; background: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: #ef4444;">
                  Delete
                </button>
              </div>
            </div>
          </form>
        </div>
        <?php endforeach; ?>

        <?php if (empty($exams)): ?>
        <div class="card" style="padding: 2rem; text-align: center; color: var(--color-muted); grid-column: 1 / -1;">
          <p>No exams found.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>



  <script src="../../public/js/main.js"></script>
  <script>
    // Exam management functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Exam management loaded');
      
      // Add event listeners for delete buttons
      document.querySelectorAll('button[name="delete_exam"]').forEach(button => {
        button.addEventListener('click', function(e) {
          if (!confirm('Are you sure you want to delete this exam?')) {
            e.preventDefault();
            return false;
          }
        });
      });
      
      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const gradeFilter = document.getElementById('gradeFilter');
      
      function filterExams() {
        const searchTerm = searchInput.value.toLowerCase();
        const gradeValue = gradeFilter.value.toLowerCase();
        
        document.querySelectorAll('[data-grade]').forEach(card => {
          const grade = card.getAttribute('data-grade').toLowerCase();
          const subject = card.getAttribute('data-subject').toLowerCase();
          const cardText = card.textContent.toLowerCase();
          
          const matchesSearch = cardText.includes(searchTerm);
          const matchesGrade = gradeValue === '' || grade.includes(gradeValue);
          
          if (matchesSearch && matchesGrade) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      }
      
      searchInput.addEventListener('input', filterExams);
      gradeFilter.addEventListener('change', filterExams);
    });
    
    // Auto-hide success message after 5 seconds
    setTimeout(function() {
      const successMsg = document.querySelector('.notification-success');
      if (successMsg) successMsg.style.display = 'none';
    }, 5000);
  </script>
</body>
</html>


