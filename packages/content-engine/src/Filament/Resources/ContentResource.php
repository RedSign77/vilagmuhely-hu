<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentResource\Pages;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'My Content';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        // All users see only their own content count
        return static::getModel()::where('creator_id', auth()->id())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from title, but can be customized'),

                        Forms\Components\Select::make('type')
                            ->options(Content::getTypes())
                            ->required()
                            ->live()
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->options(function () {
                                $statuses = Content::getStatuses();

                                // Only administrators can set Public (Full) status
                                if (! auth()->user()?->isSupervisor()) {
                                    unset($statuses[Content::STATUS_PUBLIC]);
                                }

                                return $statuses;
                            })
                            ->required()
                            ->default(Content::STATUS_DRAFT)
                            ->native(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now())
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Featured Image')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->directory('content/featured')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('excerpt')
                            ->label('Excerpt')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                            ])
                            ->maxLength(2048)
                            ->helperText(fn ($state) => (strlen($state ?? '') . ' / 2048 characters'))
                            ->live(onBlur: true)
                            ->columnSpanFull(),

                        Forms\Components\MarkdownEditor::make('body')
                            ->label('Content Body')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'heading',
                                'bulletList',
                                'orderedList',
                                'codeBlock',
                                'table',
                            ])
                            ->visible(fn (Forms\Get $get) => in_array($get('type'), [
                                Content::TYPE_MARKDOWN_POST,
                                Content::TYPE_ARTICLE,
                                Content::TYPE_RPG_MODULE,
                            ]))
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Digital File')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Upload File')
                            ->disk('public')
                            ->directory('content/files')
                            ->acceptedFileTypes(['application/pdf', 'application/zip', 'application/x-zip-compressed'])
                            ->maxSize(65536) // 64MB in KB
                            ->columnSpanFull()
                            ->helperText('Accepted formats: PDF, ZIP (Max: 64MB)')
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state instanceof \Illuminate\Http\UploadedFile) {
                                    $set('file_type', $state->getClientOriginalExtension());
                                    $set('file_size', $state->getSize());
                                }
                            }),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === Content::TYPE_DIGITAL_FILE)
                    ->collapsible(),

                Forms\Components\Section::make('Image Gallery')
                    ->schema([
                        Forms\Components\FileUpload::make('gallery_images')
                            ->label('Gallery Images')
                            ->multiple()
                            ->image()
                            ->directory('content/galleries')
                            ->imageEditor()
                            ->reorderable()
                            ->maxFiles(50)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === Content::TYPE_IMAGE_GALLERY)
                    ->collapsible(),

                Forms\Components\Section::make('Categorization')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn () => auth()->user()?->isCreator() || auth()->user()?->isSupervisor() ? [
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Textarea::make('description'),
                                Forms\Components\ColorPicker::make('color')
                                    ->default('#6366f1'),
                            ] : null),

                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn () => auth()->user()?->isCreator() || auth()->user()?->isSupervisor() ? [
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\ColorPicker::make('color')
                                    ->default('#94a3b8'),
                            ] : null),

                        Forms\Components\Select::make('creator_id')
                            ->label('Creator')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id())
                            ->required()
                            ->disabled(fn () => ! auth()->user()?->isSupervisor())
                            ->dehydrated(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Content&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->limit(50),

                Tables\Columns\TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Digital File (PDF, ZIP)' => 'info',
                        'Image Gallery' => 'success',
                        'Markdown Post' => 'warning',
                        'Long Article / Tutorial' => 'primary',
                        'RPG Module / Card Pack / Worldbuilding' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Draft' => 'gray',
                        'Public Preview' => 'warning',
                        'Members Only (Full)' => 'info',
                        'Public (Full)' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creator')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable()
                    ->toggleable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(Content::getTypes())
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options(Content::getStatuses())
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('creator')
                    ->relationship('creator', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('published')
                    ->query(fn ($query) => $query->published())
                    ->label('Published Only'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // All users (including supervisors) only see their own content in "My Content"
        return parent::getEloquentQuery()->where('creator_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
