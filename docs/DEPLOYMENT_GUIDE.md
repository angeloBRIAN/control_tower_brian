# Control Tower - LAMP Server Deployment Guide

## Prerequisites

**Server Requirements:**
- PHP 8.1+ with extensions: `mbstring`, `xml`, `bcmath`, `curl`, `mysql`, `zip`, `gd`
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ with `mod_rewrite` enabled
- Composer 2.x
- Node.js 18+ & npm (for asset compilation)

---

## Deployment Steps

### 1. Prepare Server

```bash
# Install required PHP extensions
sudo apt install php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl php8.2-mysql php8.2-zip php8.2-gd

# Enable Apache mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Install Composer (if not installed)
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE control_tower CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'control_tower'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON control_tower.* TO 'control_tower'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Upload Application

**Option A: Git Clone (Recommended)**
```bash
cd /var/www
sudo git clone <your-repo-url> control_tower
cd control_tower
```

**Option B: Upload via FTP/SCP**
```bash
# From development machine
scp -r /home/yudi/dev/control_tower/control_tower_app user@server:/var/www/control_tower
```

### 4. Set Permissions

```bash
cd /var/www/control_tower

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type f -exec chmod 644 {} \;
sudo find . -type d -exec chmod 755 {} \;

# Storage & cache must be writable
sudo chmod -R 775 storage bootstrap/cache
```

### 5. Install Dependencies

```bash
cd /var/www/control_tower

# Install PHP dependencies (production mode)
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm install
npm run build
```

### 6. Configure Environment

```bash
# Copy example env
cp .env.example .env

# Edit environment file
nano .env
```

**Update these values in `.env`:**
```env
APP_NAME="Control Tower"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=control_tower
DB_USERNAME=control_tower
DB_PASSWORD=your_secure_password

# Optional: LDAP settings if using
LDAP_ENABLED=false
```

### 7. Generate Key & Run Migrations

```bash
# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link

# Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Create Admin User

```bash
php artisan tinker
```

```php
use App\Models\User;
User::create([
    'name' => 'Administrator',
    'username' => 'admin',
    'password' => bcrypt('your_password'),
    'role' => 'admin',
]);
exit
```

### 9. Configure Apache Virtual Host

```bash
sudo nano /etc/apache2/sites-available/control_tower.conf
```

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/control_tower/public
    
    <Directory /var/www/control_tower/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/control_tower_error.log
    CustomLog ${APACHE_LOG_DIR}/control_tower_access.log combined
</VirtualHost>
```

```bash
# Enable site
sudo a2ensite control_tower.conf
sudo systemctl reload apache2
```

### 10. (Optional) Enable HTTPS with Let's Encrypt

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

---

## Quick Deployment Checklist

- [ ] PHP 8.1+ with required extensions
- [ ] MySQL database created
- [ ] Application files uploaded
- [ ] Correct file permissions set
- [ ] Composer dependencies installed
- [ ] npm build completed
- [ ] `.env` configured
- [ ] `php artisan key:generate`
- [ ] `php artisan migrate --force`
- [ ] `php artisan storage:link`
- [ ] Cache cleared and rebuilt
- [ ] Apache virtual host configured
- [ ] Admin user created

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 500 Error | Check `storage/logs/laravel.log` |
| Blank page | Enable APP_DEBUG=true temporarily |
| Permission denied | `chmod -R 775 storage bootstrap/cache` |
| Assets not loading | Run `npm run build` and check APP_URL |
| Database errors | Verify DB credentials in .env |

---

## Updating the Application

```bash
cd /var/www/control_tower

# Pull latest code (if using git)
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Run new migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
