# Webtechsolutions Mailer Package

Email management package for Laravel with Filament admin interface.

## Features

- **Email Templates**: Create and manage reusable email templates with markdown support
- **Template Variables**: Use placeholders like `{name}`, `{email}` in templates
- **Email Composer**: Rich text editor for composing emails with markdown support
- **Bulk Email Sending**: Send emails to multiple users at once
- **Queue Integration**: All emails are sent via Laravel queue for better performance
- **Sent Emails Log**: Track all sent emails with status (queued/sent/failed)
- **Test Email**: Send test emails to yourself before sending to recipients
- **Retry Failed Emails**: Retry failed emails from the admin panel

## Installation

This package is installed as a local Laravel package in the `packages/mailer` directory.

## Database Tables

The package creates two database tables:

### `email_templates`
- `id` - Primary key
- `name` - Unique template name
- `subject` - Email subject line (supports variables)
- `body` - Email body in Markdown format (supports variables)
- `variables` - JSON array of available variables
- `description` - Optional template description
- `is_active` - Boolean flag to enable/disable template
- `created_at`, `updated_at` - Timestamps

### `sent_emails`
- `id` - Primary key
- `user_id` - Foreign key to users table (nullable)
- `recipient_email` - Recipient's email address
- `recipient_name` - Recipient's name
- `subject` - Email subject
- `body` - Email body (processed with variables)
- `email_template_id` - Foreign key to email_templates (nullable)
- `status` - Email status: queued, sent, or failed
- `error_message` - Error message if failed
- `sent_at` - Timestamp when email was sent
- `created_at`, `updated_at` - Timestamps

## Admin Panel Access

Navigate to `/admin` and look under the **Configuration** menu:

1. **Email Templates** - Manage reusable email templates
   - Create, edit, delete templates
   - Define variables for templates
   - View template usage statistics

2. **Compose Email** - Send emails to users
   - Select recipients (single or multiple users)
   - Use templates or compose from scratch
   - Send test emails before sending to recipients
   - Markdown editor for rich formatting

3. **Sent Emails** - View email history
   - Filter by status (queued, sent, failed)
   - Filter by date range
   - View email details in slide-over modal
   - Retry failed emails
   - Auto-refresh every 10 seconds

## Template Variables

When creating templates or composing emails, you can use these variables:

- `{name}` - Recipient's name
- `{email}` - Recipient's email address

The variables will be automatically replaced with actual values for each recipient.

## Usage Example

### Creating a Template

1. Go to **Email Templates** → **New**
2. Fill in the form:
   - **Name**: `welcome_email`
   - **Subject**: `Welcome to {name}!`
   - **Body**:
     ```markdown
     # Welcome!

     Hello {name},

     Thank you for joining us. Your email is {email}.

     Best regards,
     The Team
     ```
   - **Variables**: `{"name": "User's full name", "email": "User's email address"}`
3. Save the template

### Sending Emails

1. Go to **Compose Email**
2. Select template (optional) or compose from scratch
3. Select recipients from the user list
4. Click **Send Test** to send a test email to yourself
5. Click **Send Emails** to queue emails for all recipients

### Viewing Sent Emails

1. Go to **Sent Emails**
2. Use filters to find specific emails
3. Click the eye icon to view email details
4. Click retry icon on failed emails to resend

## Queue Configuration

This package requires the Laravel queue to be running. Make sure your `.env` has:

```env
QUEUE_CONNECTION=database
```

And run the queue worker:

```bash
php artisan queue:listen
```

Or use the composer dev command:

```bash
composer dev
```

## Mail Configuration

Configure your mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD="your-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Your App Name"
```

## Markdown Theme

The package uses Laravel's markdown email templates. You can customize the theme by setting:

```env
MAIL_MARKDOWN_THEME=vilagmuhely
```

## License

MIT
