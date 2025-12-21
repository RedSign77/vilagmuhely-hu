# Project Memory - Világműhely

## Overview
Világműhely is a Laravel 12 application with Filament 3.3 admin panel that provides a creative platform where content crystallizes into beautiful visualizations based on user engagement.

## Core Systems

### 1. Crystal Visualization System
- **Purpose**: Visual representation of user engagement and content quality
- **Metrics**: Facets, glow, colors, geometry
- **Update Frequency**: Every 30 minutes via Laravel scheduler
- **Processing**: Queue-based calculation using `RecalculateCrystalMetricsJob`
- **Activity Queue**: Stores pending crystal updates in `crystal_activity_queue` table

### 2. Content Management
- **Content Library**: Public-facing content browsing and discovery
- **Crystal Gallery**: Visual representation of all user crystals
- **Metrics Tracked**: Views, downloads, ratings, reviews

### 3. User Management
- **Authentication**: Laravel Fortify with email verification
- **Roles**: Supervisor, members (default)
- **Invitation System**: Token-based user invitations (see features/invitation-system.md)

## Active Features

### Invitation System (Implemented)
**File**: `dps/features/invitation-system.md`
- Allows supervisors to invite new users via email
- Pre-fills registration forms with invitation data
- Automatic email verification for invited users
- Status tracking: pending, accepted, expired, cancelled
- 72-hour invitation expiration
- Admin panel resource for managing invitations

**Key Files**:
- Model: `app/Models/Invitation.php`
- Controller: `app/Http/Controllers/InvitationController.php`
- Notification: `app/Notifications/InvitationNotification.php`
- Observer: `app/Observers/InvitationObserver.php`
- Table: `invitations` (migration)

### Email Design System (Implemented)
**File**: `dps/features/email-header-footer-redesign.md`
- Modern glassmorphism design matching homepage
- Purple/cyan/pink gradient color scheme
- Crystal emoji (💎) branding
- Responsive table-based layout for email compatibility
- Multi-column footer with links and company info

**Key Files**:
- Header: `resources/views/vendor/mail/html/header.blade.php`
- Footer: `resources/views/vendor/mail/html/footer.blade.php`
- Theme: `resources/views/vendor/mail/html/themes/default.css`

### Crystal Milestone System (Planned)
**File**: `dps/features/crystal-milestone-system.md`
- Rewards users for engagement milestones
- Tracks: invitation metrics, content view/download milestones, ratings thresholds
- New activity types: `invitation_sent`, `invitation_accepted`, `content_milestone_views`, `content_milestone_downloads`
- Milestone tracking via JSON column in `contents` table

**Planned Components**:
- Service: `app/Services/MilestoneTrackerService.php`
- Enhanced listeners in `app/Listeners/QueueCrystalUpdateListener.php`
- Migration for `milestones_reached` JSON column

## Technical Stack
- **Framework**: Laravel 12.41.1
- **PHP**: 8.4.15
- **Admin Panel**: Filament 3.3
- **Database**: SQLite
- **Frontend**: Vite, Tailwind CSS 4.0
- **Queue**: Database driver
- **Email**: Laravel Mail + Notifications

## Development Environment
- **Docker Container**: `vilagmuhely-php-fpm-1`
- **Dev Command**: `composer dev` (runs server, queue, pail, scheduler, vite)
- **Testing**: PHPUnit via `php artisan test`
- **Code Style**: Laravel Pint

## Deployment
- **Script**: `./deploy.sh` (automated full deployment)
- **Critical Commands**:
  - `php artisan filament:cache-components` (after admin changes)
  - `php artisan config:clear`
  - `docker restart vilagmuhely-php-fpm-1`

## Package Architecture

### Local Packages (packages/)
1. **sessions** - Session management UI
   - Namespace: `Vilagmuhely\Sessions`
   - Admin path: `/admin` → Configuration → Sessions

2. **queue-manager** - Queue monitoring and management
   - Namespace: `Vilagmuhely\QueueManager`
   - Admin path: `/admin` → Configuration → Pending/Failed/Completed Jobs

## Key Directories
- `app/Models/` - Eloquent models
- `app/Filament/Admin/` - Filament resources, pages, widgets
- `app/Observers/` - Model observers (e.g., InvitationObserver)
- `dps/features/` - Technical feature specifications
- `packages/` - Local package development
- `resources/views/` - Blade templates
- `database/migrations/` - Database schema

## Documentation Files
- `CLAUDE.md` - Project development guide
- `CHANGELOG.md` - Version history and changes
- `README.md` - Project overview
- `dps/work.md` - Active work items
- `dps/features/*.md` - Feature specifications

## Coding Standards
- Feature specifications: Max 500 lines, technical only
- Admin views: Use slide-over modals by default
- Changes: Document in CHANGELOG.md under current date
- Localization: Hungarian (hu) support in Filament
