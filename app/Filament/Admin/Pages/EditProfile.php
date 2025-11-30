<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EditProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static string $view = 'filament.admin.pages.edit-profile';

    protected static ?string $navigationLabel = 'Edit Profile';

    protected static ?int $navigationSort = 99;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->form->fill([
            'avatar' => $user->avatar,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'city' => $user->city,
            'address' => $user->address,
            'social_media_links' => $user->social_media_links,
            'about' => $user->about,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->avatar()
                            ->directory('avatars')
                            ->imageEditor()
                            ->circleCropper()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', modifyRuleUsing: function ($rule) {
                                return $rule->ignore(Auth::id());
                            })
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->revealable()
                            ->helperText('Leave blank to keep current password'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->same('password')
                            ->maxLength(255)
                            ->revealable()
                            ->visible(fn ($get) => filled($get('password'))),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('mobile')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Social Media Links')
                    ->schema([
                        Forms\Components\Repeater::make('social_media_links')
                            ->schema([
                                Forms\Components\Select::make('platform')
                                    ->options([
                                        'facebook' => 'Facebook',
                                        'twitter' => 'Twitter / X',
                                        'instagram' => 'Instagram',
                                        'linkedin' => 'LinkedIn',
                                        'github' => 'GitHub',
                                        'youtube' => 'YouTube',
                                        'tiktok' => 'TikTok',
                                        'website' => 'Website',
                                        'other' => 'Other',
                                    ])
                                    ->required()
                                    ->searchable(),

                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->prefix('https://')
                                    ->placeholder('example.com/username'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->defaultItems(0)
                            ->addActionLabel('Add Social Media Link')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('About')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('about')
                            ->label('Biography')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();

        // Remove password_confirmation from data
        unset($data['password_confirmation']);

        // Remove password if empty
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        Notification::make()
            ->success()
            ->title('Profile updated')
            ->body('Your profile has been updated successfully.')
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide from sidebar navigation, will be in user menu
    }
}
