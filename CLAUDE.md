# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application with Filament 3.3 admin panel. The application uses:
- PHP 8.2+
- SQLite as default database
- Vite for asset bundling
- Tailwind CSS 4.0
- Queue and session management via database

## Development Commands

### Initial Setup
```bash
docker exec vilagmuhely-php-fpm-1 composer setup
```
This runs the full setup: composer install, copies .env, generates app key, runs migrations, and builds frontend assets.

### Development Server
```bash
docker exec vilagmuhely-php-fpm-1 composer dev
```
Starts all development services concurrently:
- Laravel server (http://localhost:8000)
- Queue worker
- Pail logs (real-time log viewer)
- Laravel scheduler (for crystal calculations)
- Vite dev server

Alternatively, run services individually:
```bash
docker exec vilagmuhely-php-fpm-1 php artisan serve                    # Start Laravel server
docker exec vilagmuhely-php-fpm-1 php artisan queue:listen --tries=1   # Start queue worker
docker exec vilagmuhely-php-fpm-1 php artisan pail --timeout=0         # Start log viewer
docker exec vilagmuhely-php-fpm-1 php artisan schedule:work            # Start scheduler
docker exec vilagmuhely-php-fpm-1 npm run dev                          # Start Vite dev server
```

### Testing
```bash
docker exec vilagmuhely-php-fpm-1 composer test                        # Run all tests
docker exec vilagmuhely-php-fpm-1 php artisan test                     # Run all tests (direct)
docker exec vilagmuhely-php-fpm-1 php artisan test tests/Unit          # Run unit tests only
docker exec vilagmuhely-php-fpm-1 php artisan test tests/Feature       # Run feature tests only
docker exec vilagmuhely-php-fpm-1 php artisan test --filter=TestName   # Run specific test
```

### Code Quality
```bash
docker exec vilagmuhely-php-fpm-1 ./vendor/bin/pint                    # Format code (Laravel Pint)
docker exec vilagmuhely-php-fpm-1 ./vendor/bin/pint --test             # Check code style without fixing
```

### Build
```bash
docker exec vilagmuhely-php-fpm-1 npm run build                        # Build production assets
```

### Database
```bash
docker exec vilagmuhely-php-fpm-1 php artisan migrate                  # Run migrations
docker exec vilagmuhely-php-fpm-1 php artisan migrate:fresh            # Drop all tables and re-migrate
docker exec vilagmuhely-php-fpm-1 php artisan migrate:fresh --seed     # Re-migrate and seed
docker exec vilagmuhely-php-fpm-1 php artisan db:seed                  # Run seeders
```

### Crystal Calculations
```bash
docker exec vilagmuhely-php-fpm-1 php artisan crystal:process-updates  # Manually process crystal updates
docker exec vilagmuhely-php-fpm-1 php artisan schedule:list            # View all scheduled tasks
```

**Automatic Processing**: Crystal metrics are automatically recalculated every 30 minutes via the Laravel scheduler.
The scheduler runs automatically when using `composer dev`, or can be started manually with `php artisan schedule:work`.

**How it works**:
1. User creates/updates content → Activity is queued in `crystal_activity_queue` table
2. Every 30 minutes → `crystal:process-updates` command runs
3. Command finds users with pending activities → Dispatches `RecalculateCrystalMetricsJob` for each user
4. Job recalculates all crystal metrics (facets, glow, colors, geometry) and caches the result

### Deployment

**Automated Deployment Script**:
```bash
./deploy.sh
```

This script handles complete production deployment including:
- Git pull latest changes
- Composer install dependencies (optimized for production)
- Database migrations
- Cache clearing (application, config, routes, views)
- Cache rebuilding for performance
- Filament component refresh and upgrade
- Queue worker restart
- PHP-FPM container restart

**Manual Deployment Steps** (if not using script):
```bash
cd /home/unreality1/projects/vilagmuhely
git pull
docker exec vilagmuhely-php-fpm-1 composer install --no-dev --optimize-autoloader
docker exec vilagmuhely-php-fpm-1 php artisan migrate --force
docker exec vilagmuhely-php-fpm-1 php artisan cache:clear
docker exec vilagmuhely-php-fpm-1 php artisan config:cache
docker exec vilagmuhely-php-fpm-1 php artisan route:cache
docker exec vilagmuhely-php-fpm-1 php artisan view:cache
docker exec vilagmuhely-php-fpm-1 php artisan filament:cache-components
docker exec vilagmuhely-php-fpm-1 php artisan optimize:clear
docker restart vilagmuhely-php-fpm-1
```

**Critical for Filament Changes**: After deploying changes to admin panel pages, resources, or navigation:
```bash
docker exec vilagmuhely-php-fpm-1 php artisan filament:cache-components
docker exec vilagmuhely-php-fpm-1 php artisan config:clear
docker restart vilagmuhely-php-fpm-1
```

## Architecture

### Filament Admin Panel

The application uses Filament 3.3 for the admin interface, configured in `app/Providers/Filament/AdminPanelProvider.php`:
- Panel ID: `admin`
- Panel path: `/admin`
- Primary color: Amber
- Auto-discovers resources in: `app/Filament/Admin/Resources`
- Auto-discovers pages in: `app/Filament/Admin/Pages`
- Auto-discovers widgets in: `app/Filament/Admin/Widgets`

Note: The `app/Filament` directory structure doesn't exist yet - create it when adding Filament resources.

### Directory Structure

- `app/Models/` - Eloquent models
- `app/Http/Controllers/` - HTTP controllers
- `app/Providers/` - Service providers
- `app/Filament/Admin/Resources/` - Filament resource classes (to be created)
- `app/Filament/Admin/Pages/` - Filament custom pages (to be created)
- `app/Filament/Admin/Widgets/` - Filament dashboard widgets (to be created)
- `database/migrations/` - Database migrations
- `database/factories/` - Model factories
- `database/seeders/` - Database seeders
- `resources/views/` - Blade templates
- `resources/css/` - CSS files
- `resources/js/` - JavaScript files
- `routes/web.php` - Web routes
- `routes/console.php` - Console commands
- `tests/Feature/` - Feature tests
- `tests/Unit/` - Unit tests

### Localization

The application supports multiple languages. Filament panel translations are available in `lang/vendor/filament-panels/` for 55+ languages including Hungarian (hu).

## Creating Filament Resources

To create a new Filament resource:
```bash
docker exec vilagmuhely-php-fpm-1 php artisan make:filament-resource ResourceName --generate
```

This generates:
- Resource class in `app/Filament/Admin/Resources/ResourceNameResource.php`
- List, Create, Edit pages in `app/Filament/Admin/Resources/ResourceNameResource/Pages/`

## Artisan Commands

Common artisan commands:
```bash
docker exec vilagmuhely-php-fpm-1 php artisan filament:upgrade         # Upgrade Filament assets
docker exec vilagmuhely-php-fpm-1 php artisan filament:cache-components # Cache Filament components
docker exec vilagmuhely-php-fpm-1 php artisan filament:assets          # Publish Filament assets
docker exec vilagmuhely-php-fpm-1 php artisan config:clear             # Clear configuration cache
docker exec vilagmuhely-php-fpm-1 php artisan cache:clear              # Clear application cache
docker exec vilagmuhely-php-fpm-1 php artisan route:list               # List all routes
docker exec vilagmuhely-php-fpm-1 php artisan tinker                   # Interactive REPL
```

## Packages

### Sessions Package

Located in `packages/sessions/`, this package provides session management through Filament:

- **Location**: `packages/sessions/`
- **Namespace**: `Vilagmuhely\Sessions`
- **Admin Path**: `/admin` → Configuration → Sessions
- **Features**:
  - View all active sessions
  - Track user sessions with IP addresses
  - View detailed session information in slide-over modal
  - Destroy individual or bulk sessions
  - IP address links to whatismyipaddress.com

See `packages/sessions/README.md` for detailed documentation.

### Queue Manager Package

Located in `packages/queue-manager/`, this package provides queue management through Filament:

- **Location**: `packages/queue-manager/`
- **Namespace**: `Vilagmuhely\QueueManager`
- **Admin Path**: `/admin` → Configuration → Pending Jobs / Failed Jobs / Completed Jobs
- **Features**:
  - **Pending Jobs**: View, run immediately, or terminate queued jobs
  - **Failed Jobs**: View exceptions, retry, or clear failed jobs
  - **Completed Jobs**: View queue statistics and tracking information
  - Slide-over modals for detailed job inspection
  - Bulk operations support
  - Auto-refresh every 10 seconds
  - Search and filter capabilities

**Requirements**: Database queue driver (`QUEUE_CONNECTION=database`)

See `packages/queue-manager/README.md` for detailed documentation.
- Implementing a feature needs an md file under the dps/features directory contains the neccessary description for the next coding. Updating a feature needs updating the relevant dps/features md files with the changes. These md files length not more than 500 lines and just technical information placed here.