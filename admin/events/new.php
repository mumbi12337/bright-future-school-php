<?php
require_once '../../includes/Auth.php';
require_once '../../models/Event.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($title) || empty($date)) {
        $message = 'Title and date are required.';
        $message_type = 'error';
    } else {
        try {
            $eventModel = new Event();
            $eventId = $eventModel->createEvent($title, $description, $date);
            
            if ($eventId) {
                $message = 'Event created successfully!';
                $message_type = 'success';
                
                // Reset form values
                $title = '';
                $date = '';
                $description = '';
            } else {
                $message = 'Failed to create event.';
                $message_type = 'error';
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
  <title>Add New Event - Bright Future School</title>
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

  <!-- ADD EVENT PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Add New
          <span class="gradient">Event</span>
        </h1>
        <p class="section-description">
          Create a new event for the school calendar
        </p>
      </div>

      <?php if ($message): ?>
        <div class="notification notification-<?php echo $message_type; ?>" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: <?php echo $message_type === 'success' ? '#10b981' : '#ef4444'; ?>; color: white;">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <form id="new-event-form" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
          <div class="form-group">
            <label for="title" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Event Title</label>
            <input 
              type="text" 
              id="title" 
              name="title" 
              placeholder="Enter event title" 
              required 
              class="form-control"
              value="<?php echo htmlspecialchars($title ?? ''); ?>"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem;"
            />
          </div>
          
          <div class="form-group">
            <label for="date" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Event Date</label>
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
          
          <div class="form-group">
            <label for="description" style="display: block; margin-bottom: 0.5rem; color: white; font-weight: 500;">Description (Optional)</label>
            <textarea 
              id="description" 
              name="description" 
              placeholder="Enter event description" 
              rows="4"
              class="form-control"
              style="width: 100%; padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; resize: vertical;"
            ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
          </div>
          
          <button 
            type="submit" 
            class="btn btn-primary"
            style="padding: 0.75rem 1.5rem; background: var(--color-primary); color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 1rem; margin-top: 1rem;"
          >
            Save Event
          </button>
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
    // Event form submission handling
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('new-event-form');
      
      form.addEventListener('submit', function(e) {
        const title = document.getElementById('title').value.trim();
        const date = document.getElementById('date').value.trim();
        
        if (!title || !date) {
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


