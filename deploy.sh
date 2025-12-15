#!/bin/bash

# Világműhely Production Deployment Script (Docker)
# This script handles safe deployment with proper cache management

set -e  # Exit on any error

echo "🚀 Starting deployment..."

# Pull latest code from git
echo "📥 Pulling latest code from git..."
git pull

# Install/update PHP dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

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
composer dump-autoload --optimize --no-dev

# Restart queue workers (if running)
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# Clear all optimization caches
echo "🚀 Clearing optimization caches..."
php artisan optimize:clear

echo ""
echo "✅ Deployment completed successfully!"
echo ""
echo "📊 Verification checklist:"
echo "   ✓ Content Library menu should now be visible in /admin"
echo "   ✓ Crystal calculations running every 30 minutes"
echo "   ✓ Queue workers restarted and processing jobs"
echo "   ✓ All caches cleared and regenerated"
echo ""
echo "🔍 Quick test commands:"
echo "   php artisan route:list | grep content-library"
echo "   php artisan schedule:list"
echo ""
