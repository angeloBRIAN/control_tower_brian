#!/bin/bash

#############################################
# Control Tower - Deployment Script
# Usage: ./deploy.sh [fresh|update]
#############################################

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
APP_DIR="/var/www/control_tower"
WEB_USER="www-data"
WEB_GROUP="www-data"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Control Tower Deployment Script${NC}"
echo -e "${GREEN}========================================${NC}"

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run with sudo or as root${NC}"
    exit 1
fi

# Navigate to app directory
if [ ! -d "$APP_DIR" ]; then
    echo -e "${RED}App directory not found: $APP_DIR${NC}"
    echo -e "${YELLOW}Please upload the application first.${NC}"
    exit 1
fi

cd "$APP_DIR"

echo -e "\n${YELLOW}[1/8] Pulling latest code...${NC}"
if [ -d ".git" ]; then
    git pull origin main || git pull origin master
else
    echo -e "${YELLOW}No git repository found. Skipping git pull.${NC}"
fi

echo -e "\n${YELLOW}[2/8] Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "\n${YELLOW}[3/8] Installing npm dependencies and building assets...${NC}"
if [ -f "package.json" ]; then
    npm install
    npm run build
else
    echo -e "${YELLOW}No package.json found. Skipping npm build.${NC}"
fi

echo -e "\n${YELLOW}[4/8] Running database migrations...${NC}"
php artisan migrate --force

echo -e "\n${YELLOW}[5/8] Creating storage link...${NC}"
php artisan storage:link 2>/dev/null || echo "Storage link already exists."

echo -e "\n${YELLOW}[6/8] Clearing and caching configuration...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "\n${YELLOW}[7/8] Setting file permissions...${NC}"
chown -R $WEB_USER:$WEB_GROUP .
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache

echo -e "\n${YELLOW}[8/8] Restarting services...${NC}"
systemctl reload apache2 2>/dev/null || systemctl reload nginx 2>/dev/null || echo "Web server reload skipped."

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "App URL: $(grep APP_URL .env | cut -d '=' -f2)"
echo -e "\n${YELLOW}Remember to check storage/logs/laravel.log for any errors.${NC}"
