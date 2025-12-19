User Registration
There is a bug in the registration flow. When I click on the verify email link in email then i got the guest role, my user login in and the same time it's logged out. I think this is a root cause the email_verification_at field not update in the users table.

User Activity Logs
Issue: 
1. Fix the BadMethodCallException, vendor/livewire/livewire/src/Component.php:138, Method Webtechsolutions\UserManager\Filament\Resources\UserActivityLogResource\Pages\ListUserActivityLogs::notify does not exist. when I will try to Clean Up Old Logs.

Invitation System
Issue:
1. Multiple invitations can be sent to the same email address, it's a bug! Fix it: If an invitation is resent to an existing address, the previous record will be set to 'canceled' if it's not accepted. If the previous record is accepted then the invitation failing and showing an error notification.

Navigation in Admin
Issue 1: Capital Letters of the User's name showing in the user menu's circle. If the user has an avatar then the avatar image showing here, resized and cropped if needed.

Feature 1: Change the icon to the user's avatar (if uploaded) on the Dashboard before the My Dashboard title.
