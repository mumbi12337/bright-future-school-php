// Active section tracking
let activeSection = 'home';

// Scroll-based header styling
window.addEventListener('scroll', () => {
  const header = document.getElementById('header');
  if (header) {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }

  // Update active section based on scroll position
  updateActiveSection();
});

// Smooth scroll to section
function scrollToSection(sectionId) {
  const element = document.getElementById(sectionId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
    setActiveSection(sectionId);
  }
}

// Set active section
function setActiveSection(sectionId) {
  activeSection = sectionId;
  const navButtons = document.querySelectorAll('.nav-btn');
  navButtons.forEach(btn => {
    if (btn.dataset.section === sectionId) {
      btn.classList.add('active');
    } else {
      btn.classList.remove('active');
    }
  });
}

// Update active section based on scroll position
function updateActiveSection() {
  const sections = ['home', 'about', 'admissions', 'academics', 'contact'];
  const scrollPosition = window.scrollY + 200;

  for (const sectionId of sections) {
    const section = document.getElementById(sectionId);
    if (section) {
      const sectionTop = section.offsetTop;
      const sectionBottom = sectionTop + section.offsetHeight;

      if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
        if (activeSection !== sectionId) {
          setActiveSection(sectionId);
        }
        break;
      }
    }
  }
}

// Navigation button click handlers
document.addEventListener('DOMContentLoaded', () => {
  // Add event listeners to navigation buttons
  document.querySelectorAll('.nav-btn').forEach(button => {
    button.addEventListener('click', () => {
      const sectionId = button.dataset.section;
      scrollToSection(sectionId);
    });
  });

  // Initialize active section
  updateActiveSection();
});

// Form submission handler
function handleFormSubmit(event) {
  event.preventDefault();
  
  // Get form data
  const formData = new FormData(event.target);
  const data = Object.fromEntries(formData);
  
  // Check if we're on a contact form
  if (event.target.classList.contains('contact-form')) {
    // Handle contact form submission
    submitContactForm(data);
  } else {
    alert('Thank you for your message! We will get back to you soon.');
    event.target.reset();
  }
}

// Submit contact form via API
async function submitContactForm(data) {
  try {
    // This would connect to a contact form API if available
    showNotification('Thank you for your message! We will get back to you soon.', 'success');
  } catch (error) {
    showNotification('Error submitting form. Please try again.', 'error');
    console.error('Form submission error:', error);
  }
}

// Utility function to show notification
function showNotification(message, type = 'success') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  
  // Style the notification
  Object.assign(notification.style, {
    position: 'fixed',
    top: '20px',
    right: '20px',
    padding: '1rem 1.5rem',
    borderRadius: '8px',
    backgroundColor: type === 'success' ? '#10b981' : '#ef4444',
    color: 'white',
    zIndex: '1000',
    boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
    transform: 'translateX(100%)',
    transition: 'transform 0.3s ease'
  });
  
  // Add to DOM
  document.body.appendChild(notification);
  
  // Slide in
  setTimeout(() => {
    notification.style.transform = 'translateX(0)';
  }, 10);
  
  // Remove after 3 seconds
  setTimeout(() => {
    notification.style.transform = 'translateX(100%)';
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

// Mobile menu toggle (if you add a mobile menu later)
function toggleMobileMenu() {
  const nav = document.getElementById('nav');
  if (nav) {
    nav.classList.toggle('mobile-open');
  }
}

// Check authentication status on page load
async function checkAuthStatus() {
  try {
    console.log('Checking authentication status...');
    console.log('Current page:', window.location.href);
    
    let data;
    let attempts = 0;
    const maxAttempts = 3;
    
    while (attempts < maxAttempts) {
      attempts++;
      console.log(`Attempt ${attempts} to check auth`);
      
      try {
        // Always try direct fetch first with the correct path
        console.log('Trying direct fetch with correct path');
        const response = await fetch('/bright-future-school-php/api/auth.php?action=check');

        
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        data = await response.json();
        console.log('Direct fetch successful:', data);
        break;
        
      } catch (fetchError) {
        console.error(`Direct fetch attempt ${attempts} failed:`, fetchError.message);
        
        // If we have more attempts, try the API client
        if (attempts < maxAttempts && typeof SchoolAPI !== 'undefined') {
          try {
            console.log('Trying SchoolAPI client');
            const api = new SchoolAPI();
            data = await api.checkAuth();
            console.log('API client successful:', data);
            break;
          } catch (apiError) {
            console.error('API client failed:', apiError.message);
          }
        }
        
        // If this was the last attempt, re-throw the error
        if (attempts === maxAttempts) {
          throw fetchError;
        }
      }
    }
    
    if (data && data.authenticated) {
      // Update UI based on user role
      updateUIForRole(data.user.role);
    }
  } catch (error) {
    console.error('Authentication check failed after all attempts:', error);
  }
}

// Update UI based on user role
function updateUIForRole(role) {
  // Change logout button text based on role
  const logoutBtn = document.querySelector('.portal-btn[href="/login.html"], .portal-btn[href="../login.html"]');
  if (logoutBtn) {
    logoutBtn.textContent = `Logout (${role})`;
  }
}

// Export functions for global use
window.scrollToSection = scrollToSection;
window.handleFormSubmit = handleFormSubmit;
window.showNotification = showNotification;
window.toggleMobileMenu = toggleMobileMenu;

// Initialize auth check
checkAuthStatus();
// MOBILE NAVIGATION MENU FUNCTIONALITY
document.addEventListener('DOMContentLoaded', function() {
  const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
  const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
  const mobileNavMenu = document.querySelector('.mobile-nav-menu');
  const mobileNavClose = document.querySelector('.mobile-nav-close');
  
  // Open mobile menu
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      openMobileMenu();
    });
  }
  
  // Close mobile menu
  if (mobileNavClose) {
    mobileNavClose.addEventListener('click', closeMobileMenu);
  }
  
  // Close when clicking overlay
  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener('click', closeMobileMenu);
  }
  
  // Close when clicking outside menu
  document.addEventListener('click', function(e) {
    if (mobileNavMenu && mobileNavMenu.classList.contains('show') && 
        !mobileNavMenu.contains(e.target) && 
        !mobileMenuBtn.contains(e.target)) {
      closeMobileMenu();
    }
  });
  
  // Close menu on escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && mobileNavMenu && mobileNavMenu.classList.contains('show')) {
      closeMobileMenu();
    }
  });
  
  function openMobileMenu() {
    if (mobileNavOverlay) mobileNavOverlay.classList.add('show');
    if (mobileNavMenu) mobileNavMenu.classList.add('show');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
  }
  
  function closeMobileMenu() {
    if (mobileNavOverlay) mobileNavOverlay.classList.remove('show');
    if (mobileNavMenu) mobileNavMenu.classList.remove('show');
    document.body.style.overflow = ''; // Restore scrolling
  }
  
  // Handle window resize
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 1024) {
      closeMobileMenu();
    }
  });
});

