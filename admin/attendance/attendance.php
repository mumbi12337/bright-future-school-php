<?php
require_once '../../includes/Auth.php';
require_once '../../models/Attendance.php';
require_once '../../models/Student.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

// Get attendance records from the database
$attendanceModel = new Attendance();
$studentModel = new Student();

// Get all students with their attendance stats
$students = $studentModel->findAll();
$attendanceData = [];

foreach ($students as $student) {
    $stats = $attendanceModel->getAttendanceStats($student['id']);
    if ($stats) {
        $attendanceData[] = [
            'student' => $student,
            'stats' => $stats
        ];
    }
}

// Calculate attendance percentage
function calculateAttendancePercentage($stats) {
    if ($stats['total_days'] > 0) {
        return round(($stats['present_days'] / $stats['total_days']) * 100, 0);
    }
    return 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Reports - Bright Future School</title>
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
    
    .section-description {
      color: var(--color-text);
      font-size: 1.125rem;
      max-width: 600px;
      margin: 0 auto;
    }
    
    .section-header {
      text-align: center;
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

  <!-- ATTENDANCE PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Manage
          <span class="gradient">Attendance</span>
        </h1>
        <p class="section-description">
          View and manage student attendance records
        </p>
      </div>

      <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <div class="header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 10px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <input type="text" id="searchInput" class="form-group" placeholder="Search attendance records..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
            <select id="gradeFilter" class="form-group" style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 180px;">
              <option value="">All Classes</option>
              <option value="Grade 1">Grade 1</option>
              <option value="Grade 2">Grade 2</option>
              <option value="Grade 3">Grade 3</option>
              <option value="Grade 4">Grade 4</option>
              <option value="Grade 5">Grade 5</option>
              <option value="Grade 6">Grade 6</option>
            </select>
          </div>
          <a href="../../teacher/attendance.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Mark Attendance
          </a>
        </div>
      </div>

      <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background-color: var(--color-surface);">
              <th style="padding: 1rem; text-align: left; border-bottom: 1px solid var(--color-border);">Student</th>
              <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Present</th>
              <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Absent</th>
              <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Late</th>
              <th style="padding: 1rem; text-align: center; border-bottom: 1px solid var(--color-border);">Attendance %</th>
            </tr>
          </thead>
          <tbody id="attendance-table-body">
            <?php foreach ($attendanceData as $item): ?>
            <tr style="border-bottom: 1px solid var(--color-border);" data-grade="<?php echo htmlspecialchars($item['student']['grade']); ?>">
              <td style="padding: 1rem; color: white; font-weight: 500;"><?php echo htmlspecialchars($item['student']['first_name'] . ' ' . $item['student']['last_name']); ?></td>
              <td style="padding: 1rem; text-align: center; color: var(--color-text);"><?php echo $item['stats']['present_days'] ?? 0; ?></td>
              <td style="padding: 1rem; text-align: center; color: var(--color-text);"><?php echo $item['stats']['absent_days'] ?? 0; ?></td>
              <td style="padding: 1rem; text-align: center; color: var(--color-text);"><?php echo $item['stats']['late_days'] ?? 0; ?></td>
              <td style="padding: 1rem; text-align: center; font-weight: 600; 
                <?php 
                  $percentage = calculateAttendancePercentage($item['stats']);
                  if ($percentage >= 90) echo 'color: #22c55e;';
                  elseif ($percentage >= 70) echo 'color: #f59e0b;';
                  else echo 'color: #ef4444;';
                ?>
              "><?php echo $percentage; ?>%</td>
            </tr>
            <?php endforeach; ?>

            <?php if (empty($attendanceData)): ?>
            <tr>
              <td colspan="5" style="padding: 1rem; text-align: center; color: var(--color-muted);">No attendance records found</td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
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
    // Attendance management functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Attendance management loaded');
      
      // Add event listener for Mark Attendance button
      document.querySelector('.btn-primary').addEventListener('click', function(e) {
        e.preventDefault();
        showNotification('Attendance marking started', 'info');
      });
      
      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const gradeFilter = document.getElementById('gradeFilter');
      
      function filterAttendance() {
        const searchTerm = searchInput.value.toLowerCase();
        const gradeValue = gradeFilter.value.toLowerCase();
        
        document.querySelectorAll('#attendance-table-body tr[data-grade]').forEach(row => {
          const studentName = row.cells[0].textContent.toLowerCase();
          const grade = row.getAttribute('data-grade').toLowerCase();
          
          const matchesSearch = studentName.includes(searchTerm);
          const matchesGrade = gradeValue === '' || grade === gradeValue.toLowerCase();
          
          if (matchesSearch && matchesGrade) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      }
      
      searchInput.addEventListener('input', filterAttendance);
      gradeFilter.addEventListener('change', filterAttendance);
    });
  </script>
</body>
</html>


