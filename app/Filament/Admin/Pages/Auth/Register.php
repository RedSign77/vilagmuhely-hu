<?php

namespace App\Filament\Admin\Pages\Auth;

use App\Models\Invitation;
use Filament\Forms;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Webtechsolutions\UserManager\Models\Role;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema($this->getFormSchema())
                    ->statePath('data')
            ),
        ];
    }

    protected function getFormSchema(): array
    {
        $invitationName = session('invitation_name');
        $invitationEmail = session('invitation_email');

        return [
            $this->getNameFormComponent()
                ->default($invitationName),
            $this->getEmailFormComponent()
                ->default($invitationEmail)
                ->disabled(fn () => session()->has('invitation_token'))
                ->dehydrated(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
            Forms\Components\Checkbox::make('terms_accepted')
                ->label(new HtmlString('I accept the <a href="https://webtech-solutions.hu/terms-and-conditions" target="_blank" class="text-primary-600 hover:underline">Terms and Conditions</a> and <a href="https://webtech-solutions.hu/privacy-policy" target="_blank" class="text-primary-600 hover:underline">Privacy Policy</a>'))
                ->accepted()
                ->validationAttribute('terms and conditions')
                ->required(),
        ];
    }

    protected function handleRegistration(array $data): Model
    {
        $user = $this->getUserModel()::create($data);

        // Assign default role to all users
        $defaultRole = Role::where('slug', config('invitations.default_role_slug'))->first();

        if ($defaultRole) {
            $user->roles()->attach($defaultRole->id);
        }

        // Check if user came from invitation
        $token = session('invitation_token');
        $isInvited = false;

        if ($token) {
            $invitation = Invitation::where('token', $token)
                ->where('email', $user->email)
                ->where('status', 'pending')
                ->first();

            if ($invitation && ! $invitation->isExpired()) {
                $isInvited = true;

                // Mark email as verified for invited users
                $user->email_verified_at = now();
                $user->save();

                // Update invitation record
                $invitation->update([
                    'status' => 'accepted',
                    'accepted_at' => now(),
                    'accepted_by_user_id' => $user->id,
                ]);

                session()->forget(['invitation_token', 'invitation_name', 'invitation_email']);
            }
        }

        return $user;
    }

    protected function sendEmailVerificationNotification(\Illuminate\Database\Eloquent\Model $user): void
    {
        // Don't send verification email to invited users (already verified)
        if ($user->hasVerifiedEmail()) {
            return;
        }

        // Use User model's sendEmailVerificationNotification which uses CustomVerifyEmail
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }
    }
}
