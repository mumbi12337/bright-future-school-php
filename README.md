# Bright Future School - CSS/JS Framework

This document explains how to use the consistent styling and JavaScript framework across all pages.

## File Structure

```
/public/
  ├── css/
  │   └── styles.css     # Main stylesheet with all homepage styles
  ├── js/
  │   └── main.js        # Main JavaScript with navigation and utilities
/includes/
  ├── template.php       # Reusable page template
  └── db.php            # Database connection
```

## How to Create New Pages

### Method 1: Using the Template System (Recommended)

Create a new PHP file and use this structure:

```php
<?php
// Include database connection
require_once '../includes/db.php';

// Set page variables
$page_title = 'Your Page Title - Bright Future School';
$active_section = 'home'; // 'home', 'about', 'admissions', 'academics', 'contact'

// Your page content
ob_start();
?>
<!-- Your HTML content goes here -->
<section id="home" style="min-height: 80vh;">
  <div class="container" style="padding-top: 6rem;">
    <!-- Your content -->
  </div>
</section>
<?php
$content = ob_get_clean();

// Include the template
include '../includes/template.php';
?>
```

### Method 2: Manual Inclusion

If you prefer to build pages manually:

```php
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Page Title</title>
  <link rel="stylesheet" href="/public/css/styles.css">
</head>
<body>
  <!-- Your HTML content -->
  
  <script src="/public/js/main.js"></script>
</body>
</html>
```

## Available CSS Classes

### Layout
- `.container` - Centered content wrapper (max-width: 1200px)
- `.grid-2` - Two-column grid (stacks on mobile)
- `.grid-3` - Three-column grid (stacks on mobile)

### Cards
- `.card` - Basic card component with hover effects
- `.academic-card` - Colored cards for subjects/programs
  - `.blue` - Blue variant
  - `.purple` - Purple variant  
  - `.green` - Green variant

### Sections
- `.section-header` - Section title area
- `.section-badge` - Decorative badge for sections
- `.section-title` - Main section heading
- `.section-description` - Section subtitle/description

### Buttons
- `.btn` - Base button class
- `.btn-primary` - Primary action button (blue gradient)
- `.btn-secondary` - Secondary button (outlined)

### Utilities
- `.mb-2` - Margin bottom 0.5rem
- `.mb-4` - Margin bottom 1rem
- `.mb-6` - Margin bottom 1.5rem
- `.mb-8` - Margin bottom 2rem

## Available JavaScript Functions

### Navigation
- `scrollToSection(sectionId)` - Smooth scroll to section
- `setActiveSection(sectionId)` - Manually set active navigation item

### Forms
- `handleFormSubmit(event)` - Generic form handler with success message

### Utilities
- `showNotification(message, type)` - Show toast notification
  - `type`: 'success' (green) or 'error' (red)

### Example Usage

```javascript
// Scroll to a section
scrollToSection('admissions');

// Show success notification
showNotification('Profile updated successfully!', 'success');

// Show error notification
showNotification('Please fill all required fields.', 'error');
```

## Customization

### Adding Extra CSS/JS

In your PHP page, you can add extra resources:

```php
<?php
$page_title = 'My Page';
$extra_css = ['/public/css/custom.css'];
$extra_js = ['/public/js/custom.js'];
$inline_js = 'console.log("Page loaded!");';
// ... rest of template code
?>
```

### Active Navigation

Control which navigation item is highlighted:

```php
$active_section = 'admissions'; // Will highlight Admissions nav item
```

Options: 'home', 'about', 'admissions', 'academics', 'contact'

## Responsive Design

The framework is fully responsive:
- Mobile-first approach
- Grid layouts stack on small screens
- Font sizes use `clamp()` for fluid scaling
- Touch-friendly navigation targets

## Color Variables

All colors are defined in CSS variables for easy customization:

```css
:root {
  --color-bg: #0a0f1e;        /* Background */
  --color-surface: #1a1f35;   /* Card surfaces */
  --color-primary: #3b82f6;   /* Primary blue */
  --color-accent: #60a5fa;    /* Light blue accent */
  --color-warning: #fbbf24;   /* Yellow/orange */
  --color-text: #e5e7eb;      /* Main text */
  --color-muted: #9ca3af;     /* Secondary text */
  --color-border: #374151;    /* Border colors */
}
```

## Best Practices

1. **Always use the template system** for consistency
2. **Use semantic HTML** with appropriate classes
3. **Test on mobile devices** before deploying
4. **Keep custom CSS minimal** - use existing classes when possible
5. **Place custom scripts** in `/public/js/` directory
6. **Use the notification system** for user feedback

## Troubleshooting

### Styles not loading?
- Check file paths in template.php
- Verify web server can access /public/ directory
- Clear browser cache

### JavaScript not working?
- Ensure main.js is loaded after DOM content
- Check browser console for errors
- Verify element IDs match JavaScript expectations

### Navigation highlighting wrong?
- Make sure `$active_section` variable matches the section ID
- Check that section elements have correct IDs