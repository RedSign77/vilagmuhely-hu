# Advanced Scheduled Email Dispatcher

**Status:** ✅ Implemented
**Version:** 1.0.0
**Date:** 2025-12-24
**Prerequisites:** Custom Email Templates (dps/features/custom-email-templates.md)

## Overview

An advanced email automation system that allows supervisors to schedule automated email campaigns using cron expressions. Supports targeted recipient selection (users/roles), order-status-based triggers with historical safety, and automatic deduplication.

## User Stories Implemented

### 1. Cron-Based Schedule Configuration
Supervisors define email dispatch frequency using standard 5-part cron expressions with real-time validation and human-friendly translations.

### 2. Targeted Recipient Logic (Users & Roles)
Broadcast emails to specific user segments: all users, specific roles (supervisors/regular users), or individually selected users.

### 3. Order Status Triggering with Historical Safety
Automate emails based on order statuses with configurable look-back windows to prevent sending alerts for old orders. Built-in deduplication ensures no duplicate sends.

### 4. Dynamic Template Mapping & Plugin-Ready UI
Modular interface supporting different data sources (Users/Orders) with easy extensibility for future triggers.

## Database Schema

### Table: `scheduled_emails`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | string(255) | Descriptive campaign name |
| email_template_id | foreign key | Email template to use |
| cron_expression | string(255) | 5-part cron expression |
| is_enabled | boolean | Global enable/disable |
| data_source | enum | 'users' or 'orders' |
| recipient_type | enum | 'all', 'roles', or 'individual' |
| recipient_roles | json | Array of role identifiers |
| recipient_users | json | Array of user IDs |
| order_statuses | json | Array of order statuses to filter |
| lookback_hours | integer | Look-back window for orders |
| variable_mapping | json | Data field to template variable mapping |
| last_run_at | timestamp | Last execution timestamp |
| next_run_at | timestamp | Next scheduled run |
| total_sent | integer | Cumulative emails sent |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |

### Table: `email_dispatch_logs`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| scheduled_email_id | foreign key | Scheduled email |
| email_template_id | foreign key | Template used |
| recipient_user_id | foreign key | Recipient user |
| data_source | string | 'users' or 'orders' |
| source_record_id | bigint | ID of source record |
| recipient_email | string | Email address |
| status | enum | 'sent' or 'failed' |
| error_message | text | Error details if failed |
| created_at | timestamp | Dispatch timestamp |
| updated_at | timestamp | Update timestamp |

**Unique Constraint:** (scheduled_email_id, email_template_id, source_record_id, recipient_user_id) for deduplication

## Model Structure

### ScheduledEmail Model

**File:** `app/Models/ScheduledEmail.php`

#### Key Methods

```php
emailTemplate(): BelongsTo
```
- Returns email template relationship

```php
getHumanReadableCron(): string
```
- Converts cron expression to human-readable format
- Returns next run date or error message

```php
shouldRun(): bool
```
- Checks if schedule is enabled and due to run
- Compares next_run_at with current time

```php
updateNextRunTime(): void
```
- Calculates and updates next_run_at based on cron expression

### EmailDispatchLog Model

**File:** `app/Models/EmailDispatchLog.php`

#### Key Methods

```php
static wasDispatched(int $scheduledEmailId, int $templateId, int $sourceRecordId, int $userId): bool
```
- Checks if email was already sent to prevent duplicates
- Uses composite unique index for fast lookups

## Filament Resource

**File:** `app/Filament/Resources/ScheduledEmailResource.php`

### Access Control
- **Restricted to:** Supervisors only
- **Navigation:** System > Scheduled Emails (sort: 41)

### Form Sections

#### 1. Basic Information
- **Name:** Campaign identifier
- **Email Template:** Select from existing templates
- **Enable Schedule:** Global toggle

#### 2. Schedule Configuration
- **Cron Expression:** 5-part cron with real-time validation
  - Regex validation
  - CronExpression class validation
  - Auto-updates next_run_at on blur
- **Common Patterns:** Helper showing frequent expressions
- **Next Scheduled Run:** Calculated display field

