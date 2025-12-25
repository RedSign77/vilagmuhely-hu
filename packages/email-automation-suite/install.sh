#!/bin/bash

# Email Automation Suite - Installation Script
# Copyright © 2025 Webtech-solutions. All rights reserved.

set -e

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  📧 Email Automation Suite - Installation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if Laravel project
if [ ! -f "artisan" ]; then
    echo "❌ Error: This script must be run from the root of a Laravel project."
    exit 1
fi

echo "✓ Laravel project detected"
echo ""

# Get package directory
PACKAGE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "📦 Package location: $PACKAGE_DIR"
echo ""

# Create backup
BACKUP_DIR="backups/email-automation-suite-backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"
echo "💾 Creating backup in: $BACKUP_DIR"
echo ""

# Step 1: Copy Models
echo "📝 Step 1/7: Copying Models..."
cp "$PACKAGE_DIR/src/Models"/*.php app/Models/
echo "   ✓ EmailTemplate.php"
echo "   ✓ ScheduledEmail.php"
echo "   ✓ EmailDispatchLog.php"
echo ""

# Step 2: Copy Migrations
echo "📝 Step 2/7: Copying Migrations..."
cp "$PACKAGE_DIR/database/migrations"/*.php database/migrations/
echo "   ✓ Migrations copied"
echo ""

# Step 3: Copy Mailable
echo "📝 Step 3/7: Copying Mail Classes..."
mkdir -p app/Mail
cp "$PACKAGE_DIR/src/Mail/TemplateEmail.php" app/Mail/
echo "   ✓ TemplateEmail.php"
echo ""

# Step 4: Copy Commands
echo "📝 Step 4/7: Copying Console Commands..."
mkdir -p app/Console/Commands
cp "$PACKAGE_DIR/src/Console/Commands/ProcessScheduledEmails.php" app/Console/Commands/
echo "   ✓ ProcessScheduledEmails.php"
echo ""

# Step 5: Copy Views
echo "📝 Step 5/7: Copying Views..."
mkdir -p resources/views/emails
cp "$PACKAGE_DIR/resources/views/emails"/*.php resources/views/emails/
echo "   ✓ template.blade.php"
echo "   ✓ template-preview.blade.php"
echo ""

# Step 6: Copy Filament Resources
echo "📝 Step 6/7: Copying Filament Resources..."
mkdir -p app/Filament/Resources
cp -r "$PACKAGE_DIR/src/Filament/Resources"/* app/Filament/Resources/
echo "   ✓ EmailTemplateResource.php + Pages"
echo "   ✓ ScheduledEmailResource.php + Pages"
echo ""

# Step 7: Install Composer Dependencies
echo "📝 Step 7/7: Installing Composer Dependencies..."
if command -v composer &> /dev/null; then
    composer require dragonmantank/cron-expression:^3.6
    echo "   ✓ dragonmantank/cron-expression installed"
else
    echo "   ⚠ Composer not found. Please run manually:"
    echo "     composer require dragonmantank/cron-expression:^3.6"
fi
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  ✅ Installation Complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "⚡ Next Steps:"
echo ""
echo "1. Run migrations:"
echo "   php artisan migrate"
echo ""
echo "2. Add to routes/console.php:"
echo "   Schedule::command('emails:process-scheduled')"
echo "       ->everyMinute()"
echo "       ->withoutOverlapping();"
echo ""
echo "3. Setup Laravel scheduler (production):"
echo "   * * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "4. Or run scheduler (development):"
echo "   php artisan schedule:work"
echo ""
echo "5. Clear caches:"
echo "   php artisan optimize:clear"
echo ""
echo "6. Access in Filament admin:"
echo "   System Settings > Email Templates"
echo "   System Settings > Scheduled Emails"
echo ""
echo "📚 Documentation: $PACKAGE_DIR/docs/"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
