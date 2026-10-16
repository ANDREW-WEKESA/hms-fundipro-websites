# Deployment Guide

## Prerequisites

- Web server (Apache/Nginx)
- PHP 7.4+ (for contact forms - future)
- SSL certificate
- Domain names configured

## Deployment Steps

### 1. Server Setup

```bash
# For Apache
sudo apt update
sudo apt install apache2
sudo systemctl enable apache2

# Enable required modules
sudo a2enmod rewrite
sudo a2enmod expires
sudo a2enmod deflate
```

### 2. Upload Files

```bash
# Upload to server
scp -r hms/ user@server:/var/www/hms.co.ke/
scp -r fundipro/ user@server:/var/www/fundipro.co.ke/
```

### 3. Configure Virtual Hosts

#### HMS Virtual Host
```apache
<VirtualHost *:80>
    ServerName hms.co.ke
    ServerAlias www.hms.co.ke
    DocumentRoot /var/www/hms.co.ke
    
    <Directory /var/www/hms.co.ke>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/hms-error.log
    CustomLog ${APACHE_LOG_DIR}/hms-access.log combined
</VirtualHost>
```

#### FundiPro Virtual Host
```apache
<VirtualHost *:80>
    ServerName fundipro.co.ke
    ServerAlias www.fundipro.co.ke
    DocumentRoot /var/www/fundipro.co.ke
    
    <Directory /var/www/fundipro.co.ke>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/fundipro-error.log
    CustomLog ${APACHE_LOG_DIR}/fundipro-access.log combined
</VirtualHost>
```

### 4. SSL Configuration (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificates
sudo certbot --apache -d hms.co.ke -d www.hms.co.ke
sudo certbot --apache -d fundipro.co.ke -d www.fundipro.co.ke

# Auto-renewal
sudo certbot renew --dry-run
```

### 5. Performance Optimization

```bash
# Enable caching
sudo a2enmod cache
sudo a2enmod cache_disk

# Restart Apache
sudo systemctl restart apache2
```

## Environment-Specific Configuration

### Production
- Enable HTTPS redirect
- Minify CSS/JS
- Enable browser caching
- Configure CDN (optional)

### Staging
- Use subdomain (staging.hms.co.ke)
- Keep development features enabled
- Test all forms and features

## Post-Deployment Checklist

- [ ] SSL certificates installed and working
- [ ] All pages load correctly
- [ ] Forms submit successfully
- [ ] Mobile responsive on real devices
- [ ] Google Maps showing correct location
- [ ] Contact forms sending emails
- [ ] Analytics tracking configured
- [ ] Sitemap submitted to Google Search Console
- [ ] Robots.txt accessible
- [ ] 404 pages working
- [ ] Performance tested (GTmetrix, PageSpeed)

## Monitoring

### Tools
- Google Analytics
- Google Search Console
- Uptime monitoring (UptimeRobot)
- Error tracking (Sentry)

### Logs
```bash
# Check error logs
tail -f /var/log/apache2/hms-error.log
tail -f /var/log/apache2/fundipro-error.log
```

## Backup Strategy

- Daily automated backups
- Store off-site (AWS S3, DigitalOcean Spaces)
- Test restore quarterly
- Keep 30 days of backups

## Rollback Plan

```bash
# Backup current version
cp -r /var/www/hms.co.ke /var/www/hms.co.ke.backup

# Restore previous version if needed
rm -rf /var/www/hms.co.ke
mv /var/www/hms.co.ke.backup /var/www/hms.co.ke
```

## Support

For deployment issues, contact: andrewwekesa675@gmail.com
