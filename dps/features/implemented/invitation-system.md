# Invitation System Feature Specification

Status: Implemented

## Overview

A comprehensive user invitation system that allows authenticated supervisors to invite new users to the Világműhely platform via email. Invited users receive a unique invitation link that pre-fills registration forms and automatically verifies their email upon signup.

## User Stories

### As a Supervisor
- I want to click an invitation icon in the top navigation bar to quickly invite users
- I want to provide the invitee's name, email, and optional personal message
- I want to see all sent invitations with their status (pending/accepted/expired/cancelled)
- I want to resend invitations if needed
- I want to cancel pending invitations
- I want to copy invitation links to share them manually

### As an Invited User
- I want to receive an invitation email with a personalized message
- I want to click the invitation link and have my name and email pre-filled
- I want my email to be automatically verified (no verification email needed)
- I want to be assigned the default "members" role automatically
- I want to see a welcome notification after successful registration

## Technical Architecture

### Technology Stack
- Laravel 12.41.1
- PHP 8.4.15
- Filament 3.3 (Admin Panel)
- SQLite Database
- Queue System (Database Driver)
- Email Notifications

### Core Components

1. **Database Layer**
   - `invitations` table with token-based authentication
   - Foreign key relationships to users table

2. **Model Layer**
   - `Invitation` model with relationships, scopes, and business logic
   - Integration with `User` and `Role` models

3. **User Interface**
   - Top navigation bar action (Livewire component)
   - Filament admin resource for invitation management
   - Custom registration page with pre-fill support

4. **Email System**
   - `InvitationNotification` for sending invitation emails
   - Integration with existing mailer infrastructure

5. **Authentication Flow**
   - Custom registration handler for invitation acceptance
   - Email verification bypass for invited users
   - Automatic role assignment

## Database Schema

### invitations Table

```sql
CREATE TABLE invitations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NULL,
    invited_by_user_id INTEGER NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,
    accepted_by_user_id INTEGER NULL,
    status ENUM('pending', 'accepted', 'expired', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (invited_by_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (accepted_by_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX (email, status),
    INDEX (token),
    INDEX (expires_at),
    INDEX (status)
);
```

### Field Descriptions

- **id**: Primary key
- **name**: Invitee's full name
- **email**: Invitee's email address (indexed with status for uniqueness checks)
- **message**: Optional personal message from inviter
- **invited_by_user_id**: Foreign key to user who sent invitation
- **token**: 64-character random token for invitation URL (unique, indexed)
- **expires_at**: Expiration timestamp (72 hours default, indexed for cleanup)
- **accepted_at**: Timestamp when invitation was accepted
- **accepted_by_user_id**: Foreign key to user who accepted (for audit trail)
- **status**: Current state of invitation (indexed for queries)
- **created_at/updated_at**: Standard Laravel timestamps

## File Structure

### Core Files

```
app/
├── Console/Commands/
│   └── CleanupExpiredInvitations.php          # Daily cleanup command
├── Filament/Admin/
│   ├── Pages/
│   │   ├── Auth/
│   │   │   └── Register.php                   # Custom registration with invitation support
│   │   └── InviteUserAction.php               # Livewire component for topbar action
│   └── Resources/
│       ├── InvitationResource.php              # Admin CRUD resource
│       └── InvitationResource/Pages/
│           ├── ListInvitations.php
│           ├── CreateInvitation.php
│           ├── EditInvitation.php
│           └── ViewInvitation.php
├── Http/
│   ├── Controllers/
│   │   └── InvitationController.php           # Public invitation acceptance
│   └── Responses/
│       ├── RegistrationResponse.php           # Custom registration redirect
│       └── EmailVerificationResponse.php      # Custom verification handler
├── Listeners/
│   └── HandleInvitationAcceptance.php         # (Deprecated - logic in Register.php)
├── Models/
│   └── Invitation.php                         # Eloquent model
├── Notifications/
│   └── InvitationNotification.php             # Email notification
└── Policies/
    └── InvitationPolicy.php                   # Authorization policy

config/
└── invitations.php                            # Configuration file

database/migrations/
└── 2025_12_18_000001_create_invitations_table.php

resources/views/filament/topbar/
├── invite-user-action.blade.php               # Livewire component view
└── invite-user.blade.php                      # (Deprecated)

routes/
└── web.php                                    # Public invitation route
```

## Configuration

### config/invitations.php

```php
return [
    'expires_in_hours' => env('INVITATION_EXPIRES_IN_HOURS', 72),
    'default_role_slug' => env('INVITATION_DEFAULT_ROLE', 'members'),
    'cleanup_after_days' => env('INVITATION_CLEANUP_DAYS', 30),
];
```

