#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# --- Configuration ---
# Set to true to enable maintenance mode during update
ENABLE_MAINTENANCE_MODE=true
PHP_VERSION="8.4" # Ensure this matches your installed PHP version

# --- Helper Functions ---
print_success() {
    echo ""
    echo -e "\033[0;32m✅ $1\033[0m"
    echo ""
}

print_warning() {
    echo ""
    echo -e "\033[0;33m⚠️ $1\033[0m"
    echo ""
}

print_error() {
    echo ""
    echo -e "\033[0;31m❌ ERROR: $1\033[0m"
    echo ""
    exit 1
}

# --- Script Start ---
echo "🚀 Starting Laravel Application Update..."

# Check if inside a Git repository and potentially a Laravel project
if [ ! -d ".git" ] || [ ! -f "artisan" ]; then
    print_error "This script must be run from the root directory of your Laravel application."
fi

# Ensure PHP command is available
if ! command -v php &> /dev/null; then
    print_error "PHP command not found. Is PHP installed and in your PATH?"
fi

# --- 1. Maintenance Mode (Optional) ---
if [ "$ENABLE_MAINTENANCE_MODE" = true ]; then
    echo "🔒 Enabling maintenance mode..."
    php artisan down || print_warning "Failed to enable maintenance mode. Continuing..."
fi

# --- 2. Update Application Code ---
echo "🔄 Pulling latest changes from Git repository..."
git pull

# --- 3. Install/Update PHP Dependencies ---
echo "🎼 Installing/updating Composer dependencies..."
# Use --no-interaction for non-interactive environments
# Use --no-dev for production to skip development dependencies
# Use --optimize-autoloader to build a more performant class autoloader
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# --- 4. Run Database Migrations ---
echo "🗄️ Running database migrations..."
# Use --force for non-interactive execution in production
php artisan migrate --force

# --- 5. Clear and Cache Configurations ---
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan event:clear
php artisan route:clear
php artisan view:clear

echo "📦 Caching configurations for production..."
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

# --- 6. Build Frontend Assets (if applicable) ---
if [ -f "package.json" ]; then
    echo "🌐 Found package.json. Installing npm dependencies and building assets..."
    # Check for npm and node
    if ! command -v npm &> /dev/null || ! command -v node &> /dev/null; then
        print_warning "npm or Node.js not found. Skipping frontend build. Please install them."
    else
        npm install
        npm run build # Or 'prod', 'production' depending on your setup
    fi
else
    echo "No package.json found. Skipping frontend build."
fi

# --- 7. Restart PHP-FPM (Good practice after updates) ---
echo "🔄 Restarting PHP ${PHP_VERSION}-FPM service..."
# Check if systemctl is available
if command -v systemctl &> /dev/null; then
    # Check if the service exists before trying to restart
    if systemctl list-units --full -all | grep -Fq "php${PHP_VERSION}-fpm.service"; then
        sudo systemctl restart "php${PHP_VERSION}-fpm"
    else
        print_warning "PHP-FPM service (php${PHP_VERSION}-fpm) not found. Skipping restart."
    fi
else
    print_warning "systemctl not found. Cannot restart PHP-FPM. You might need to do this manually."
fi


# --- 8. Exit Maintenance Mode (Optional) ---
if [ "$ENABLE_MAINTENANCE_MODE" = true ]; then
    echo "🔓 Disabling maintenance mode..."
    php artisan up
fi

print_success "Laravel application update script finished!"
echo "------------------------------------------------------------------"
echo "✅ Your application has been updated."
echo "   Remember to check your application's logs for any runtime errors."
echo "------------------------------------------------------------------"
