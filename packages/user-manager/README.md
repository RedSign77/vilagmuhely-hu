# User Manager Package

This package provides user and role management functionality for the application.

## Features

- **User Management**: Comprehensive user CRUD operations through Filament admin panel
- **Role Management**: Role-based access control system
- **Profile Fields**: Extended user profile with avatar, mobile, city, address, social media links, and biography
- **Supervisor Roles**: Special roles with elevated permissions

## Installation

The package is auto-discovered and automatically registered in your Laravel application.

## Migrations

The package includes the following migrations:

- `create_roles_table` - Creates the roles table
- `create_role_user_table` - Creates the pivot table for user-role relationships
- `add_profile_fields_to_users_table` - Adds extended profile fields to users table

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
