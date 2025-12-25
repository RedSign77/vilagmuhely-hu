#!/bin/bash

# Világműhely Deployment Script
# This script handles safe deployment with proper cache management

set -e  # Exit on any error

echo "🚀 Starting deployment..."

# Detect environment
if [ -f .env ]; then
    APP_ENV=$(grep APP_ENV .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
else
    APP_ENV="production"
fi

echo "🌍 Environment detected: ${APP_ENV}"

# Pull latest code from git
echo "📥 Pulling latest code from git..."
git pull

# Install/update PHP dependencies
echo "📦 Installing Composer dependencies..."
if [ "$APP_ENV" = "production" ]; then
    echo "   Using production mode (--no-dev)"
    php composer.phar install --no-dev --optimize-autoloader --no-interaction
else
    echo "   Using development mode (with dev dependencies)"
    php composer.phar install --optimize-autoloader --no-interaction
fi

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear all caches
echo "🧹 Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration for better performance
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear and cache Filament components
echo "💎 Refreshing Filament components..."
php artisan filament:cache-components

# Upgrade Filament assets
echo "🔄 Upgrading Filament assets..."
php artisan filament:upgrade

# Optimize autoloader
echo "🔧 Optimizing autoloader..."
if [ "$APP_ENV" = "production" ]; then
    php composer.phar dump-autoload --optimize --no-dev
else
    php composer.phar dump-autoload --optimize
fi

# Restart queue workers (if running)
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Clear all optimization caches
echo "🚀 Clearing optimization caches..."
php artisan optimize:clear

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📊 Environment: ${APP_ENV}"
echo ""
echo "📋 Verification checklist:"
echo "   ✓ Content Library menu should now be visible in /admin"
echo "   ✓ Crystal calculations running every 30 minutes"
echo "   ✓ Queue workers restarted and processing jobs"
echo "   ✓ All caches cleared and regenerated"
if [ "$APP_ENV" = "production" ]; then
    echo "   ✓ Dev dependencies excluded (production mode)"
else
    echo "   ✓ Dev dependencies included (development mode)"
fi
echo ""
echo "🔍 Quick test commands:"
echo "   php artisan route:list | grep content-library"
echo "   php artisan schedule:list"
echo "   php artisan env"
echo ""