#### 3. Data Source & Recipients

**Dynamic Fields (shown based on data_source):**

**For data_source = 'users':**
- Recipient Selection (all/roles/individual)
- Role selector (if recipient_type = 'roles')
- User searchable multi-select (if recipient_type = 'individual')

**For data_source = 'orders':**
- Order Status multi-select
- Look-back Window (hours)

#### 4. Execution Statistics
- Last Executed (human-readable)
- Total Emails Sent (formatted number)
- Only shown on edit (hidden on create)

### Table Features

**Columns:**
- Name (searchable, sortable, bold)
- Template Code (badge, primary color)
- Active (boolean icon)
- Source (badge with color coding)
- Schedule (cron expression, toggleable)
- Next Run (datetime with relative time)
- Sent (numeric count)
- Last Run (datetime, toggleable)

**Filters:**
- Data Source (users/orders)
- Active status (ternary)

**Row Actions:**
- **Force Run** (bolt icon, warning color)
  - Executes immediately bypassing schedule
  - Shows confirmation with scheduled time
  - Displays sent/skipped count after execution
  - Available for all campaigns
- Edit
- Delete

**Bulk Actions:**
- **Force Run Selected** - Execute multiple campaigns at once
- Delete multiple

**Header Actions:**
- **Clear Dispatch Logs** (danger color)
  - Resets deduplication system
  - Visible only when logs exist
  - Useful for testing

## Console Command

**File:** `app/Console/Commands/ProcessScheduledEmails.php`

### Signature
```bash
php artisan emails:process-scheduled {--id=}
```

### Options
- `--id`: Process only specific scheduled email ID (for manual runs)

### Execution Flow

1. **Query Scheduled Emails**
   - Filter by is_enabled = true
   - If --id provided, process only that record
   - Otherwise, process where next_run_at <= now()

2. **For Each Scheduled Email:**
   - Get recipients based on data source and filters
   - Skip if no recipients found
   - For each recipient:
     - Check deduplication (EmailDispatchLog)
     - Skip if already sent
     - Build template variables
     - Send email via TemplateEmail mailable
     - Log dispatch attempt (sent/failed)
     - Handle exceptions

3. **Update Statistics:**
   - Set last_run_at to now()
   - Increment total_sent counter
   - Calculate and set next_run_at

### Recipient Resolution

**Users Data Source:**
- **All:** Query all users
- **Roles:** Filter by supervisor boolean
  - supervisor = true for 'supervisor' role
  - supervisor = false for 'user' role
- **Individual:** Filter by user IDs array

**Orders Data Source:**
- Filter by order_statuses array (if provided)
- Filter by updated_at >= now() - lookback_hours
- Eager load buyer relationship

### Variable Mapping

**Users Data Source:**
```php
[
    'name' => $user->name,
    'email' => $user->email,
]
```

**Orders Data Source:**
```php
[
    'name' => $order->buyer->name,
    'email' => $order->buyer->email,
    'order_number' => $order->order_number,
    'order_status' => ucfirst($order->order_status),
    'total_amount' => number_format($order->total_amount, 2),
]
```

## Scheduled Task

**File:** `routes/console.php`

```php
Schedule::command('emails:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping();
```

- Runs every minute
- Prevents overlapping executions
- Checks all enabled scheduled emails
- Only dispatches those due to run based on cron

## Usage Examples

### Example 1: Weekly Newsletter to All Users

```php
ScheduledEmail::create([
    'name' => 'Weekly Newsletter',
    'email_template_id' => EmailTemplate::where('code', 'weekly-newsletter')->first()->id,
    'cron_expression' => '0 9 * * 1', // Every Monday at 9 AM
    'is_enabled' => true,
    'data_source' => 'users',
    'recipient_type' => 'all',
]);
```

### Example 2: Order Shipped Notifications