### Environment Variables (.env)

```env
INVITATION_EXPIRES_IN_HOURS=72
INVITATION_DEFAULT_ROLE=members
INVITATION_CLEANUP_DAYS=30
```

## User Workflows

### 1. Sending an Invitation

**Actor**: Supervisor

**Steps**:
1. Click user-plus icon in top navigation bar
2. Modal opens with form fields:
   - Name (Required)
   - Email (Required, must be unique among pending invitations)
   - Personal Message (Optional, max 500 chars)
3. Submit form
4. System generates:
   - 64-character random token
   - Expiration timestamp (72 hours from now)
   - Creates invitation record with status "pending"
5. Sends email notification with invitation link
6. Shows success notification: "Invitation sent!"

**Validation Rules**:
- Name: Required, max 255 characters
- Email: Required, valid email format, unique constraint for pending invitations
- Message: Optional, max 500 characters

### 2. Accepting an Invitation (New User)

**Actor**: Invited User

**Steps**:
1. Clicks invitation link in email: `/invitations/{token}/accept`
2. System validates:
   - Token exists and status is "pending"
   - Invitation not expired
3. Stores invitation data in session:
   - `invitation_token`
   - `invitation_name`
   - `invitation_email`
4. Redirects to registration page `/admin/register`
5. Registration form pre-filled:
   - Name field: Default value from invitation
   - Email field: Default value, disabled (cannot be changed)
6. User enters password and confirms
7. On submit:
   - User account created
   - Email marked as verified (email_verified_at = now())
   - Default role assigned (from config)
   - Invitation record updated:
     - status = "accepted"
     - accepted_at = now()
     - accepted_by_user_id = new user's ID
   - Session data cleared
8. User automatically logged in
9. Shows notification: "Welcome to Világműhely!"
10. Redirects to admin dashboard

### 3. Accepting an Invitation (Existing User)

**Actor**: Invited User with Existing Account

**Steps**:
1. Clicks invitation link
2. System finds existing user with matching email
3. Assigns default role (if not already assigned)
4. Updates invitation to "accepted"
5. Shows notification: "Invitation accepted! You can now log in."
6. Redirects to login page

### 4. Managing Invitations (Admin)

**Actor**: Supervisor

**Access**: `/admin/invitations`

**Available Actions**:

**List View**:
- View all invitations with filters:
  - Status (pending/accepted/expired/cancelled)
  - Invited by (user filter)
  - Expired only toggle
- Sort by: created_at, expires_at, accepted_at, status
- Search by: name, email
- Badge count: Shows number of pending invitations

**Table Actions**:
- **View** (Slide-over): Shows full invitation details
- **Resend**: Sends invitation email again (only pending & not expired)
- **Cancel**: Sets status to "cancelled" (only pending)
- **Copy Link**: Copies invitation URL to clipboard with notification
- **Edit**: Modify invitation details
- **Delete**: Permanently remove invitation

**Bulk Actions**:
- Delete selected invitations
- Cancel selected invitations

### 5. Expiration & Cleanup

**Automatic Processes**:

1. **Daily Cleanup** (1:00 AM):
   - Command: `php artisan invitations:cleanup`
   - Marks expired pending invitations as "expired"
   - Deletes expired/cancelled invitations older than 30 days
   - Scheduled in `bootstrap/app.php`

2. **Real-time Expiration Check**:
   - `isExpired()` method checks expiration on access
   - Prevents acceptance of expired invitations

## Security Considerations

### Token Security
- **Generation**: 64-character random string using `Str::random(64)`
- **Uniqueness**: Database unique constraint on token column
- **One-time Use**: Status changed to "accepted" after use
- **Expiration**: Default 72 hours, configurable
- **No Predictability**: Cryptographically secure random generation

### Email Uniqueness
- **Pending Constraint**: Only one pending invitation per email
- **Validation Rule**: `unique:invitations,email,NULL,id,status,pending`
- **Prevents Spam**: Cannot flood user with multiple invitations

### Access Control
- **Policy-Based**: `InvitationPolicy` restricts all actions to supervisors
- **Methods Protected**: viewAny, view, create, update, delete, deleteAny
- **Check**: `$user->isSupervisor()` on all methods

### Data Protection
- **Foreign Keys**: Cascade deletes maintain referential integrity
- **Soft References**: accepted_by_user_id uses SET NULL on delete
- **Session Security**: Invitation data cleared after use
- **CSRF Protection**: All forms protected by Laravel middleware

## Integration Points

### 1. User Model Integration

**File**: `app/Models/User.php`

