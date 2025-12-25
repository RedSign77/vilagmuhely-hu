# Quick Start Guide

## 🚀 Fastest Installation

```bash
# 1. Extract package to your Laravel project root
cd /path/to/your-laravel-project

# 2. Run installation script
bash packages/email-automation-suite/install.sh

# 3. Run migrations
php artisan migrate

# 4. Add scheduler to routes/console.php
# Schedule::command('emails:process-scheduled')->everyMinute()->withoutOverlapping();

# 5. Start scheduler (development)
php artisan schedule:work

# 6. Clear caches
php artisan optimize:clear
```

## ✅ Done!

Access features in Filament admin:
- **System Settings > Email Templates**
- **System Settings > Scheduled Emails**

## 📚 Full Documentation

See [README.md](README.md) for complete installation and usage guide.

## 🎯 Quick Usage

### Create Email Template
1. System Settings > Email Templates > Create
2. Code: `welcome-email`
3. Subject: `Welcome {{ name }}!`
4. Body: Markdown content with `{{ variables }}`

### Create Scheduled Campaign
1. System Settings > Scheduled Emails > Create
2. Name: "Weekly Newsletter"
3. Cron: `0 9 * * 1` (Every Monday 9 AM)
4. Select template and recipients

### Send Email Programmatically
```php
use App\Mail\TemplateEmail;
use App\Models\EmailTemplate;

$template = EmailTemplate::where('code', 'welcome-email')->first();
Mail::to($user)->send(new TemplateEmail($template, [
    'name' => $user->name
]));
```

## 🆘 Common Issues

**Emails not sending?**
- Check scheduler is running: `php artisan schedule:work`
- Check queue: `php artisan queue:work database --once`
- Review logs: `storage/logs/laravel.log`

**Preview not working?**
- Clear views: `php artisan view:clear`
- Publish mail views: `php artisan vendor:publish --tag=laravel-mail`

## 📞 Support

Contact: Webtech-solutions
