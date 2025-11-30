# User Manager Package

This package provides user and role management functionality for the application.

## Features

- **User Management**: Comprehensive user CRUD operations through Filament admin panel
- **Role Management**: Role-based access control system
- **Profile Fields**: Extended user profile with avatar, mobile, city, address, social media links, and biography
- **Supervisor Roles**: Special roles with elevated permissions
- **Activity Logging**: Comprehensive tracking of user activities including login, logout, failed login, profile changes, password changes, and role changes

## Installation

The package is auto-discovered and automatically registered in your Laravel application.

## Migrations

The package includes the following migrations:

- `create_roles_table` - Creates the roles table
- `create_role_user_table` - Creates the pivot table for user-role relationships
- `add_profile_fields_to_users_table` - Adds extended profile fields to users table
- `create_user_activity_logs_table` - Creates the activity logging table

## Seeders

### RolesTableSeeder

Located at `database/seeders/RolesTableSeeder.php`

Seeds the default roles for the application:

1. **Guests** - Guest users with limited access to the system
2. **Members** - Regular members with standard access privileges
3. **Creators** - Content creators with extended permissions
4. **Administrators** - System administrators with full access (supervisor role)

#### Usage

Run all seeders (includes roles):
```bash
php artisan db:seed
```

Run only the roles seeder:
```bash
php artisan db:seed --class=RolesTableSeeder
```

Refresh database and seed:
```bash
php artisan migrate:fresh --seed
```

## Models

### Role Model

Located at `Webtechsolutions\UserManager\Models\Role`

**Fields:**
- `name` - Role name (e.g., "Administrators")
- `slug` - URL-friendly identifier (auto-generated from name)
- `description` - Role description
- `avatar` - Optional role avatar/icon
- `is_supervisor` - Boolean flag for supervisor privileges

**Relationships:**
- `users()` - BelongsToMany relationship with User model

### User Model Extensions

The package extends the base User model with:

**Additional Fields:**
- `avatar` - User avatar image
- `mobile` - Phone number
- `city` - City of residence
- `address` - Full address
- `social_media_links` - Array of social media profiles
- `about` - Biography/about text

**Relationships:**
- `roles()` - BelongsToMany relationship with Role model

**Methods:**
- `hasRole(string $roleSlug)` - Check if user has a specific role
- `isSupervisor()` - Check if user has any supervisor role

## User Activity Logging

The package includes a comprehensive activity logging system that automatically tracks user activities.

### Tracked Activities

1. **Login** - Successful user login attempts
2. **Logout** - User logout events
3. **Failed Login** - Failed authentication attempts with email and IP tracking
4. **Profile Change** - User profile field updates with old/new values
5. **Password Change** - Password updates via profile or password reset
6. **Role Change** - Role assignments and removals

### Activity Log Features

- **Automatic tracking** - All activities are logged automatically via event listeners and observers
- **IP address tracking** - Records the IP address for each activity
- **User agent tracking** - Captures browser and platform information
- **Detailed properties** - Stores additional context (changed fields, old/new values, etc.)
- **Filterable interface** - Filter by activity type, user, and date range
- **Tabbed navigation** - Quick access to specific activity types
- **Auto-refresh** - Activity list refreshes every 10 seconds
- **Slide-over details** - View full activity details in a modal
- **Manual cleanup** - Bulk delete old logs from the UI

### Accessing Activity Logs

Navigate to: **Admin → Management → User Activity Logs**

The activity log page includes tabs for:
- All Activities
- Logins
- Logouts
- Failed Logins
- Profile Changes
- Password Changes
- Role Changes

### Automatic Cleanup

Activity logs are automatically cleaned up daily to maintain database performance:

```bash
# Scheduled daily at 4:00 AM
# Deletes logs older than 90 days
php artisan user-manager:cleanup-activity-logs --days=90
```

Manual cleanup:

```bash
# Clean up logs older than 30 days
php artisan user-manager:cleanup-activity-logs --days=30
```

### Programmatic Usage

```php
use Webtechsolutions\UserManager\Models\UserActivityLog;

// Log a custom activity
UserActivityLog::log(
    userId: $user->id,
    activityType: UserActivityLog::TYPE_LOGIN,
    description: 'Custom activity description',
    properties: ['key' => 'value']
);

// Query activity logs
$logins = UserActivityLog::ofType(UserActivityLog::TYPE_LOGIN)->get();
$recentActivities = UserActivityLog::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

## Filament Resources

### UserResource

Located at `/admin` → Management → Users

**Features:**
- List all users with avatars, roles, and verification status
- Create new users
- Edit existing users
- View user details in slide-over modal
- Filter by email verification and roles
- Search by name, email, mobile, city

**Form Sections:**
- Basic Information (avatar, name, email, password)
- Contact Information (mobile, city, address)
- Social Media Links (repeater for multiple platforms)
- About (markdown editor for biography)
- Roles (checkbox list for role assignment)

### RoleResource

Located at `/admin` → Management → Roles

**Features:**
- List all roles with user counts
- Create new roles
- Edit existing roles
- View role details in slide-over modal
- Assign users to roles

### UserActivityLogResource

Located at `/admin` → Management → User Activity Logs

**Features:**
- List all user activities with filtering and search
- View activity details in slide-over modal
- Filter by activity type, user, and date range
- Tabbed interface for different activity types
- IP address tracking with external lookup links
- Browser and platform information
- Auto-refresh every 10 seconds
- Bulk delete old activity logs
- Manual cleanup action for logs older than 90 days

## Usage Examples

### Check User Role

```php
if ($user->hasRole('administrators')) {
    // User is an administrator
}
```

### Check Supervisor Status

```php
if ($user->isSupervisor()) {
    // User has supervisor privileges
}
```

### Get User Roles

```php
$roles = $user->roles;
foreach ($roles as $role) {
    echo $role->name;
}
```

### Create a Role

```php
use Webtechsolutions\UserManager\Models\Role;

Role::create([
    'name' => 'Moderators',
    'description' => 'Forum moderators',
    'is_supervisor' => false,
]);
```

## Admin Access

Users can access the Filament admin panel after email verification. The `User` model implements the `FilamentUser` interface and uses the `canAccessPanel()` method to control access.

## License

This package is part of the Vilagmuhely application.
