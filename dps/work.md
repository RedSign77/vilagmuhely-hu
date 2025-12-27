# Work items

## Completed - 2025-12-27

### Bug fixes
- ✓ Invitation Resend action visibility fixed (formatting and logic clarity improved)

### Features
- ✓ Daily cronjob (09:00) to send invitation reminder emails for pending users expiring within 24 hours
- ✓ Daily cronjob (01:00) to mark expired invitations and cleanup old records
- ✓ Added `reminded_at` tracking to prevent duplicate reminder emails
- ✓ Enhanced InvitationNotification to support reminder mode with distinct subject/content