**Changes**:
- Added `email_verified_at` to `$fillable` array
- Custom `sendEmailVerificationNotification()` method uses `CustomVerifyEmail`
- Implements `MustVerifyEmail` interface

### 2. Role System Integration

**Dependencies**:
- `Webtechsolutions\UserManager\Models\Role`
- Default role must exist with slug matching config value
- Relationship: `$user->roles()->attach($roleId)`

### 3. Filament Panel Integration

**File**: `app/Providers/Filament/AdminPanelProvider.php`

**Integrations**:
- Custom registration page: `->registration(\App\Filament\Admin\Pages\Auth\Register::class)`
- Topbar render hook: `panels::topbar.end` → Livewire component
- Email verification enabled: `->emailVerification()`

### 4. Response Handlers

**Custom Responses Registered**:
- `RegistrationResponseContract` → `RegistrationResponse`
  - Checks if user has verified email (invited users)
  - Shows appropriate notification
  - Redirects to dashboard or login

- `EmailVerificationResponseContract` → `EmailVerificationResponse`
  - Ensures email_verified_at is set
  - Shows success notification
  - Redirects to dashboard

### 5. Notification System

**Email Template**: `InvitationNotification`
- Subject: "You have been invited to Világműhely"
- Greeting: "Hello {name}!"
- Body: "{inviter} has invited you to join Világműhely."
- Personal message included if provided
- Action button: "Accept Invitation" → invitation URL
- Expiration notice: "This invitation will expire {timeframe}."

## API/Routes

### Public Routes

```php
// GET /invitations/{token}/accept
Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])
    ->name('invitations.accept');
```

**Purpose**: Accept invitation and redirect to registration or login

**Parameters**:
- `token` (string, 64 chars): Unique invitation token

**Returns**:
- Redirect to registration (new user)
- Redirect to login (existing user)
- Redirect to login with error (expired/invalid)

### Admin Routes (Auto-generated by Filament)

- `/admin/invitations` - List invitations
- `/admin/invitations/create` - Create invitation
- `/admin/invitations/{id}/edit` - Edit invitation
- `/admin/invitations/{id}` - View invitation

## UI Components

### 1. Topbar Invite Action

**Component**: `app/Filament/Admin/Pages/InviteUserAction.php` (Livewire)

**View**: `resources/views/filament/topbar/invite-user-action.blade.php`

**Features**:
- Icon: `heroicon-o-user-plus`
- Tooltip: "Invite User"
- Modal width: Medium
- Form validation: Real-time
- Success notification on submit

### 2. Invitation Resource Table

**Columns**:
- Name (searchable, sortable, semibold)
- Email (searchable, sortable, copyable, envelope icon)
- Invited By (sortable, searchable)
- Status (badge with colors)
- Expires (datetime, color-coded)
- Accepted (datetime, placeholder if null)
- Sent (datetime, toggleable)

**Status Colors**:
- Pending: Warning (yellow)
- Accepted: Success (green)
- Expired: Danger (red)
- Cancelled: Gray

### 3. View Invitation (Slide-over)

**Sections**:

**Invitation Information**:
- Name
- Email (copyable)
- Message (placeholder if empty)
- Status (badge)

**Invitation Details**:
- Invited By
- Accepted By (placeholder if not accepted)
- Sent At
- Expires At (color-coded)
- Accepted At (placeholder if not accepted)
- Token (copyable, key icon)

## Error Handling

### Common Error Scenarios

1. **Expired Invitation**
   - Check: `$invitation->isExpired()`
   - Action: Set status to "expired"
   - Response: Redirect to login with error notification

2. **Invalid Token**
   - Check: Token not found in database
   - Response: 404 Not Found

3. **Already Accepted**
   - Check: Status is not "pending"
   - Response: Redirect to login (cannot accept twice)

4. **Duplicate Email (Pending)**
   - Check: Unique validation rule
   - Response: Form validation error
   - Message: "Email already has a pending invitation"

5. **Missing Default Role**
   - Check: Role with config slug exists
   - Action: Skip role assignment (log warning)
   - User still created but without role

## Testing Checklist

### Unit Tests
- [ ] Invitation model scopes (pending, expired, accepted)
- [ ] Token generation uniqueness
- [ ] Expiration logic
- [ ] Role assignment logic

### Feature Tests
- [ ] Send invitation via topbar action
- [ ] Email delivery with correct data
- [ ] Token acceptance (new user)
- [ ] Token acceptance (existing user)
- [ ] Expired token rejection
- [ ] Email uniqueness validation
- [ ] Registration form pre-fill
- [ ] Email field disabled for invited users
- [ ] Automatic email verification
- [ ] Role assignment
- [ ] Invitation record update on acceptance
- [ ] Cleanup command functionality