```php
ScheduledEmail::create([
    'name' => 'Order Shipped Notification',
    'email_template_id' => EmailTemplate::where('code', 'order-shipped')->first()->id,
    'cron_expression' => '*/5 * * * *', // Every 5 minutes
    'is_enabled' => true,
    'data_source' => 'orders',
    'order_statuses' => ['shipped'],
    'lookback_hours' => 1, // Only orders updated in last hour
]);
```

### Example 3: Supervisor-Only Announcements

```php
ScheduledEmail::create([
    'name' => 'Admin Announcements',
    'email_template_id' => EmailTemplate::where('code', 'admin-update')->first()->id,
    'cron_expression' => '0 10 1 * *', // First day of month at 10 AM
    'is_enabled' => true,
    'data_source' => 'users',
    'recipient_type' => 'roles',
    'recipient_roles' => ['supervisor'],
]);
```

## Deduplication Logic

The system prevents duplicate sends using a composite unique index:
- `scheduled_email_id`
- `email_template_id`
- `source_record_id`
- `recipient_user_id`

**Example Scenario:**
1. Order #123 status changes to "shipped" at 2 PM
2. Scheduled email runs at 2:05 PM, sends notification
3. EmailDispatchLog created with order ID 123
4. Same scheduled email runs at 2:10 PM
5. Deduplication check finds existing log, skips send

## Testing Scheduled Emails

### Manual Testing with Force Run

1. **Create a test campaign:**
   - Go to System Settings > Scheduled Emails
   - Create new campaign with any schedule
   - Select template and recipients

2. **Force execute immediately:**
   - Click "Force Run" action (bolt icon)
   - Confirm execution
   - View sent/skipped count in notification

3. **Test deduplication:**
   - Click "Force Run" again
   - All emails should be skipped
   - Notification shows: "Sent: 0 | Skipped: X"

4. **Reset for re-testing:**
   - Click "Clear Dispatch Logs" button (header)
   - Confirm deletion
   - Run campaign again - emails will send

### Command Line Testing

```bash
# Force run specific campaign (bypasses schedule)
php artisan emails:process-scheduled --id=1

# Process all due campaigns (respects schedule)
php artisan emails:process-scheduled

# Check what would run
php artisan tinker --execute="
\App\Models\ScheduledEmail::where('is_enabled', true)
    ->where('next_run_at', '<=', now())
    ->pluck('name', 'id');
"
```

### Verify Email Delivery

1. Check dispatch logs:
   - `App\Models\EmailDispatchLog::latest()->get()`
   - Status: 'sent' or 'failed'

2. Check queue:
   - Emails are queued via `ShouldQueue`
   - Process queue: `php artisan queue:work --once`

3. Check email inbox:
   - Spam/junk folders
   - Search by campaign execution time

## Best Practices

1. **Cron Expression Design**
   - Use appropriate frequency for data source
   - Orders: Higher frequency (every 5-15 minutes)
   - Users: Lower frequency (daily/weekly)

2. **Look-back Windows**
   - Set lookback_hours to match or slightly exceed cron frequency
   - Example: 5-minute cron → 10-15 minute lookback
   - Prevents missing records between runs

3. **Testing**
   - Use "Run Now" action in Filament for testing
   - Check EmailDispatchLog for successful sends
   - Monitor failed sends via status = 'failed'

4. **Performance**
   - Limit recipient counts for high-frequency schedules
   - Use individual selection sparingly
   - Monitor queue if emails are queued

## Troubleshooting

### Emails Not Sending

1. Check `is_enabled` is true
2. Verify `next_run_at` is in the past
3. Confirm cron expression is valid
4. Check scheduled task is running (`php artisan schedule:work`)

### Duplicate Emails

- Should never occur due to deduplication
- If occurring, check EmailDispatchLog for missing entries
- Verify composite unique index exists

### Wrong Recipients

- Review data_source and recipient_type settings
- For orders, check order_statuses and lookback_hours
- For users with roles, verify supervisor field

## Future Enhancements

- Additional data sources (subscriptions, cart abandonment)
- Advanced variable mapping UI
- Email preview with sample data
- Scheduling statistics dashboard
- Pause/resume functionality
- A/B testing support
