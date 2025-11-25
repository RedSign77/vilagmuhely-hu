# Vilagmuhely

A Laravel 12 application with Filament 3.3 admin panel for managing sessions and application data.

## Tech Stack

- **PHP**: 8.2+
- **Laravel**: 12.0
- **Filament**: 3.3
- **Database**: SQLite (default)
- **Frontend**: Vite + Tailwind CSS 4.0
- **Development**: Docker

## Features

- Filament Admin Panel at `/admin`
- Session Management Package
  - View all active sessions
  - Monitor user activity
  - Destroy sessions individually or in bulk
  - Track IP addresses and user agents
- Multi-language support (55+ languages)

## Installation

### Initial Setup

```bash
composer setup
```

This command will:
- Install dependencies
- Copy `.env.example` to `.env`
- Generate application key
- Run migrations
- Install and build frontend assets

### Manual Setup

If you prefer to run commands individually:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Development

### Start Development Server

Using Docker (recommended):
```bash
docker exec vilagmuhely-php-fpm-1 composer dev
```

This starts all services concurrently:
- Laravel server (http://localhost:8000)
- Queue worker
- Pail logs (real-time)
- Vite dev server

### Individual Services

```bash
docker exec vilagmuhely-php-fpm-1 php artisan serve
docker exec vilagmuhely-php-fpm-1 php artisan queue:listen --tries=1
docker exec vilagmuhely-php-fpm-1 php artisan pail --timeout=0
npm run dev
```

## Testing

```bash
# Run all tests
docker exec vilagmuhely-php-fpm-1 composer test

# Run specific test suite
docker exec vilagmuhely-php-fpm-1 php artisan test tests/Unit
docker exec vilagmuhely-php-fpm-1 php artisan test tests/Feature

# Run specific test
docker exec vilagmuhely-php-fpm-1 php artisan test --filter=TestName
```

## Code Quality

```bash
# Format code
docker exec vilagmuhely-php-fpm-1 ./vendor/bin/pint

# Check code style
docker exec vilagmuhely-php-fpm-1 ./vendor/bin/pint --test
```

## Admin Panel

### Access

Navigate to `/admin` and login with:
- **Email**: admin@example.com
- **Password**: password

### Features

- **Dashboard**: Overview of application metrics
- **Configuration**: System settings and session management
  - **Sessions**: View and manage active user sessions

## Packages

### Sessions Package

Located in `packages/sessions/`, this package provides comprehensive session management:

- View all active sessions
- Track user activity with timestamps
- Monitor IP addresses and user agents
- View detailed session information in slide-over modal
- Destroy sessions individually or in bulk
- Filter by active sessions or authenticated users
- Search sessions by ID, user, IP, or user agent

See [packages/sessions/README.md](packages/sessions/README.md) for detailed documentation.

## Database

The application uses SQLite by default. Configure in `.env`:

```env
DB_CONNECTION=sqlite
```

### Migrations

```bash
docker exec vilagmuhely-php-fpm-1 php artisan migrate
docker exec vilagmuhely-php-fpm-1 php artisan migrate:fresh --seed
```

## Documentation

- [CLAUDE.md](CLAUDE.md) - Development guidelines for AI assistants
- [Sessions Package](packages/sessions/README.md) - Session management documentation

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
