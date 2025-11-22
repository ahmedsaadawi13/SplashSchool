# SplashSchool Deployment Guide

## Pre-Deployment Checklist

- [ ] All code tested locally
- [ ] Database schema finalized
- [ ] Environment variables configured
- [ ] Security review completed
- [ ] Backup strategy in place
- [ ] Domain name configured
- [ ] SSL certificate obtained

## Server Requirements

### Minimum Requirements
- **OS**: Ubuntu 20.04 LTS or CentOS 7/8
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **RAM**: 2GB minimum (4GB recommended)
- **Disk Space**: 20GB minimum
- **CPU**: 2 cores minimum

### PHP Extensions
```bash
# Ubuntu/Debian
sudo apt-get install php-mysql php-mbstring php-json php-gd php-curl php-zip php-xml

# CentOS/RHEL
sudo yum install php-mysql php-mbstring php-json php-gd php-curl php-zip php-xml
```

## Step-by-Step Deployment

### 1. Prepare the Server

#### Update System
```bash
sudo apt update && sudo apt upgrade -y
```

#### Install Required Packages
```bash
# Apache, PHP, MySQL
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql -y

# PHP extensions
sudo apt install php-mbstring php-json php-gd php-curl php-zip php-xml -y
```

### 2. Configure MySQL

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE splashschool_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'splashschool_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON splashschool_db.* TO 'splashschool_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Upload Application Files

#### Using Git
```bash
cd /var/www/
sudo git clone https://github.com/yourusername/SplashSchool.git
sudo chown -R www-data:www-data SplashSchool
```

#### Using SCP/SFTP
```bash
scp -r SplashSchool user@server:/var/www/
```

### 4. Configure Environment

```bash
cd /var/www/SplashSchool
sudo cp .env.example .env
sudo nano .env
```

Update `.env`:
```
APP_ENV=production
DB_HOST=localhost
DB_NAME=splashschool_db
DB_USER=splashschool_user
DB_PASS=your_strong_password
APP_URL=https://yourdomain.com
ENCRYPTION_KEY=generate-32-character-random-key
```

### 5. Import Database

```bash
mysql -u splashschool_user -p splashschool_db < /var/www/SplashSchool/database.sql
```

### 6. Set Permissions

```bash
cd /var/www/SplashSchool
sudo chown -R www-data:www-data storage
sudo chmod -R 755 storage
sudo chmod -R 755 storage/uploads
sudo chmod -R 755 storage/logs
sudo chmod 644 .env
```

### 7. Configure Apache

Create virtual host:
```bash
sudo nano /etc/apache2/sites-available/splashschool.conf
```

Add configuration:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/SplashSchool/public

    <Directory /var/www/SplashSchool/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/SplashSchool/storage>
        Require all denied
    </Directory>

    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/splashschool-error.log
    CustomLog ${APACHE_LOG_DIR}/splashschool-access.log combined
</VirtualHost>
```

Enable site and modules:
```bash
sudo a2ensite splashschool.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 8. Configure SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtain certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### 9. Configure PHP for Production

Edit PHP configuration:
```bash
sudo nano /etc/php/7.4/apache2/php.ini
```

Update settings:
```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
session.cookie_httponly = 1
session.cookie_secure = 1
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### 10. Enable OPcache (Performance)

```bash
sudo nano /etc/php/7.4/apache2/conf.d/10-opcache.ini
```

Add:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### 11. Configure Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable

# Check status
sudo ufw status
```

### 12. Setup Cron Jobs (Optional)

```bash
sudo crontab -e
```

Add:
```cron
# Send queued notifications every hour
0 * * * * cd /var/www/SplashSchool && php cli/send_notifications.php

# Update tenant usage daily
0 2 * * * cd /var/www/SplashSchool && php cli/update_usage.php

# Backup database daily
0 3 * * * mysqldump -u splashschool_user -p'password' splashschool_db | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz
```

## Nginx Configuration (Alternative)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/SplashSchool/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.env {
        deny all;
    }

    location ^~ /storage/ {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable SSL:
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

## Security Hardening

### 1. Disable Directory Listing
Already configured in Apache/Nginx

### 2. Hide PHP Version
```bash
sudo nano /etc/php/7.4/apache2/php.ini
```
Set:
```ini
expose_php = Off
```

### 3. Restrict File Permissions
```bash
find /var/www/SplashSchool -type d -exec chmod 755 {} \;
find /var/www/SplashSchool -type f -exec chmod 644 {} \;
chmod 755 /var/www/SplashSchool/storage
```

### 4. Install Fail2Ban
```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 5. Change Default Passwords
- [ ] Change platform admin password
- [ ] Change demo school admin password
- [ ] Update database user password

## Monitoring & Maintenance

### 1. Setup Log Rotation

```bash
sudo nano /etc/logrotate.d/splashschool
```

Add:
```
/var/www/SplashSchool/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
}
```

### 2. Monitor Disk Space

```bash
# Check disk usage
df -h

# Check storage directory size
du -sh /var/www/SplashSchool/storage/uploads
```

### 3. Database Optimization

```bash
# Weekly optimization
mysqlcheck -u splashschool_user -p --optimize --all-databases
```

### 4. Application Monitoring

Setup monitoring for:
- Uptime monitoring (UptimeRobot, Pingdom)
- Error logging (Sentry, Rollbar)
- Performance monitoring (New Relic, DataDog)

## Backup Strategy

### Database Backup

```bash
#!/bin/bash
# backup.sh
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backups/splashschool"
DB_USER="splashschool_user"
DB_PASS="your_password"
DB_NAME="splashschool_db"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$TIMESTAMP.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$TIMESTAMP.tar.gz /var/www/SplashSchool/storage/uploads

# Keep only last 30 days
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $TIMESTAMP"
```

Make executable:
```bash
chmod +x /usr/local/bin/backup.sh
```

Schedule in crontab:
```cron
0 2 * * * /usr/local/bin/backup.sh >> /var/log/splashschool-backup.log 2>&1
```

## Rollback Procedure

### 1. Restore Database
```bash
gunzip < /backups/db_20250115_020000.sql.gz | mysql -u splashschool_user -p splashschool_db
```

### 2. Restore Files
```bash
tar -xzf /backups/files_20250115_020000.tar.gz -C /
```

## Performance Optimization

### 1. Enable Gzip Compression

Apache:
```bash
sudo a2enmod deflate
sudo systemctl restart apache2
```

Nginx (add to server block):
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
```

### 2. Browser Caching

Already configured in Nginx example above.

For Apache, add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 3. MySQL Tuning

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Add:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 32M
query_cache_limit = 2M
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

## Post-Deployment Verification

- [ ] Homepage loads correctly
- [ ] Login works
- [ ] Dashboard displays
- [ ] Student CRUD operations work
- [ ] File uploads work
- [ ] API endpoints respond
- [ ] SSL certificate valid
- [ ] Logs being written
- [ ] Backups running
- [ ] Email notifications working (if configured)

## Troubleshooting

### Issue: 500 Internal Server Error
- Check Apache/Nginx error logs
- Verify file permissions
- Check PHP error log
- Ensure .htaccess is correct

### Issue: Database Connection Failed
- Verify database credentials in `.env`
- Check MySQL service is running
- Verify database user permissions

### Issue: File Uploads Failing
- Check storage directory permissions
- Verify PHP upload settings
- Check disk space

---

**Deployment Date**: _________________
**Deployed By**: _________________
**Server**: _________________
