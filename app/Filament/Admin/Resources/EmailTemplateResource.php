<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Filament\Admin\Resources;

use App\Filament\Resources\EmailTemplateResource\RelationManagers;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()->isSupervisor();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->unique(ignoreRecord: true)
                    ->helperText('Unique slug identifier (lowercase letters, numbers, hyphens only). Cannot be changed after creation.')
                    ->disabled(fn ($record) => $record !== null)
                    ->dehydrated(),
                Forms\Components\Textarea::make('description')
                    ->rows(2)
                    ->helperText('Document what this email template is for and when it should be used.')
                    ->columnSpanFull(),
                Forms\Components\Placeholder::make('variables_cheatsheet')
                    ->label('Available Variables')
                    ->content(new HtmlString('
                        <div class="text-sm">
                            <p class="mb-2 text-gray-600 dark:text-gray-400">Use these variable patterns in your subject and body:</p>
                            <ul class="space-y-1 list-disc list-inside text-gray-700 dark:text-gray-300">
                                <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ name }}</code> - User/recipient name</li>
                                <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ email }}</code> - User email address</li>
                                <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ order_number }}</code> - Order number</li>
                                <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ card_title }}</code> - Card title</li>
                                <li><code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-800 rounded">{{ game_name }}</code> - Game name</li>
                            </ul>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">Note: Variables must be passed when sending the email programmatically.</p>
                        </div>
                    '))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Use {{ variable_name }} for dynamic content.')
                    ->columnSpanFull(),
                Forms\Components\MarkdownEditor::make('body')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'heading',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'table',
                        'undo',
                    ])
                    ->helperText('Write email content using Markdown. Use {{ variable_name }} for dynamic content.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(60)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn ($record) => 'Preview: ' . $record->subject)
                    ->modalContent(function ($record) {
                        $html = Str::markdown($record->body);
                        return new HtmlString(view('emails.template-preview', [
                            'content' => $html,
                        ])->render());
                    })
                    ->modalWidth('4xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\EmailTemplateResource\Pages\ListEmailTemplates::route('/'),
            'create' => \App\Filament\Admin\Resources\EmailTemplateResource\Pages\CreateEmailTemplate::route('/create'),
            'edit' => \App\Filament\Admin\Resources\EmailTemplateResource\Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
