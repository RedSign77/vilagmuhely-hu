# Email Automation Suite

**Version:** 1.0.0
**Author:** Webtech-solutions
**License:** Proprietary

A comprehensive Laravel package providing customizable email templates and advanced scheduled email campaigns with Filament admin integration.

## Features

### 📧 Custom Email Templates
- Markdown-based email templates with live preview
- Variable injection system (`{{ variable }}` placeholders)
- Read-only template codes for persistent reference
- Integration with Laravel mail components
- Supervisor-only access control

### ⏰ Advanced Scheduled Email Dispatcher
- Cron-based scheduling with validation
- Multi-source data support (Users/Orders)
- Dynamic recipient targeting (all users, roles, individual)
- Automatic deduplication system
- Order status filtering with look-back windows
- Force run capabilities for testing
- Comprehensive execution tracking

## Requirements

- **Laravel:** 11.x or 12.x
- **PHP:** 8.2+
- **Filament:** 3.x
- **Composer packages:**
  - `dragonmantank/cron-expression: ^3.6`

## Installation

### Step 1: Copy Package Files

Copy the entire `email-automation-suite` directory to your project:

```bash
cp -r email-automation-suite /path/to/your-laravel-project/packages/
```

### Step 2: Register Models

Copy model files to your Laravel app:

```bash
cp packages/email-automation-suite/src/Models/*.php app/Models/
```

**Models included:**
- `EmailTemplate.php`
- `ScheduledEmail.php`
- `EmailDispatchLog.php`

### Step 3: Copy Migrations

Copy migration files:

```bash
cp packages/email-automation-suite/database/migrations/*.php database/migrations/
```

Run migrations:

```bash
php artisan migrate
```

**Tables created:**
- `email_templates`
- `scheduled_emails`
- `email_dispatch_logs`

### Step 4: Copy Mailable

Copy the mailable class:

```bash
cp packages/email-automation-suite/src/Mail/TemplateEmail.php app/Mail/
```

### Step 5: Copy Console Command

Copy the command:

```bash
cp packages/email-automation-suite/src/Console/Commands/ProcessScheduledEmails.php app/Console/Commands/
```

### Step 6: Copy Views

Copy email views:

```bash
cp packages/email-automation-suite/resources/views/emails/*.php resources/views/emails/
```

**Views included:**
- `template.blade.php` - Main email template
- `template-preview.blade.php` - Preview modal template

### Step 7: Copy Filament Resources

Copy Filament resources:

```bash
cp -r packages/email-automation-suite/src/Filament/Resources/* app/Filament/Resources/
```

**Resources included:**
- `EmailTemplateResource.php` + Pages directory
- `ScheduledEmailResource.php` + Pages directory

### Step 8: Install Dependencies

Install required Composer package:

```bash
composer require dragonmantank/cron-expression:^3.6
```

### Step 9: Schedule Task Setup

Add to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