### Integration Tests
- [ ] Full workflow: Invite → Email → Accept → Login
- [ ] Resend invitation
- [ ] Cancel invitation
- [ ] Copy link functionality
- [ ] Bulk operations
- [ ] Policy enforcement (supervisors only)

### UI/UX Tests
- [ ] Topbar icon visible and clickable
- [ ] Modal opens with correct form
- [ ] Validation messages display correctly
- [ ] Success notifications appear
- [ ] Slide-over view renders properly
- [ ] Badge counts update correctly
- [ ] Filters and search work
- [ ] Copy link shows notification

## Performance Considerations

### Database Optimization
- **Indexes**: token, expires_at, status, (email, status)
- **Queries**: Use scopes for common filters
- **Eager Loading**: Load relationships when needed

### Queue System
- Email notifications sent synchronously (fast enough)
- Could move to queue for high-volume scenarios

### Cleanup Strategy
- Daily cleanup at off-peak hours (1:00 AM)
- Only deletes old records (30+ days)
- Minimal performance impact

## Future Enhancements

### Planned Features
1. **Bulk Invitations**
   - CSV upload for multiple invites
   - Batch processing with progress bar

2. **Custom Roles per Invitation**
   - Dropdown to select role when inviting
   - Override default role setting

3. **Invitation Templates**
   - Pre-defined message templates
   - Personalization variables

4. **Analytics Dashboard**
   - Acceptance rate tracking
   - Time-to-accept metrics
   - Inviter leaderboard

5. **Reminders**
   - Automatic reminder email before expiration
   - Configurable reminder schedule

6. **Usage Limits**
   - Limit invitations per user
   - Rate limiting for invitation sends

7. **Referral Tracking**
   - Track who invited whom
   - Referral chains visualization
   - Reward system for successful invites

### Technical Improvements
- **Multi-tenancy Support**: Scope invitations by organization
- **API Endpoints**: RESTful API for invitation management
- **Webhooks**: Trigger external systems on invitation events
- **Audit Logging**: Detailed activity logs for compliance

## Dependencies

### Laravel Packages
- `filament/filament`: ^3.3
- `livewire/livewire`: (via Filament)
- `illuminate/notifications`: (Laravel core)
- `illuminate/mail`: (Laravel core)

### Custom Packages
- `webtechsolutions/user-manager`: Role management
- Mailer package: Email infrastructure (optional integration)

### PHP Requirements
- PHP 8.4.15 or higher
- Extensions: PDO, OpenSSL, Mbstring

## Deployment Checklist

### Before Deployment
- [ ] Run migrations: `php artisan migrate`
- [ ] Ensure "members" role exists in database
- [ ] Set environment variables in `.env`
- [ ] Test email delivery configuration
- [ ] Verify queue worker is running
- [ ] Schedule cron job for cleanup command

### After Deployment
- [ ] Clear caches: `php artisan config:clear`
- [ ] Cache Filament components: `php artisan filament:cache-components`
- [ ] Restart queue workers
- [ ] Test invitation flow end-to-end
- [ ] Verify scheduled tasks: `php artisan schedule:list`

### Production Configuration
```env
INVITATION_EXPIRES_IN_HOURS=72
INVITATION_DEFAULT_ROLE=members
INVITATION_CLEANUP_DAYS=30
MAIL_MAILER=smtp
QUEUE_CONNECTION=database
```

## Troubleshooting

### Common Issues

**Issue**: Invitation emails not sending
- **Check**: Mail configuration in `.env`
- **Check**: Queue worker running
- **Check**: `sent_emails` table for errors

**Issue**: Email verification not working
- **Check**: `CustomVerifyEmail` route matches Filament panel
- **Check**: `EmailVerificationResponse` registered in AppServiceProvider
- **Solution**: Clear config cache

**Issue**: Duplicate invitations allowed
- **Check**: Unique validation rule on email field
- **Check**: Database constraint on (email, status)
- **Solution**: Add missing index/constraint

**Issue**: Role not assigned
- **Check**: Default role exists with correct slug
- **Check**: Config value matches role slug
- **Solution**: Create role or update config

**Issue**: Token expired but still works
- **Check**: `isExpired()` method logic
- **Check**: Cleanup command running
- **Solution**: Run manual cleanup

## Conclusion

This invitation system provides a complete, secure, and user-friendly way to onboard new users to the Világműhely platform. It integrates seamlessly with Filament's authentication system while adding enterprise-grade features like expiration handling, role assignment, and comprehensive admin management.

The system is designed to be maintainable, testable, and extensible for future enhancements while maintaining security best practices and performance optimization.
