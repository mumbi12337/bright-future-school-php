<?php
require_once '../../includes/Auth.php';
require_once '../../models/Application.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

// Get applications from the database
$applications = new Application();
$appData = $applications->findAll();

// Handle approve/reject actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $appId = $_POST['application_id'] ?? '';
    
    if ($action && $appId) {
        try {
            $result = $applications->updateStatus($appId, strtoupper($action));
            if ($result) {
                $message = ucfirst($action) . ' application successfully.';
            } else {
                $message = 'Failed to update application status.';
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
  <title>Applications - Bright Future School</title>
  <link rel="stylesheet" href="../../public/css/styles.css">
</head>
<body>
  <!-- HEADER -->
  <header id="header">
    <div class="header-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo" onclick="window.location.href='../../../index.html'">
          <div class="logo-icon">BF</div>
          <div class="logo-text">
            <h1>BRIGHT FUTURE</h1>
            <p>Primary School</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav id="nav">
          <button data-section="home" class="nav-btn" onclick="window.location.href='../../../index.html'">Home</button>
          <button data-section="about" class="nav-btn" onclick="window.location.href='../../../index.html#about'">About</button>
          <button data-section="admissions" class="nav-btn" onclick="window.location.href='../../../index.html#admissions'">Admissions</button>
          <button data-section="academics" class="nav-btn" onclick="window.location.href='../../../index.html#academics'">Academics</button>
          <button data-section="contact" class="nav-btn" onclick="window.location.href='../../../index.html#contact'">Contact</button>
        </nav>

        <!-- Portal Button -->
        <a href="../logout.php" class="portal-btn">Logout</a>
      </div>
    </div>
  </header>

  <!-- APPLICATIONS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Manage
          <span class="gradient">Applications</span>
        </h1>
        <p class="section-description">
          Review and manage student applications
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
            <input type="text" id="searchInput" class="form-group" placeholder="Search applications..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
            <select id="statusFilter" class="form-group" style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 180px;">
              <option value="">All Status</option>
              <option value="PENDING">Pending</option>
              <option value="APPROVED">Approved</option>
              <option value="REJECTED">Rejected</option>
            </select>
          </div>
          <div class="badge" style="font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 12px; background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);">
            <?php echo count($appData); ?> Total Applications
          </div>
        </div>
      </div>

      <div class="application-cards">
        <?php foreach ($appData as $app): ?>
        <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;" data-status="<?php echo strtolower(htmlspecialchars($app['status'])); ?>">
          <form method="POST" action="" style="margin: 0;">
            <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
            
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
              <div>
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-xl font-semibold" style="color: white;"><?php echo htmlspecialchars($app['student_first_name'] . ' ' . $app['student_last_name']); ?></h3>
                  <span class="badge" style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 999px; 
                    <?php 
                      if (strtoupper($app['status']) === 'PENDING') {
                        echo 'background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);';
                      } elseif (strtoupper($app['status']) === 'APPROVED') {
                        echo 'background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3);';
                      } else {
                        echo 'background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);';
                      }
                    ?>"
                    ><?php echo strtoupper(htmlspecialchars($app['status'])); ?></span>
                </div>
                <div class="flex flex-wrap gap-4 text-sm" style="color: var(--color-text);">
                  <div class="flex items-center gap-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>DOB: <?php echo htmlspecialchars(date('Y-m-d', strtotime($app['student_date_of_birth']))); ?></span>
                  </div>
                  <div class="flex items-center gap-1">
                    <span>Grade: <?php echo htmlspecialchars($app['student_grade'] ?: 'Not specified'); ?></span>
                  </div>
                  <div class="flex items-center gap-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Applied: <?php echo htmlspecialchars(date('Y-m-d', strtotime($app['created_at']))); ?></span>
                  </div>
                </div>
              </div>
              <div class="flex gap-2">
                <?php if (strtoupper($app['status']) === 'PENDING'): ?>
                  <button type="submit" name="action" value="approve" class="btn btn-success" style="padding: 0.5rem 1rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid #22c55e; min-width: auto;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Approve
                  </button>
                  <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 0.5rem 1rem; background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; min-width: auto;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reject
                  </button>
                <?php else: ?>
                  <button type="button" class="btn btn-success" style="padding: 0.5rem 1rem; background: rgba(34, 197, 94, 0.2); color: #22c55e; border: 1px solid #22c55e; min-width: auto;" disabled>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <?php echo strtoupper(htmlspecialchars($app['status'])); ?>
                  </button>
                <?php endif; ?>
              </div>
            </div>

            <div class="grid grid-2" style="padding-top: 1rem; border-top: 1px solid var(--color-border);">
              <div>
                <h4 class="font-semibold mb-3 flex items-center gap-2" style="color: white; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; color: var(--color-primary);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Parent Information
                </h4>
                <div class="space-y-2 text-sm" style="color: var(--color-text);">
                  <div><span class="font-medium" style="color: white;">Parent Name:</span> <?php echo htmlspecialchars($app['parent_first_name'] . ' ' . $app['parent_last_name']); ?></div>
                  <div class="flex items-center gap-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span><?php echo htmlspecialchars($app['parent_email']); ?></span>
                  </div>
                  <div class="flex items-center gap-1">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; margin-right: 0.25rem;">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span><?php echo htmlspecialchars($app['parent_phone']); ?></span>
                  </div>
                  <?php if (!empty($app['parent_address'])): ?>
                  <div><span class="font-medium" style="color: white;">Address:</span> <?php echo htmlspecialchars($app['parent_address']); ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div>
                <h4 class="font-semibold mb-3" style="color: white; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; color: var(--color-primary);">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Additional Details
                </h4>
                <div class="space-y-2 text-sm" style="color: var(--color-text);">
                  <?php if (!empty($app['emergency_contact_name']) || !empty($app['emergency_contact_phone'])): ?>
                  <div><span class="font-medium" style="color: white;">Emergency Contact:</span> <?php echo htmlspecialchars($app['emergency_contact_name'] . ' (' . $app['emergency_contact_phone'] . ')'); ?></div>
                  <?php endif; ?>
                  
                  <?php if (!empty($app['previous_school'])): ?>
                  <div><span class="font-medium" style="color: white;">Previous School:</span> <?php echo htmlspecialchars($app['previous_school']); ?></div>
                  <?php endif; ?>
                  
                  <?php if (!empty($app['medical_conditions'])): ?>
                  <div><span class="font-medium" style="color: white;">Medical Conditions:</span> <?php echo htmlspecialchars($app['medical_conditions']); ?></div>
                  <?php endif; ?>
                  
                  <?php if (!empty($app['additional_notes'])): ?>
                  <div><span class="font-medium" style="color: white;">Notes:</span> <?php echo htmlspecialchars($app['additional_notes']); ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </form>
        </div>
        <?php endforeach; ?>

        <?php if (empty($appData)): ?>
        <div class="card" style="padding: 2rem; text-align: center; color: var(--color-muted);">
          <p>No applications found.</p>
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

  <script src="/public/js/main.js"></script>
  <script>
    // Application management functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Application management loaded');
      
      // Add event listeners for approve/reject buttons
      document.querySelectorAll('.btn-success:not([disabled])').forEach(button => {
        button.addEventListener('click', function(e) {
          if (!confirm('Are you sure you want to approve this application?')) {
            e.preventDefault();
            return false;
          }
        });
      });
      
      document.querySelectorAll('.btn-danger:not([disabled])').forEach(button => {
        button.addEventListener('click', function(e) {
          if (!confirm('Are you sure you want to reject this application?')) {
            e.preventDefault();
            return false;
          }
        });
      });
      
      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const statusFilter = document.getElementById('statusFilter');
      
      function filterApplications() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        
        document.querySelectorAll('[data-status]').forEach(card => {
          const cardText = card.textContent.toLowerCase();
          const cardStatus = card.getAttribute('data-status');
          
          const matchesSearch = cardText.includes(searchTerm);
          const matchesStatus = statusValue === '' || cardStatus === statusValue;
          
          if (matchesSearch && matchesStatus) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      }
      
      searchInput.addEventListener('input', filterApplications);
      statusFilter.addEventListener('change', filterApplications);
    });
    
    // Auto-hide success message after 5 seconds
    setTimeout(function() {
      const successMsg = document.querySelector('.notification-success');
      if (successMsg) successMsg.style.display = 'none';
    }, 5000);
  </script>
</body>
</html>


