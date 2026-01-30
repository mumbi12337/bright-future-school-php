# Bright Future School - Deployment Guide

This guide will help you deploy your PHP school management system online.

## 📋 Project Overview

**Project**: Bright Future School Management System  
**Technology Stack**: PHP 8+, PostgreSQL, HTML/CSS/JavaScript  
**Type**: School administration and management web application

## 🚀 Deployment Options

### Option 1: Shared Hosting (Recommended for beginners)
**Providers**: Bluehost, HostGator, GoDaddy, Namecheap
**Best for**: Small to medium schools with budget constraints

### Option 2: VPS/Dedicated Server
**Providers**: DigitalOcean, Linode, AWS EC2, Google Cloud
**Best for**: Larger schools or those needing full control

### Option 3: Cloud Platform
**Providers**: Heroku, Railway, Render
**Best for**: Quick deployment and testing

## 🔧 Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] Updated database credentials in `includes/db.php`
- [ ] Set up your domain name
- [ ] Created a PostgreSQL database
- [ ] Uploaded all project files
- [ ] Set proper file permissions
- [ ] Configured SSL certificate

## 📁 Files to Upload

Upload these directories and files to your web server:

```
├── admin/           # Admin dashboard
├── api/            # API endpoints
├── database/       # Database schema
├── includes/       # Core includes
├── models/         # Data models
├── parent/         # Parent portal
├── public/         # CSS/JS assets
├── teacher/        # Teacher portal
├── index.php       # Main homepage
├── login.php       # Login page
├── logout.php      # Logout functionality
└── setup-database.php  # Database setup script
```

**Exclude these files for security:**
- `test-*.php` files
- `debug_*.php` files
- `*.md` documentation files (optional)
- Development configuration files

## 🛠️ Step-by-Step Deployment

### Step 1: Choose Your Hosting Provider

#### Shared Hosting (Easiest)
1. Sign up with a provider (Bluehost recommended for beginners)
2. Purchase hosting plan
3. Register/transfer your domain
4. Access cPanel

#### VPS/Dedicated Server
1. Choose provider (DigitalOcean recommended)
2. Create server instance
3. Install LAMP/LEMP stack
4. Configure domain DNS

### Step 2: Database Setup

#### For Shared Hosting:
1. Log into cPanel
2. Go to PostgreSQL Databases
3. Create new database named `bright`
4. Create database user
5. Assign user to database

#### For VPS:
```bash
# Connect to your server
ssh root@your-server-ip

# Install PostgreSQL
sudo apt update
sudo apt install postgresql postgresql-contrib

# Create database and user
sudo -u postgres psql
CREATE DATABASE bright;
CREATE USER your_username WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE bright TO your_username;
\q
```

### Step 3: Update Configuration

Edit `includes/db.php` with your production credentials:

```php
<?php
// Production database configuration
define('DB_HOST', 'your-database-host');     // Usually localhost for shared hosting
define('DB_PORT', '5432');                   // PostgreSQL port
define('DB_NAME', 'bright');                 // Your database name
define('DB_USER', 'your_database_user');     // Your database username
define('DB_PASS', 'your_secure_password');   // Your database password
?>
```

### Step 4: Upload Files

#### Using FTP (Shared Hosting):
1. Download FileZilla or use cPanel File Manager
2. Connect to your hosting server
3. Upload all project files to `public_html` or `www` directory
4. Ensure directory structure is maintained

#### Using Git (VPS/Advanced):
```bash
# On your server
cd /var/www/html
git clone https://github.com/your-repo/bright-future-school.git .
```

### Step 5: Set File Permissions

```bash
# Set proper permissions (run on server)
find /path/to/your/project -type d -exec chmod 755 {} \;
find /path/to/your/project -type f -exec chmod 644 {} \;
chmod 600 includes/db.php  # Secure database config
```

### Step 6: Initialize Database

