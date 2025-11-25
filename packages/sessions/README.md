# Sessions Package

A Laravel package for managing and viewing sessions with a Filament admin interface.

## Features

- **Session Management**: View all active and inactive sessions in your Laravel application
- **Filament Integration**: Beautiful admin interface with Filament 3.x
- **Configuration Menu**: Organized under a collapsible "Configuration" navigation group
- **View Session Details**: View detailed session information in a slide-over modal
- **Destroy Sessions**: Clear individual sessions or bulk destroy multiple sessions
- **IP Address Tracking**: Click on IP addresses to view location information on whatismyipaddress.com
- **User Association**: See which user each session belongs to
- **Activity Tracking**: View last activity timestamps with human-readable format

## Installation

The package is already installed as a local package. It's configured in the root `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/sessions"
        }
    ],
    "require": {
        "vilagmuhely/sessions": "@dev"
    }
}
```

## Usage

### Accessing the Sessions Interface

1. Navigate to your Filament admin panel (typically `/admin`)
2. Look for the "Configuration" navigation group
3. Click on "Sessions" to view all sessions

### Session Table Features

The session table displays:

- **Session ID**: Unique identifier for each session (click to copy)
- **User**: Associated user or "Guest" for unauthenticated sessions
- **IP Address**: Clickable link to view IP location (opens in new tab)
- **User Agent**: Browser and device information
- **Last Activity**: Human-readable time since last activity

### Available Actions

#### View Action
- Click the "View" button on any row
- Opens a slide-over modal with detailed session information
- Displays session payload in formatted JSON

#### Destroy Action
- Click the "Destroy" button on any row
- Confirms before permanently removing the session
- Shows success notification after deletion

#### Bulk Actions
- Select multiple sessions using checkboxes
- Use "Destroy Selected" to remove multiple sessions at once

### Filters

- **Active (Last 5 minutes)**: Show only recently active sessions
- **Authenticated Users**: Show only sessions with logged-in users

## Package Structure

```
packages/sessions/
├── composer.json
├── README.md
└── src/
    ├── Models/
    │   └── Session.php
    ├── Providers/
    │   └── SessionServiceProvider.php
    └── Filament/
        └── Resources/
            ├── SessionResource.php
            └── SessionResource/
                └── Pages/
                    └── ListSessions.php
```

## Requirements

- PHP ^8.2
- Laravel ^12.0
- Filament ^3.3
- Database driver set to `database` in `config/session.php`

## Database

This package uses the default Laravel `sessions` table created by the migration:

```php
Schema::create('sessions', function (Blueprint $table) {
    $table->string('id')->primary();
    $table->foreignId('user_id')->nullable()->index();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->longText('payload');
    $table->integer('last_activity')->index();
});
```

Make sure your `SESSION_DRIVER` is set to `database` in your `.env` file:

```env
SESSION_DRIVER=database
```

## Customization

### Changing Navigation Group

To change the navigation group, edit `SessionResource.php`:

```php
protected static ?string $navigationGroup = 'Your Group Name';
```

### Changing Navigation Label

```php
protected static ?string $navigationLabel = 'Your Label';
```

### Changing Navigation Icon

```php
protected static ?string $navigationIcon = 'heroicon-o-your-icon';
```

## License

MIT License
