<?php
require_once '../../includes/Auth.php';
require_once '../../models/Event.php';

$auth = new Auth();
$auth->requireRole('ADMIN');

// Get events from the database
$eventModel = new Event();
$events = $eventModel->findAll();

// Handle delete action
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $eventId = $_POST['event_id'] ?? '';
    
    if ($eventId) {
        try {
            $eventModel = new Event();
            $result = $eventModel->delete($eventId);
            
            if ($result) {
                $message = 'Event deleted successfully.';
                // Refresh events list
                $events = $eventModel->findAll();
            } else {
                $message = 'Failed to delete event.';
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
  <title>Events - Bright Future School</title>
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

  <!-- EVENTS PAGE -->
  <section id="home" style="min-height: 100vh; padding-top: 5rem;">
    <div class="container">
      <div class="section-header" style="margin-bottom: 3rem;">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Administration</span>
        </div>
        <h1 class="section-title">
          Manage
          <span class="gradient">Events</span>
        </h1>
        <p class="section-description">
          View, create, and manage school events
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
            <input type="text" id="searchInput" class="form-group" placeholder="Search events..." style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 250px;">
            <select id="eventTypeFilter" class="form-group" style="padding: 0.75rem 1rem; border-radius: 12px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--color-border); color: white; font-size: 1rem; width: 180px;">
              <option value="">All Events</option>
              <option value="upcoming">Upcoming</option>
              <option value="past">Past</option>
              <option value="academic">Academic</option>
              <option value="sports">Sports</option>
              <option value="cultural">Cultural</option>
            </select>
          </div>
          <a href="new.php" class="btn btn-primary" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Event
          </a>
        </div>
      </div>

      <div class="grid grid-2" id="events-container">
        <?php foreach ($events as $event): ?>
        <div class="card" style="padding: 1.5rem;" data-event-type="<?php echo strtolower(htmlspecialchars($event['title'])); ?>" data-date="<?php echo htmlspecialchars($event['date']); ?>">
          <form method="POST" action="" style="margin: 0;">
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
            
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom: 1rem;">
              <div>
                <h3 style="color: white; margin: 0 0 0.25rem 0; font-size: 1.25rem; font-weight: 600;"><?php echo htmlspecialchars($event['title']); ?></h3>
                <p style="color: var(--color-muted); margin: 0.25rem 0; font-size: 0.875rem;">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 0.25rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <?php echo htmlspecialchars(date('j F Y', strtotime($event['date']))); ?>
                </p>
                <p style="color: var(--color-text); margin: 0.5rem 0 0 0; font-size: 0.875rem;">
                  <?php echo htmlspecialchars($event['description'] ?: 'No description provided'); ?>
                </p>
              </div>
              <div style="display: flex; gap: 0.5rem;">
                <a href="view.php?id=<?php echo $event['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-decoration: none; min-width: auto;">
                  View Details
                </a>
                <button type="submit" name="delete_event" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.875rem; min-width: auto; background: rgba(220, 38, 38, 0.2); border: 1px solid #dc2626; color: #ef4444;">
                  Delete
                </button>
              </div>
            </div>
          </form>
        </div>
        <?php endforeach; ?>

        <?php if (empty($events)): ?>
        <div class="card" style="padding: 2rem; text-align: center; color: var(--color-muted); grid-column: 1 / -1;">
          <p>No events found.</p>
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

  <script src="../../public/js/main.js"></script>
  <script>
    // Event management functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Event management loaded');
      
      // Add event listeners for delete buttons
      document.querySelectorAll('button[name="delete_event"]').forEach(button => {
        button.addEventListener('click', function(e) {
          if (!confirm('Are you sure you want to delete this event?')) {
            e.preventDefault();
            return false;
          }
        });
      });
      
      // Search functionality
      const searchInput = document.getElementById('searchInput');
      const eventTypeFilter = document.getElementById('eventTypeFilter');
      
      function filterEvents() {
        const searchTerm = searchInput.value.toLowerCase();
        const eventTypeValue = eventTypeFilter.value.toLowerCase();
        
        document.querySelectorAll('[data-event-type]').forEach(card => {
          const eventType = card.getAttribute('data-event-type').toLowerCase();
          const cardText = card.textContent.toLowerCase();
          
          const matchesSearch = cardText.includes(searchTerm);
          const matchesType = eventTypeValue === '' || eventType.includes(eventTypeValue);
          
          if (matchesSearch && matchesType) {
            card.style.display = '';
          } else {
            card.style.display = 'none';
          }
        });
      }
      
      searchInput.addEventListener('input', filterEvents);
      eventTypeFilter.addEventListener('change', filterEvents);
    });
    
    // Auto-hide success message after 5 seconds
    setTimeout(function() {
      const successMsg = document.querySelector('.notification-success');
      if (successMsg) successMsg.style.display = 'none';
    }, 5000);
  </script>
</body>
</html>