1. Visit `https://yourdomain.com/setup-database.php`
2. This will create all required tables
3. Delete or rename this file after setup for security

### Step 7: Create Admin User

1. Visit `https://yourdomain.com/create-user.php`
2. Create your admin account
3. Test login functionality

### Step 8: SSL Certificate (HTTPS)

#### Free SSL with Let's Encrypt:
```bash
# For VPS servers
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com
```

#### Shared Hosting:
Most providers offer free SSL certificates through cPanel

## 🔒 Security Configuration

### Essential Security Measures:

1. **Rename/Delete Setup Files:**
   ```bash
   # After initial setup
   mv setup-database.php setup-database.php.bak
   mv create-user.php create-user.php.bak
   ```

2. **Secure Database Configuration:**
   - Move `db.php` outside web root if possible
   - Use strong database passwords
   - Limit database user privileges

3. **File Permissions:**
   - 755 for directories
   - 644 for files
   - 600 for sensitive config files

4. **Hide Sensitive Information:**
   Create `.htaccess` in project root:
   ```apache
   # Prevent access to sensitive files
   <Files "includes/db.php">
       Order allow,deny
       Deny from all
   </Files>
   
   <FilesMatch "\.(md|log|bak|tmp)$">
       Order allow,deny
       Deny from all
   </FilesMatch>
   ```

## 🌐 Domain Configuration

### DNS Settings:
- **A Record**: Point to your server IP
- **CNAME**: www → yourdomain.com
- **MX Records**: For email (if needed)

### URL Rewriting (Optional):
Create `.htaccess` for clean URLs:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

## 📊 Monitoring and Maintenance

### Essential Monitoring:
- Website uptime monitoring
- Database backup automation
- Error log monitoring
- Performance monitoring

### Regular Maintenance:
- Update PHP and dependencies
- Backup database weekly
- Monitor disk space
- Check security updates

## 🆘 Troubleshooting Common Issues

### Database Connection Failed:
1. Verify database credentials in `db.php`
2. Check if PostgreSQL service is running
3. Ensure database user has proper permissions
4. Test connection with a simple PHP script

### 500 Internal Server Error:
1. Check server error logs
2. Verify PHP version compatibility
3. Check file permissions
4. Review `.htaccess` syntax

### Pages Not Loading:
1. Check file paths in includes
2. Verify web server document root
3. Ensure all files uploaded correctly
4. Check for missing dependencies

## 💡 Performance Optimization

### Caching:
1. Enable OPcache for PHP
2. Use browser caching headers
3. Minify CSS/JS files
4. Optimize images

### Database Optimization:
1. Add database indexes
2. Regular database maintenance
3. Query optimization
4. Connection pooling

## 📞 Support Resources

### Hosting Provider Support:
- Most shared hosts offer 24/7 support
- Check knowledge base for PHP/PostgreSQL guides

### Community Resources:
- Stack Overflow for coding issues
- PHP.net documentation
- PostgreSQL documentation

### Professional Help:
- Consider hiring a system administrator
- Managed hosting services
- Professional deployment services

## ✅ Final Checklist

Before going live:
- [ ] All pages load correctly
- [ ] Login/authentication works
- [ ] Database operations function
- [ ] SSL certificate installed
- [ ] Security measures implemented
- [ ] Backup system configured
- [ ] Monitoring in place
- [ ] Contact information updated
- [ ] Test all user roles (admin, teacher, parent)
- [ ] Verify all forms submit correctly

## 🚀 Post-Deployment

### Essential Next Steps:
1. Set up automated backups
2. Configure monitoring alerts
3. Test all functionality thoroughly
4. Create user documentation
5. Set up analytics (Google Analytics)
6. Configure email notifications
7. Create emergency contact procedures

---

**Need Help?** Refer to your hosting provider's documentation or contact their support team for specific server configurations.

**Security Reminder**: Always keep your software updated and monitor your site regularly for security issues.