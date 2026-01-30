

<?php
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[$key] = trim($value);
    }
}


require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Bright Future Primary School - Quality Education in Lusaka, Zambia</title>
  
  <style>
    :root {
      --color-bg: #0a0f1e;
      --color-surface: #1a1f35;
      --color-primary: #3b82f6;
      --color-secondary: #2563eb;
      --color-accent: #60a5fa;
      --color-warning: #fbbf24;
      --color-text: #e5e7eb;
      --color-muted: #9ca3af;
      --color-border: #374151;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background: var(--color-bg);
      color: var(--color-text);
      overflow-x: hidden;
    }

    /* HEADER */
    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 50;
      transition: all 0.5s ease;
    }

    header.scrolled {
      background: rgba(10, 15, 30, 0.95);
      backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .header-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem;
    }

    .header-content {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      cursor: pointer;
    }

    .logo-icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(to bottom right, var(--color-primary), var(--color-accent));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.125rem;
      color: white;
      transition: transform 0.3s ease;
      box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }

    .logo:hover .logo-icon {
      transform: scale(1.1) rotate(3deg);
    }

    .logo-text h1 {
      font-size: 1.125rem;
      font-weight: bold;
      color: white;
      letter-spacing: 0.05em;
    }

    .logo-text p {
      font-size: 0.75rem;
      color: var(--color-accent);
      font-weight: 500;
      letter-spacing: 0.05em;
    }

    nav {
      display: flex;
      align-items: center;
      gap: 2rem;
    }

    nav button {
      background: none;
      border: none;
      font-size: 0.875rem;
      font-weight: 500;
      letter-spacing: 0.05em;
      color: var(--color-muted);
      cursor: pointer;
      transition: color 0.3s ease;
      position: relative;
      padding: 0.5rem 0;
    }

    nav button:hover {
      color: white;
    }

    nav button.active {
      color: var(--color-accent);
    }

    nav button.active::after {
      content: '';
      position: absolute;
      bottom: -4px;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(to right, var(--color-primary), var(--color-accent));
      border-radius: 9999px;
    }

    .portal-btn {
      padding: 0.625rem 1.5rem;
      border-radius: 12px;
      background: linear-gradient(to right, var(--color-primary), var(--color-accent));
      color: white;
      font-weight: 500;
      font-size: 0.875rem;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 4px 24px -6px var(--color-primary);
    }

    .portal-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 32px -6px var(--color-primary);
    }
    
    .user-menu {
      position: relative;
    }
    
    .user-btn {
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      color: white;
      padding: 0.625rem 1.5rem;
      border-radius: 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .dropdown-menu {
      position: absolute;
      top: 100%;
      right: 0;
      background: var(--color-surface);
      border: 1px solid var(--color-border);
      border-radius: 12px;
      padding: 0.5rem 0;
      min-width: 160px;
      z-index: 100;
      display: none;
    }
    
    .dropdown-menu.show {
      display: block;
    }
    
    .dropdown-item {
      display: block;
      padding: 0.75rem 1rem;
      color: var(--color-text);
      text-decoration: none;
      transition: background 0.3s ease;
    }
    
    .dropdown-item:hover {
      background: rgba(59, 130, 246, 0.2);
    }

    /* SECTIONS */
    section {
      position: relative;
      width: 100%;
      padding: 6rem 1.5rem;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    /* HERO SECTION */
    #home {
      min-height: 85vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding-top: 5rem;
      overflow: hidden;
    }

    .hero-background {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
    }

    .hero-gradient {
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at center, rgba(59, 130, 246, 0.05), var(--color-bg));
    }

    .hero-grid {
      position: absolute;
      inset: 0;
      background-image: linear-gradient(rgba(59, 130, 246, 0.02) 1px, transparent 1px),
                        linear-gradient(90deg, rgba(59, 130, 246, 0.02) 1px, transparent 1px);
      background-size: 60px 60px;
      animation: gridMove 40s linear infinite;
    }

    @keyframes gridMove {
      0% { transform: translateY(0); }
      100% { transform: translateY(60px); }
    }

    .hero-orb-1 {
      position: absolute;
      top: -100px;
      left: 5%;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: var(--color-primary);
      filter: blur(120px);
      opacity: 0.08;
    }

    .hero-orb-2 {
      position: absolute;
      bottom: -100px;
      right: 5%;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: var(--color-accent);
      filter: blur(120px);
      opacity: 0.08;
    }

    .hero-content {
      position: relative;
      z-index: 10;
      text-align: center;
      padding: 3rem 0;
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.625rem 1.25rem;
      border-radius: 9999px;
      background: rgba(59, 130, 246, 0.05);
      border: 1px solid rgba(59, 130, 246, 0.3);
      backdrop-filter: blur(8px);
      margin-bottom: 2.5rem;
    }

    .badge-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--color-primary);
      animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .badge-text {
      color: var(--color-primary);
      font-weight: 500;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.15em;
    }

    .hero-title {
      font-size: clamp(2.5rem, 8vw, 4.5rem);
      font-weight: bold;
      line-height: 1.1;
      margin-bottom: 2rem;
      letter-spacing: -0.02em;
    }

    .hero-title span {
      display: block;
    }

    .hero-title .white {
      color: white;
    }

    .hero-title .gradient {
      background: linear-gradient(to right, var(--color-primary), var(--color-accent), var(--color-warning));
      background-size: 200% 200%;
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: gradientShift 8s ease infinite;
      letter-spacing: 0.02em;
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    .hero-description {
      font-size: clamp(1rem, 2vw, 1.25rem);
      color: var(--color-muted);
      margin-bottom: 3.5rem;
      max-width: 42rem;
      margin-left: auto;
      margin-right: auto;
      line-height: 1.75;
    }

    .hero-description .highlight {
      color: var(--color-primary);
      font-weight: 500;
    }

    .hero-buttons {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1.25rem;
      margin-bottom: 5rem;
    }

    .btn {
      padding: 1rem 2rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary {
      background: linear-gradient(to right, var(--color-primary), var(--color-accent));
      color: white;
      box-shadow: 0 8px 32px -8px var(--color-primary);
    }

    .btn-primary:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 40px -8px var(--color-primary);
    }

    .btn-secondary {
      background: var(--color-surface);
      color: white;
      border: 1px solid var(--color-border);
      backdrop-filter: blur(8px);
    }

    .btn-secondary:hover {
      border-color: var(--color-primary);
      background: rgba(59, 130, 246, 0.1);
      transform: translateY(-4px);
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 1.5rem;
      width: 100%;
      margin-top: auto;
      padding-top: 3rem;
      border-top: 1px solid rgba(59, 130, 246, 0.2);
    }

    .stat-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(8px);
      padding: 2rem;
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      text-align: center;
      transition: all 0.5s ease;
    }

    .stat-card:hover {
      border-color: rgba(59, 130, 246, 0.5);
      transform: translateY(-4px);
    }

    .stat-value {
      font-size: clamp(1.875rem, 4vw, 2.5rem);
      font-weight: bold;
      color: white;
      margin-bottom: 0.75rem;
      transition: transform 0.3s ease;
    }

    .stat-card:hover .stat-value {
      transform: scale(1.1);
    }

    .stat-label {
      color: var(--color-muted);
      font-size: 0.875rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.1em;
    }

    /* SECTION HEADERS */
    .section-header {
      text-align: center;
      margin-bottom: 5rem;
    }

    .section-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.625rem 1.25rem;
      border-radius: 9999px;
      background: rgba(59, 130, 246, 0.05);
      border: 1px solid rgba(59, 130, 246, 0.3);
      backdrop-filter: blur(8px);
      margin-bottom: 2rem;
    }

    .section-title {
      font-size: clamp(2rem, 5vw, 3rem);
      font-weight: bold;
      color: white;
      margin-bottom: 1.5rem;
      letter-spacing: -0.02em;
    }

    .section-title .gradient {
      display: block;
      background: linear-gradient(to right, var(--color-primary), var(--color-accent));
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-top: 0.5rem;
    }

    .section-description {
      color: var(--color-muted);
      font-size: 1.125rem;
      max-width: 42rem;
      margin: 0 auto;
      line-height: 1.75;
    }

    /* CARDS */
    .card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(8px);
      padding: 2.5rem;
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.5s ease;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    .card h3 {
      font-size: 1.5rem;
      font-weight: bold;
      color: white;
      margin-bottom: 1.25rem;
      letter-spacing: -0.02em;
    }

    .card p {
      color: var(--color-muted);
      font-size: 1.125rem;
      line-height: 1.75;
    }

    /* GRID LAYOUTS */
    .grid-2 {
      display: grid;
      grid-template-columns: 1fr;
      gap: 3rem;
    }

    .grid-3 {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    @media (min-width: 768px) {
      .grid-3 {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (min-width: 1024px) {
      .grid-2 {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    /* ABOUT SECTION */
    .about-list {
      list-style: none;
      margin-top: 1.5rem;
    }

    .about-list li {
      display: flex;
      align-items: start;
      gap: 0.75rem;
      margin-bottom: 0.75rem;
    }

    .about-list .bullet {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--color-primary);
      margin-top: 10px;
      flex-shrink: 0;
    }

    .about-quote {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .about-avatar {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: linear-gradient(to bottom right, var(--color-primary), var(--color-accent));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.5rem;
      font-weight: bold;
      box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
      flex-shrink: 0;
    }

    .about-author h4 {
      color: white;
      font-weight: bold;
      font-size: 1.125rem;
    }

    .about-author p {
      color: var(--color-primary);
      font-size: 0.875rem;
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: 0.15em;
    }

    .about-quote-text {
      border-left: 4px solid rgba(59, 130, 246, 0.3);
      padding-left: 1.5rem;
      margin-bottom: 2rem;
    }

    .about-quote-text p {
      color: var(--color-muted);
      font-size: 1.125rem;
      font-style: italic;
      line-height: 1.75;
    }

    /* ADMISSIONS */
    .step-card {
      position: relative;
      display: flex;
      align-items: start;
      gap: 1.25rem;
      padding: 1.5rem;
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(8px);
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.5s ease;
      margin-bottom: 1.5rem;
    }

    .step-card:hover {
      border-color: var(--color-accent);
      transform: translateY(-4px);
    }

    .step-card:not(:last-child)::after {
      content: '';
      position: absolute;
      left: 30px;
      top: 100%;
      width: 2px;
      height: 24px;
      background: linear-gradient(to bottom, rgba(59, 130, 246, 0.5), transparent);
    }

    .step-number {
      flex-shrink: 0;
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: rgba(59, 130, 246, 0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--color-primary);
      font-weight: bold;
      font-size: 1.125rem;
      transition: transform 0.3s ease;
    }

    .step-card:hover .step-number {
      transform: scale(1.1);
    }

    .step-content h4 {
      font-size: 1.125rem;
      font-weight: 600;
      color: white;
      margin-bottom: 0.25rem;
      letter-spacing: 0.025em;
    }

    .step-content p {
      color: var(--color-muted);
      line-height: 1.75;
    }

    .requirement-list {
      list-style: none;
    }

    .requirement-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem;
      border-radius: 12px;
      background: rgba(26, 31, 53, 0.3);
      margin-bottom: 0.75rem;
      transition: all 0.3s ease;
    }

    .requirement-item:hover {
      background: rgba(59, 130, 246, 0.1);
    }

    .requirement-check {
      width: 24px;
      height: 24px;
      border-radius: 8px;
      background: var(--color-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: transform 0.3s ease;
    }

    .requirement-item:hover .requirement-check {
      transform: scale(1.1);
    }

    .requirement-check svg {
      width: 14px;
      height: 14px;
      stroke: white;
      fill: none;
      stroke-width: 3;
    }

    .fee-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.25rem;
      border-radius: 12px;
      background: rgba(26, 31, 53, 0.3);
      margin-bottom: 1rem;
    }

    .fee-row.highlight {
      background: rgba(96, 165, 250, 0.1);
      border: 1px solid rgba(96, 165, 250, 0.2);
    }

    .fee-label {
      color: var(--color-text);
      font-weight: 500;
    }

    .fee-amount {
      font-size: 1.25rem;
      font-weight: bold;
      color: white;
    }

    .fee-row.highlight .fee-amount {
      color: var(--color-warning);
      font-size: 1.5rem;
    }

    /* ACADEMICS */
    .academic-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(8px);
      padding: 2.5rem;
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.5s ease;
    }

    .academic-card:hover {
      transform: translateY(-8px);
    }

    .academic-card.blue {
      background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.2), rgba(34, 211, 238, 0.2));
    }

    .academic-card.purple {
      background: linear-gradient(to bottom right, rgba(139, 92, 246, 0.2), rgba(236, 72, 153, 0.2));
    }

    .academic-card.green {
      background: linear-gradient(to bottom right, rgba(34, 197, 94, 0.2), rgba(16, 185, 129, 0.2));
    }

    .academic-icon {
      color: var(--color-primary);
      margin-bottom: 1.5rem;
      transition: transform 0.3s ease;
    }

    .academic-card:hover .academic-icon {
      transform: scale(1.1);
    }

    .academic-icon svg {
      width: 40px;
      height: 40px;
      stroke-width: 1.5;
    }

    .academic-card h4 {
      font-size: 1.5rem;
      font-weight: bold;
      color: white;
      margin-bottom: 1rem;
      letter-spacing: -0.02em;
    }

    .academic-card p {
      color: var(--color-muted);
      font-size: 1.125rem;
      line-height: 1.75;
    }

    /* CONTACT */
    .contact-form {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .form-group label {
      display: block;
      color: var(--color-text);
      font-weight: 500;
      margin-bottom: 0.5rem;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 1rem;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: white;
      font-size: 1rem;
      transition: all 0.3s ease;
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
      color: var(--color-muted);
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--color-primary);
      background: rgba(255, 255, 255, 0.08);
    }

    .form-group textarea {
      resize: none;
      height: 160px;
    }

    .contact-info-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(8px);
      padding: 1.5rem;
      border-radius: 24px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 1.5rem;
      transition: all 0.5s ease;
    }

    .contact-info-card:hover {
      transform: translateY(-4px);
    }

    .contact-info-content {
      display: flex;
      align-items: start;
      gap: 1rem;
    }

    .contact-icon {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      background: linear-gradient(to bottom right, var(--color-primary), var(--color-accent));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      flex-shrink: 0;
      box-shadow: 0 4px 12px -2px rgba(59, 130, 246, 0.3);
      transition: transform 0.3s ease;
    }

    .contact-info-card:hover .contact-icon {
      transform: scale(1.1);
    }

    .contact-icon svg {
      width: 24px;
      height: 24px;
      stroke-width: 2;
    }

    .contact-info-text h4 {
      color: white;
      font-weight: bold;
      font-size: 1.125rem;
      margin-bottom: 0.5rem;
      letter-spacing: 0.025em;
    }

    .contact-info-text p {
      color: var(--color-muted);
      line-height: 1.75;
    }

    /* FOOTER */
    footer {
      padding: 3rem 1rem;
      border-top: 1px solid rgba(59, 130, 246, 0.2);
      position: relative;
    }

    footer::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to bottom, rgba(59, 130, 246, 0.02), transparent);
      pointer-events: none;
    }

    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 2rem;
      position: relative;
      z-index: 1;
    }

    .footer-logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .footer-logo-icon {
      width: 48px;
      height: 48px;
      background: linear-gradient(to bottom right, var(--color-primary), var(--color-accent));
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 1.125rem;
      color: white;
      box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }

    .footer-logo-text {
      color: white;
      font-weight: bold;
      font-size: 1.125rem;
      letter-spacing: 0.05em;
    }

    .footer-logo-subtext {
      color: var(--color-accent);
      font-size: 0.75rem;
      font-weight: 500;
      letter-spacing: 0.05em;
    }

    .footer-copyright {
      color: var(--color-muted);
      letter-spacing: 0.025em;
    }

    .footer-socials {
      display: flex;
      gap: 1rem;
    }

    .social-link {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--color-muted);
      transition: all 0.3s ease;
      text-decoration: none;
    }

    .social-link:hover {
      color: var(--color-accent);
      border-color: var(--color-accent);
      transform: translateY(-4px);
    }

    .social-link svg {
      width: 20px;
      height: 20px;
      stroke-width: 2;
    }

    /* MOBILE MENU */
    @media (max-width: 1023px) {
      nav {
        display: none;
      }
    }

    @media (min-width: 768px) {
      .footer-content {
        flex-direction: row;
        justify-content: space-between;
      }
    }

    /* UTILITY */
    .mb-2 { margin-bottom: 0.5rem; }
    .mb-4 { margin-bottom: 1rem; }
    .mb-6 { margin-bottom: 1.5rem; }
    .mb-8 { margin-bottom: 2rem; }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header id="header">
    <div class="header-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="logo">
          <div class="logo-icon">BF</div>
          <div class="logo-text">
            <h1>BRIGHT FUTURE</h1>
            <p>Primary School</p>
          </div>
        </div>

        <!-- Navigation -->
        <nav id="nav">
          <button data-section="home" class="nav-btn active">Home</button>
          <button data-section="about" class="nav-btn">About</button>
          <button data-section="admissions" class="nav-btn">Admissions</button>
          <button data-section="academics" class="nav-btn">Academics</button>
          <button data-section="contact" class="nav-btn">Contact</button>
        </nav>

        <?php if ($currentUser): ?>
        <!-- User Menu -->
        <div class="user-menu">
          <button class="user-btn" onclick="toggleDropdown()">
            <span><?php echo htmlspecialchars($currentUser['email']); ?></span>
            <svg width="12" height="12" fill="currentColor" viewBox="0 0 24 24">
              <path d="M7 10l5 5 5-5z"/>
            </svg>
          </button>
          <div class="dropdown-menu" id="dropdownMenu">
            <?php if ($auth->isAdmin()): ?>
              <a href="admin/index.php" class="dropdown-item">Admin Dashboard</a>
            <?php elseif ($auth->isTeacher()): ?>
              <a href="teacher/dashboard.php" class="dropdown-item">Teacher Dashboard</a>
            <?php elseif ($auth->isParent()): ?>
              <a href="parent/parent.php" class="dropdown-item">Parent Dashboard</a>
            <?php endif; ?>
            <a href="logout.php" class="dropdown-item">Logout</a>
          </div>
        </div>
        <?php else: ?>
        <!-- Portal Button -->
        <a href="login.php" class="portal-btn">Portal</a>
        <?php endif; ?>

      </div>
    </div>
  </header>

  <!-- HERO SECTION -->
  <section id="home">
    <div class="hero-background">
      <div class="hero-gradient"></div>
      <div class="hero-grid"></div>
      <div class="hero-orb-1"></div>
      <div class="hero-orb-2"></div>
    </div>

    <div class="container hero-content">
      <div class="badge">
        <div class="badge-dot"></div>
        <span class="badge-text">Est. 2010 • Excellence in Education</span>
      </div>

      <h1 class="hero-title">
        <span class="white">WHERE</span>
        <span class="gradient">FUTURE</span>
        <span class="white">BEGINS</span>
      </h1>

      <p class="hero-description">
        Empowering young minds through innovative education, 
        <span class="highlight">modern facilities</span>, and 
        <span class="highlight">world-class teaching</span> in Lusaka, Zambia.
      </p>

      <div class="hero-buttons">
        <button class="btn btn-primary" onclick="scrollToSection('admissions')">
          Enroll Now
        </button>
        <button class="btn btn-secondary" onclick="scrollToSection('about')">
          Discover More
        </button>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-value">500+</div>
          <div class="stat-label">Students</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">15+</div>
          <div class="stat-label">Educators</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">95%</div>
          <div class="stat-label">Success Rate</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">20+</div>
          <div class="stat-label">Awards</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT SECTION -->
  <section id="about">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">About Us</span>
        </div>
        <h2 class="section-title">
          SHAPING
          <span class="gradient">TOMORROW'S LEADERS</span>
        </h2>
      </div>

      <div class="grid-2">
        <div>
          <div class="card mb-8">
            <h3>Our Vision</h3>
            <p class="mb-8">
              To be Zambia's premier educational institution, producing confident, capable, and compassionate global citizens equipped with knowledge and values to transform their communities.
            </p>
            <ul class="about-list">
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Holistic education</span></li>
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Character development</span></li>
              <li><div class="bullet"></div><span style="color: var(--color-muted); font-size: 1.125rem;">Innovation & technology</span></li>
            </ul>
          </div>

          <div class="card">
            <h3>14 Years of Excellence</h3>
            <p>
              From a small community initiative to one of Zambia's most respected schools, our journey is marked by innovation and commitment to excellence.
            </p>
          </div>
        </div>

        <div class="card">
          <div class="about-quote">
            <div class="about-avatar">SM</div>
            <div class="about-author">
              <h4>Mrs. Sarah Mwansa</h4>
              <p>Headteacher</p>
            </div>
          </div>
          <div class="about-quote-text">
            <p>
              "Every child deserves an education that ignites curiosity, builds character, and prepares them for a bright future."
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ADMISSIONS SECTION -->
  <section id="admissions">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Admissions</span>
        </div>
        <h2 class="section-title">
          JOIN OUR
          <span class="gradient">COMMUNITY</span>
        </h2>
      </div>

      <div class="grid-2">
        <!-- Steps -->
        <div>
          <div class="step-card">
            <div class="step-number">01</div>
            <div class="step-content">
              <h4>Apply Online</h4>
              <p>Complete our digital application form</p>
            </div>
          </div>

          <div class="step-card">
            <div class="step-number">02</div>
            <div class="step-content">
              <h4>Interview</h4>
              <p>Meet with our admissions team</p>
            </div>
          </div>

          <div class="step-card">
            <div class="step-number">03</div>
            <div class="step-content">
              <h4>Documentation</h4>
              <p>Submit required documents</p>
            </div>
          </div>

          <div class="step-card">
            <div class="step-number">04</div>
            <div class="step-content">
              <h4>Registration</h4>
              <p>Pay fees & confirm enrollment</p>
            </div>
          </div>

          <div class="step-card">
            <div class="step-number">05</div>
            <div class="step-content">
              <h4>Welcome</h4>
              <p>Join the Bright Future family</p>
            </div>
          </div>
        </div>

        <!-- Requirements & Fees -->
        <div>
          <div class="card mb-8">
            <h3 class="mb-6">Requirements</h3>
            <ul class="requirement-list">
              <li class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Birth Certificate</span>
              </li>
              <li class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Previous school records</span>
              </li>
              <li class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Medical report</span>
              </li>
              <li class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Passport photos</span>
              </li>
              <li class="requirement-item">
                <div class="requirement-check">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span style="color: var(--color-text); font-weight: 500;">Guardian ID</span>
              </li>
            </ul>
          </div>

          <div class="card">
            <h3 class="mb-6">Fee Structure</h3>
            <div>
              <div class="fee-row highlight">
                <span class="fee-label">Tuition (per term)</span>
                <span class="fee-amount">K500</span>
              </div>
              <div class="fee-row">
                <span class="fee-label">Registration</span>
                <span class="fee-amount">K200</span>
              </div>
              <div class="fee-row">
                <span class="fee-label">Uniform & Supplies</span>
                <span class="fee-amount">K300</span>
              </div>
            </div>
            <a href="application.php" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; text-align: center; display: block; text-decoration: none;">
              Start Application
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ACADEMICS SECTION -->
  <section id="academics">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Academics</span>
        </div>
        <h2 class="section-title">
          EXCEPTIONAL
          <span class="gradient">LEARNING</span>
        </h2>
        <p class="section-description">
          Our curriculum combines academics, arts, and technology to prepare students for a rapidly changing world.
        </p>
      </div>

      <div class="grid-3">
        <div class="academic-card blue">
          <div class="academic-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <h4>STEM Programs</h4>
          <p>Mathematics, Science, and Technology integrated learning</p>
        </div>

        <div class="academic-card purple">
          <div class="academic-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
          </div>
          <h4>Arts & Culture</h4>
          <p>Music, Drama, Visual Arts & Cultural events</p>
        </div>

        <div class="academic-card green">
          <div class="academic-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h4>Sports & Wellness</h4>
          <p>Football, Athletics, and Physical Education</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CONTACT SECTION -->
  <section id="contact">
    <div class="container">
      <div class="section-header">
        <div class="section-badge">
          <div class="badge-dot"></div>
          <span class="badge-text">Contact Us</span>
        </div>
        <h2 class="section-title">
          GET IN
          <span class="gradient">TOUCH</span>
        </h2>
        <p class="section-description">
          Have questions? Reach out and we'll guide you through everything you need to know about Bright Future School.
        </p>
      </div>

      <div class="grid-2">
        <div class="card">
          <form class="contact-form" onsubmit="handleFormSubmit(event)">
            <div class="form-group">
              <input type="text" placeholder="Your Name" required>
            </div>
            <div class="form-group">
              <input type="email" placeholder="Your Email" required>
            </div>
            <div class="form-group">
              <textarea placeholder="Your Message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
              Send Message
            </button>
          </form>
        </div>

        <div>
          <div class="contact-info-card">
            <div class="contact-info-content">
              <div class="contact-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="contact-info-text">
                <h4>Address</h4>
                <p>1234 Freedom Road, Lusaka, Zambia</p>
              </div>
            </div>
          </div>

          <div class="contact-info-card">
            <div class="contact-info-content">
              <div class="contact-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <div class="contact-info-text">
                <h4>Phone</h4>
                <p>+260 123 456 789</p>
              </div>
            </div>
          </div>

          <div class="contact-info-card">
            <div class="contact-info-content">
              <div class="contact-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div class="contact-info-text">
                <h4>Email</h4>
                <p>info@brightfuture.ac.zm</p>
              </div>
            </div>
          </div>
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

  <script src="/public/js/api.js"></script>
  <script src="/public/js/main.js"></script>
  <script>
    // Toggle dropdown menu
    function toggleDropdown() {
      const dropdown = document.getElementById('dropdownMenu');
      dropdown.classList.toggle('show');
    }

    // Close dropdown when clicking outside
    window.onclick = function(event) {
      if (!event.target.matches('.user-btn') && !event.target.closest('.user-menu')) {
        const dropdown = document.getElementById('dropdownMenu');
        if (dropdown.classList.contains('show')) {
          dropdown.classList.remove('show');
        }
      }
    };

    // Active section tracking
    let activeSection = 'home';

    // Scroll-based header styling
    window.addEventListener('scroll', () => {
      const header = document.getElementById('header');
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
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
    document.querySelectorAll('.nav-btn').forEach(button => {
      button.addEventListener('click', () => {
        const sectionId = button.dataset.section;
        scrollToSection(sectionId);
      });
    });

    // Form submission handler
    function handleFormSubmit(event) {
      event.preventDefault();
      alert('Thank you for your message! We will get back to you soon.');
      event.target.reset();
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      updateActiveSection();
    });
  </script>

</body>
</html>