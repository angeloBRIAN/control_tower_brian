#!/bin/bash

#############################################
# Control Tower - Package & Deploy to Portainer
# Usage: ./package-and-deploy.sh
#############################################

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration - EDIT THESE
SERVER_IP="192.168.99.123"
SERVER_USER="administrator"  # Change to your SSH user
REMOTE_PATH="/opt/stacks/control_tower"
LOCAL_PATH="/home/yudi/dev/control_tower/control_tower_app"

echo -e "${CYAN}========================================${NC}"
echo -e "${CYAN}  Control Tower - Portainer Deployer${NC}"
echo -e "${CYAN}========================================${NC}"

# Check if we're in the right directory
if [ ! -f "$LOCAL_PATH/artisan" ]; then
    echo -e "${RED}Error: Not a Laravel project directory${NC}"
    exit 1
fi

cd "$LOCAL_PATH"

echo -e "\n${YELLOW}[1/6] Preparing files...${NC}"
# Create fresh .env for production
if [ ! -f ".env.production" ]; then
    cp .env.example .env.production 2>/dev/null || echo "No .env.example found"
fi

echo -e "\n${YELLOW}[2/6] Creating deployment package...${NC}"
# Create package excluding unnecessary files
cd ..
tar -czvf control_tower_deploy.tar.gz \
    --exclude='control_tower_app/node_modules' \
    --exclude='control_tower_app/vendor' \
    --exclude='control_tower_app/.git' \
    --exclude='control_tower_app/storage/logs/*' \
    --exclude='control_tower_app/storage/framework/cache/*' \
    --exclude='control_tower_app/storage/framework/sessions/*' \
    --exclude='control_tower_app/storage/framework/views/*' \
    control_tower_app

PACKAGE_SIZE=$(du -h control_tower_deploy.tar.gz | cut -f1)
echo -e "${GREEN}Package created: control_tower_deploy.tar.gz (${PACKAGE_SIZE})${NC}"

echo -e "\n${YELLOW}[3/6] Transferring to server ${SERVER_IP}...${NC}"
echo -e "${CYAN}You may be prompted for SSH password${NC}"

# Create remote directory with sudo
ssh ${SERVER_USER}@${SERVER_IP} "sudo mkdir -p ${REMOTE_PATH} && sudo chown ${SERVER_USER}:${SERVER_USER} ${REMOTE_PATH}"

# Transfer package
scp control_tower_deploy.tar.gz ${SERVER_USER}@${SERVER_IP}:${REMOTE_PATH}/

echo -e "\n${YELLOW}[4/6] Extracting on server...${NC}"
ssh ${SERVER_USER}@${SERVER_IP} << REMOTE_SCRIPT
    cd ${REMOTE_PATH}
    tar -xzf control_tower_deploy.tar.gz --strip-components=1
    rm control_tower_deploy.tar.gz
    
    # Set permissions
    chmod -R 755 .
    chmod -R 777 storage bootstrap/cache
REMOTE_SCRIPT

echo -e "\n${YELLOW}[5/6] Cleanup local package...${NC}"
rm -f control_tower_deploy.tar.gz

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}  Transfer Complete!${NC}"
echo -e "${GREEN}========================================${NC}"

echo -e "\n${CYAN}Next Steps:${NC}"
echo -e "1. Open Portainer: ${YELLOW}http://${SERVER_IP}:9000${NC}"
echo -e "2. Go to Stacks → Add Stack"
echo -e "3. Name: control_tower"
echo -e "4. Build method: Repository (path: ${REMOTE_PATH})"
echo -e "5. Or use Web Editor and paste docker-compose.yml content"
echo -e "6. Click Deploy"
echo -e ""
echo -e "${CYAN}After deployment, run in container console:${NC}"
echo -e "   php artisan key:generate --force"
echo -e "   php artisan migrate --force"
echo -e "   php artisan storage:link"
echo -e ""
echo -e "${CYAN}App will be at:${NC} ${YELLOW}http://${SERVER_IP}:8080${NC}"