// Process scheduled emails every minute
Schedule::command('emails:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping();
```

Make sure Laravel scheduler is running:

```bash
# Add to crontab (production)
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

# Or run in development
php artisan schedule:work
```

### Step 10: Mail Layout (Optional)

If you want to use custom mail branding, publish Laravel mail views:

```bash
php artisan vendor:publish --tag=laravel-mail
```

Then customize:
- `resources/views/vendor/mail/html/themes/default.css` (or create a custom theme)
- `resources/views/vendor/mail/html/header.blade.php`
- `resources/views/vendor/mail/html/footer.blade.php`

### Step 11: Clear Caches

```bash
php artisan optimize:clear
```

## Usage

### Access Filament Admin Panel

After installation, the following will be available in your Filament admin panel:

**Navigation:** System Settings
- **Email Templates** - Manage email templates
- **Scheduled Emails** - Configure automated campaigns

### Creating an Email Template

1. Go to **System Settings > Email Templates**
2. Click **Create**
3. Fill in:
   - **Code:** Unique slug (e.g., `welcome-email`)
   - **Description:** What this template is for
   - **Subject:** Email subject with `{{ variable }}` support
   - **Body:** Markdown content with `{{ variable }}` support
4. Click **Preview** to see rendered email
5. Save

### Creating a Scheduled Email Campaign

1. Go to **System Settings > Scheduled Emails**
2. Click **Create**
3. Configure:
   - **Name:** Campaign name
   - **Email Template:** Select template
   - **Cron Expression:** Schedule (e.g., `0 9 * * 1` for Mondays at 9 AM)
   - **Data Source:** Users or Orders
   - **Recipients:** All, specific roles, or individual users
   - **Order Filters** (if applicable): Status and look-back window
4. Save

### Sending Emails Programmatically

```php
use App\Mail\TemplateEmail;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;

$template = EmailTemplate::where('code', 'welcome-email')->first();

Mail::to($user->email)->send(
    new TemplateEmail($template, [
        'name' => $user->name,
        'email' => $user->email,
    ])
);
```

### Manual Testing

**Force Run a Campaign:**
1. Go to **System Settings > Scheduled Emails**
2. Click **Force Run** (bolt icon) on any campaign
3. Confirm execution
4. View sent/skipped counts in notification

**Reset Deduplication:**
1. Click **Clear Dispatch Logs** button (header)
2. Confirm - this allows re-sending to same recipients

### Command Line Usage

```bash
# Process all due campaigns (respects schedule)
php artisan emails:process-scheduled

# Force run specific campaign (bypasses schedule)
php artisan emails:process-scheduled --id=1
```

## Configuration

### Access Control

Both features are restricted to supervisors by default. To customize access:

Edit `canAccess()` method in:
- `app/Filament/Resources/EmailTemplateResource.php`
- `app/Filament/Resources/ScheduledEmailResource.php`

```php
public static function canAccess(): bool
{
    return auth()->user()->isSupervisor(); // Customize this
}
```

### Navigation

By default, both features appear under "System Settings". To change:

Edit `$navigationGroup` in resource files:

```php
protected static ?string $navigationGroup = 'System Settings'; // Change this
```

## Documentation

Detailed feature documentation is available in the `docs/` directory:

- **[custom-email-templates.md](docs/custom-email-templates.md)** - Complete Email Templates documentation
- **[advanced-scheduled-email-dispatcher.md](docs/advanced-scheduled-email-dispatcher.md)** - Complete Scheduled Emails documentation

## File Structure

```
email-automation-suite/
├── README.md                           # This file
├── database/
│   └── migrations/
│       ├── *_create_email_templates_table.php
│       ├── *_create_scheduled_emails_table.php
│       └── *_create_email_dispatch_logs_table.php
├── src/
│   ├── Models/
│   │   ├── EmailTemplate.php
│   │   ├── ScheduledEmail.php
│   │   └── EmailDispatchLog.php
│   ├── Mail/
│   │   └── TemplateEmail.php
│   ├── Console/
│   │   └── Commands/
│   │       └── ProcessScheduledEmails.php
│   └── Filament/
│       └── Resources/
│           ├── EmailTemplateResource.php
│           ├── EmailTemplateResource/
│           │   └── Pages/
│           ├── ScheduledEmailResource.php
│           └── ScheduledEmailResource/
│               └── Pages/
├── resources/
│   └── views/
│       └── emails/
│           ├── template.blade.php
│           └── template-preview.blade.php
└── docs/
    ├── custom-email-templates.md
    └── advanced-scheduled-email-dispatcher.md
```

## Troubleshooting

### Emails Not Sending

1. **Check Laravel scheduler is running:**
   ```bash
   php artisan schedule:work  # Development
   ```

2. **Verify mail configuration:**
   ```bash
   php artisan tinker
   config('mail.mailer')  # Should return your mail driver
   ```

3. **Check queue is processing:**
   ```bash
   php artisan queue:work database --once
   ```

4. **Review dispatch logs:**
   - Go to System Settings > Scheduled Emails
   - Check execution statistics
   - Review EmailDispatchLog table for errors

### Schedule Not Running

1. **Verify cron expression is valid**
2. **Check `next_run_at` timestamp**
3. **Ensure `is_enabled` is true**
4. **Review Laravel logs:** `storage/logs/laravel.log`

### Preview Not Showing

1. **Clear view cache:**
   ```bash
   php artisan view:clear
   ```

2. **Check mail layout exists:**
   ```bash
   ls resources/views/vendor/mail/html/
   ```

## Uninstallation

To remove the package:

```bash
# 1. Remove migrations (WARNING: This deletes all data!)
php artisan migrate:rollback --step=3

# 2. Delete files
rm -rf app/Models/EmailTemplate.php
rm -rf app/Models/ScheduledEmail.php
rm -rf app/Models/EmailDispatchLog.php
rm -rf app/Mail/TemplateEmail.php
rm -rf app/Console/Commands/ProcessScheduledEmails.php
rm -rf app/Filament/Resources/EmailTemplateResource*
rm -rf app/Filament/Resources/ScheduledEmailResource*
rm -rf resources/views/emails/template*.blade.php

# 3. Remove from routes/console.php
# (Manually remove the scheduled task)

# 4. Clear caches
php artisan optimize:clear
```

## Support

For issues, questions, or feature requests, contact Webtech-solutions.

## License

Copyright © 2025 Webtech-solutions. All rights reserved.

This package is proprietary software and may not be redistributed without permission.

## Changelog

### Version 1.0.0 (2025-12-24)
- Initial release
- Custom Email Templates feature
- Advanced Scheduled Email Dispatcher feature
- Filament 3.x integration
- Laravel 11.x/12.x support
