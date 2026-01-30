<?php
require_once '../../includes/Auth.php';
require_once '../../models/Event.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

// Get event ID from URL
$eventId = $_GET['id'] ?? null;

if (!$eventId) {
    header('Location: events.php');
    exit;
}

$eventModel = new Event();
$event = $eventModel->findById($eventId);

if (!$event) {
    header('Location: events.php');
    exit;
}

$message = '';
$message_type = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    try {
        $result = $eventModel->delete($eventId);
        
        if ($result) {
            $message = 'Event deleted successfully.';
            $message_type = 'success';
            // Redirect after successful deletion
            header('Location: events.php?message=' . urlencode($message) . '&type=success');
            exit;
        } else {
            $message = 'Failed to delete event.';
            $message_type = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Details - Bright Future School</title>
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
    
    .btn-secondary {
      background: rgba(220, 38, 38, 0.2);
      color: #ef4444;
      border: 1px solid #dc2626;
    }
    
    .btn-secondary:hover {
      background: rgba(220, 38, 38, 0.3);
    }
    
    .event-detail {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      border-bottom: 1px solid var(--color-border);
    }
    
    .event-detail:last-child {
      border-bottom: none;
    }
    
    .detail-label {
      color: var(--color-text);
      font-weight: 500;
      min-width: 120px;
    }
    
    .detail-value {
      color: white;
      flex: 1;
      text-align: right;
    }
    
    .detail-value.description {
      text-align: left;
      white-space: pre-wrap;
      line-height: 1.6;
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

  <!-- VIEW EVENT PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Event
          <span class="gradient">Details</span>
        </h1>
        <p class="section-description">
          View and manage event information
        </p>
      </div>

      <?php if ($message): ?>
        <div class="notification notification-<?php echo $message_type; ?>" style="margin-bottom: 1rem; padding: 1rem; border-radius: 8px; background-color: <?php echo $message_type === 'success' ? '#10b981' : '#ef4444'; ?>; color: white;">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <a href="events.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-primary); text-decoration: none; margin-bottom: 1.5rem;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        ← Back to Events
      </a>

      <div class="card" style="margin-top: 1.5rem; overflow: hidden;">
        <div class="header" style="background: linear-gradient(135deg, var(--color-primary), var(--color-accent)); color: white; padding: 2rem;">
          <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;"><?php echo htmlspecialchars($event['title']); ?></h1>
          <p style="margin: 0; color: rgba(255, 255, 255, 0.8); font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <?php echo date('j F Y', strtotime($event['date'])); ?>
          </p>
        </div>

        <div class="section" style="padding: 2rem;">
          <?php if (!empty($event['description'])): ?>
            <div class="event-detail">
              <div class="detail-label">Description:</div>
              <div class="detail-value description"><?php echo htmlspecialchars($event['description']); ?></div>
            </div>
          <?php else: ?>
            <div class="event-detail">
              <div class="detail-label">Description:</div>
              <div class="detail-value description" style="color: var(--color-text); font-style: italic;">No description provided</div>
            </div>
          <?php endif; ?>
          
          <div class="event-detail">
            <div class="detail-label">Event ID:</div>
            <div class="detail-value">#<?php echo $event['id']; ?></div>
          </div>
          
          <div class="event-detail">
            <div class="detail-label">Created:</div>
            <div class="detail-value"><?php echo date('j F Y \a\t g:i A', strtotime($event['created_at'])); ?></div>
          </div>
          
          <?php if (!empty($event['updated_at']) && $event['updated_at'] != $event['created_at']): ?>
            <div class="event-detail">
              <div class="detail-label">Last Updated:</div>
              <div class="detail-value"><?php echo date('j F Y \a\t g:i A', strtotime($event['updated_at'])); ?></div>
            </div>
          <?php endif; ?>
        </div>

        <div style="padding: 1.5rem; background: rgba(0, 0, 0, 0.2); border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; gap: 1rem;">
          <a href="events.php" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none;">
            Close
          </a>
          <form method="POST" style="margin: 0; display: inline;">
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
            <button type="submit" name="delete_event" class="btn btn-secondary" style="padding: 0.75rem 1.5rem;" onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
              Delete Event
            </button>
          </form>
        </div>
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
</body>
</html>