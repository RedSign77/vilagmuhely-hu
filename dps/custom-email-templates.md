# Custom Email Template System

**Status:** ✅ Implemented
**Version:** 1.0.0
**Date:** 2025-12-24

## Overview

A flexible email template management system that allows supervisors to create, edit, and manage email templates with Markdown content and dynamic variable injection. Templates are triggered programmatically using unique reference codes.

## User Stories Implemented

### 1. Template Definition & Identification
Supervisors can create email templates with unique reference codes that remain persistent even when content changes.

### 2. Content Creation with Markdown
Templates support Markdown editing with live preview functionality, wrapped in the application's branded email layout.

### 3. Simple Variable Injection
Subject lines and body content support `{{ variable }}` placeholders for personalization.

### 4. Branding Consistency
All emails automatically inherit consistent styling with branded header and footer from custom HTML email template.

## Database Schema

**Table:** `email_templates`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| code | string(255) | Unique slug identifier (read-only after creation) |
| subject | string(255) | Email subject with variable support |
| body | text | Markdown content with variable support |
| description | text (nullable) | Admin documentation |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Last update timestamp |

**Indexes:**
- Unique index on `code` column

## Model Structure

**File:** `app/Models/EmailTemplate.php`

### Key Methods

```php
replaceVariables(array $variables): array
```
- Accepts key-value pairs for variable replacement
- Returns array with processed `subject` and `body`
- Example: `['name' => 'John']` replaces `{{ name }}` with "John"

### Fillable Fields
- code
- subject
- body
- description

## Filament Resource

**File:** `app/Filament/Resources/EmailTemplateResource.php`

### Access Control
- **Restricted to:** Supervisors only (`canAccess()` checks `isSupervisor()`)
- **Navigation:** System > Email Templates (sort: 40)

### Form Features

1. **Code Field**
   - Validation: Slug format (lowercase, numbers, hyphens only)
   - Unique constraint
   - Read-only after creation (disabled on edit, but dehydrated)

2. **Description Field**
   - Documents template purpose
   - 2-row textarea

3. **Variables Cheat Sheet**
   - Built-in placeholder component
   - Shows common variables:
     - `{{ name }}` - User/recipient name
     - `{{ email }}` - Email address
     - `{{ order_number }}` - Order number
     - `{{ card_title }}` - Card title
     - `{{ game_name }}` - Game name

4. **Subject Field**
   - Text input with variable support
   - Helper text guides variable usage

5. **Body Field**
   - MarkdownEditor component
   - Toolbar: bold, italic, headings, lists, links, tables, code blocks
   - 15 rows default

### Table Features

**Columns:**
- Code (badge, primary color, searchable, sortable)
- Subject (searchable, sortable, 50 char limit)
- Description (searchable, toggleable, 60 char limit)
- Updated At (since format, sortable, toggleable)

**Actions:**
- Preview (eye icon) - Opens modal with rendered email
- Edit
- Delete

**Default Sort:** Code (ascending)

### Preview Functionality

**Table Action:**
- Modal preview from list view
- Shows processed Markdown wrapped in email layout
- 4xl modal width

**Edit Page Header Action:**
- "Preview Email" button (info color)
- Real-time preview while editing
- Same modal rendering as table action

## Mailable Class

**File:** `app/Mail/TemplateEmail.php`

### Usage

```php
use App\Mail\TemplateEmail;
use App\Models\EmailTemplate;

$template = EmailTemplate::where('code', 'welcome-email')->first();
$variables = [
    'name' => $user->name,
    'email' => $user->email,
];

Mail::to($user->email)->send(new TemplateEmail($template, $variables));
```

### Features
- Implements `ShouldQueue` for background processing
- Processes variables on construction
- Converts Markdown to HTML automatically
- Uses `emails.template` markdown view
- Applies 'cardsforge' theme automatically via `theme()` method

### Constructor Parameters
- `EmailTemplate $template` - The template model
- `array $variables = []` - Variable replacements

## Views

### Template Email View
**File:** `resources/views/emails/template.blade.php`

Uses Laravel mail component system (`x-mail::message`) with:
- Automatic integration with `resources/views/vendor/mail` layouts
- Cards Forge custom theme (`cardsforge.css`) with branded styling
- Responsive email-safe HTML structure
- Consistent header with spade icon and site name
- Branded footer with card suit symbols and copyright

### Preview View
**File:** `resources/views/emails/template-preview.blade.php`

Simulates actual email layout for admin panel preview:
- Loads Cards Forge theme CSS
- Displays site header with branding
- Shows content in email-safe table structure
- Includes footer with card symbols and copyright
- Provides accurate representation of sent emails

## Integration Examples

### 1. Welcome Email on User Registration

```php
// In UserObserver or EventListener
$template = EmailTemplate::where('code', 'user-welcome')->first();
Mail::to($user->email)->send(new TemplateEmail($template, [
    'name' => $user->name,
]));
```

### 2. Order Confirmation

```php
$template = EmailTemplate::where('code', 'order-confirmation')->first();
Mail::to($order->user->email)->send(new TemplateEmail($template, [
    'name' => $order->user->name,
    'order_number' => $order->order_number,
]));
```

### 3. Card Approval Notification

```php
$template = EmailTemplate::where('code', 'card-approved')->first();
Mail::to($card->user->email)->send(new TemplateEmail($template, [
    'name' => $card->user->name,
    'card_title' => $card->title,
    'game_name' => $card->game->name,
]));
```

## Best Practices

1. **Template Codes**
   - Use descriptive, kebab-case names
   - Examples: `user-welcome`, `order-shipped`, `card-rejected`
   - Never change codes after creation

2. **Variable Naming**
   - Use snake_case for consistency
   - Keep names short and descriptive
   - Document available variables in description field

3. **Markdown Content**
   - Use heading levels appropriately (##, ###)
   - Avoid raw HTML when possible
   - Test preview before saving

4. **Variable Defaults**
   - Always pass all expected variables
   - Missing variables will remain as `{{ variable }}` in output

## Migration Path

To create initial templates:

```php
EmailTemplate::create([
    'code' => 'user-welcome',
    'subject' => 'Welcome to {{ app_name }}, {{ name }}!',
    'body' => "# Welcome!\n\nThank you for joining **{{ app_name }}**...",
    'description' => 'Sent to new users upon successful registration',
]);
```

## Limitations

1. Variables are simple string replacements (no conditional logic)
2. Code field cannot be changed after creation
3. Supervisor access only
4. No built-in A/B testing or versioning

## Future Enhancements

- Template versioning system
- Preview with sample data
- Usage analytics per template
- Template categories/tags
- Rich variable types (dates, currency, etc.)
- Template inheritance/partials
