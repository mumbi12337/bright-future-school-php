<?php
require_once '../../includes/Auth.php';
require_once '../../models/Exam.php';
require_once '../../models/Grade.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

// Get all grades for the dropdown
$gradeModel = new Grade();
$grades = $gradeModel->findAll();

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $date = trim($_POST['date'] ?? '');
    
    if (empty($title) || empty($subject) || empty($grade) || empty($date)) {
        $message = 'All fields are required.';
        $message_type = 'error';
    } else {
        try {
            // Find the grade ID based on the grade name
            $gradeId = null;
            foreach ($grades as $g) {
                if ($g['name'] === $grade) {
                    $gradeId = $g['id'];
                    break;
                }
            }
            
            if (!$gradeId) {
                $message = 'Selected grade not found.';
                $message_type = 'error';
            } else {
                $examModel = new Exam();
                $examId = $examModel->createExam($title, $gradeId, $subject, $date);
                
                if ($examId) {
                    $message = 'Exam created successfully!';
                    $message_type = 'success';
                    
                    // Reset form values
                    $title = '';
                    $subject = '';
                    $grade = '';
                    $date = '';
                } else {
                    $message = 'Failed to create exam.';
                    $message_type = 'error';
                }
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Exam - Bright Future School</title>
  <link rel="stylesheet" href="../../public/css/styles.css">
  <style>
    :root {
      --color-primary: #3b82f6;
      --color-accent: #8b5cf6;
      --color-text: #9ca3af;
      --color-muted: #6b7280;
      --color-border: rgba(255, 255, 255, 0.1);
      --color-surface: rgba(26, 31, 53, 0.5);
    }
    
    .gradient {
      background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .section-badge {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }
    
    .badge-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: var(--color-primary);
    }
    
    .badge-text {
      color: var(--color-primary);
      font-weight: 500;
      font-size: 0.875rem;
    }
    
    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: white;
      margin: 0 0 1rem 0;
    }
    
    .section-header {
      text-align: center;
    }
    
    .section-description {
      color: var(--color-text);
      font-size: 1.125rem;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .card {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 16px;
      backdrop-filter: blur(20px);
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      border: none;
    }
    
    .btn-primary {
      background: linear-gradient(to right, var(--color-primary), var(--color-accent));
      color: white;
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px -6px var(--color-primary);
    }
  </style>
</head>
<body>


  <!-- ADD EXAM PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Exam</span>
        </h1>
        <p class="section-description">
          Create a new exam for students
        </p>
      </div>

      <?php if ($message): ?>
        <div class="notification notification-<?php echo $message_type; ?>" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: <?php echo $message_type === 'success' ? '#10b981' : '#ef4444'; ?>; color: white;">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <form id="new-exam-form" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
          <div class="form-group">
            <label for="title" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Exam Title</label>
            <input 
              type="text" 
              id="title" 
              name="title" 
              placeholder="Enter exam title" 
              required 
              class="form-control"
              value="<?php echo htmlspecialchars($title ?? ''); ?>"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            />
          </div>
          
          <div class="form-group">
            <label for="subject" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Subject</label>
            <input 
              type="text" 
              id="subject" 
              name="subject" 
              placeholder="Enter subject (e.g. Math)" 
              required 
              class="form-control"
              value="<?php echo htmlspecialchars($subject ?? ''); ?>"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            />
          </div>
          
          <div class="form-group">
            <label for="grade" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Grade</label>
            <select 
              id="grade" 
              name="grade" 
              required 
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            >
              <option value="">Select Grade</option>
              <?php foreach ($grades as $g): ?>
              <option value="<?php echo htmlspecialchars($g['name']); ?>" <?php echo (isset($grade) && $grade === $g['name']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($g['name']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label for="date" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Exam Date</label>
            <input 
              type="date" 
              id="date" 
              name="date" 
              required 
              class="form-control"
              value="<?php echo htmlspecialchars($date ?? ''); ?>"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            />
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Save Exam
          </button>
        </form>
      </div>
    </div>
  </section>



  <script src="/public/js/main.js"></script>
  <script>
    // Exam form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('new-exam-form');
      
      form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const subject = document.getElementById('subject').value.trim();
        const grade = document.getElementById('grade').value.trim();
        const date = document.getElementById('date').value.trim();
        
        if (!title || !subject || !grade || !date) {
          e.preventDefault();
          showNotification('Please fill in all required fields.', 'error');
          return;
        }
      });
    });
    
    // Auto-hide success message after 5 seconds
    setTimeout(function() {
      const successMsg = document.querySelector('.notification-success');
      if (successMsg) successMsg.style.display = 'none';
    }, 5000);
  </script>
</body>
</html>


