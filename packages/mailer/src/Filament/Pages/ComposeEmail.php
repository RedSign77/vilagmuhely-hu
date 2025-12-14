<?php

namespace Webtechsolutions\Mailer\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Webtechsolutions\Mailer\Jobs\SendEmailJob;
use Webtechsolutions\Mailer\Models\EmailTemplate;
use Webtechsolutions\Mailer\Models\SentEmail;

class ComposeEmail extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string $view = 'mailer::filament.pages.compose-email';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Compose Email';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSupervisor() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('template_id')
                            ->label('Use Template')
                            ->options(EmailTemplate::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $template = EmailTemplate::find($state);
                                    if ($template) {
                                        $set('subject', $template->subject);
                                        $set('body', $template->body);
                                    }
                                }
                            })
                            ->helperText('Optional: Select a template to pre-fill subject and body'),

                        Forms\Components\Select::make('recipients')
                            ->label('Recipients')
                            ->multiple()
                            ->searchable()
                            ->options(User::all()->pluck('email', 'id'))
                            ->required()
                            ->helperText('Select one or more users to send email to'),

                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\MarkdownEditor::make('body')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Use Markdown formatting'),

                        Forms\Components\Toggle::make('send_test')
                            ->label('Send test email to yourself')
                            ->helperText('Send a test email to your account before sending to recipients'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $recipients = User::whereIn('id', $data['recipients'])->get();

        $sentCount = 0;

        foreach ($recipients as $recipient) {
            // Replace variables in template
            $body = $data['body'];
            $subject = $data['subject'];

            $variables = [
                'name' => $recipient->name,
                'email' => $recipient->email,
            ];

            foreach ($variables as $key => $value) {
                $body = str_replace('{'.$key.'}', $value, $body);
                $subject = str_replace('{'.$key.'}', $value, $subject);
            }

            // Create sent email record
            $sentEmail = SentEmail::create([
                'user_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'recipient_name' => $recipient->name,
                'subject' => $subject,
                'body' => $body,
                'email_template_id' => $data['template_id'] ?? null,
                'status' => 'queued',
            ]);

            // Dispatch job to queue
            SendEmailJob::dispatch($sentEmail->id);

            $sentCount++;
        }

        Notification::make()
            ->success()
            ->title('Emails queued successfully')
            ->body("$sentCount email(s) have been queued for sending.")
            ->send();

        $this->form->fill();
    }

    public function sendTest(): void
    {
        $data = $this->form->getState();

        $currentUser = auth()->user();

        // Replace variables
        $body = $data['body'];
        $subject = '[TEST] '.$data['subject'];

        $variables = [
            'name' => $currentUser->name,
            'email' => $currentUser->email,
        ];

        foreach ($variables as $key => $value) {
            $body = str_replace('{'.$key.'}', $value, $body);
        }

        // Create sent email record
        $sentEmail = SentEmail::create([
            'user_id' => $currentUser->id,
            'recipient_email' => $currentUser->email,
            'recipient_name' => $currentUser->name,
            'subject' => $subject,
            'body' => $body,
            'email_template_id' => $data['template_id'] ?? null,
            'status' => 'queued',
        ]);

        // Dispatch job to queue
        SendEmailJob::dispatch($sentEmail->id);

        Notification::make()
            ->success()
            ->title('Test email sent')
            ->body('Check your inbox for the test email.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('send')
                ->label('Send Emails')
                ->action('send')
                ->requiresConfirmation()
                ->color('primary'),

            Forms\Components\Actions\Action::make('sendTest')
                ->label('Send Test')
                ->action('sendTest')
                ->color('gray'),
        ];
    }
}
