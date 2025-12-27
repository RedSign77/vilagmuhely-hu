<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PostResource\Pages;
use App\Filament\Admin\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Webtechsolutions\ContentEngine\Models\Content;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $modelLabel = 'Blog Post';

    protected static ?string $pluralModelLabel = 'Blog Posts';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->isSupervisor() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Post Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-generated from title, but can be manually overridden'),

                        Forms\Components\Textarea::make('excerpt')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText('Short summary of the post'),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ]),

                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->directory('blog/featured-images')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->helperText('Leave empty for immediate publishing when status is Published'),

                        Forms\Components\Select::make('author_id')
                            ->relationship('author', 'name')
                            ->required()
                            ->default(fn () => auth()->id())
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('SEO Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(255)
                            ->helperText('Auto-generated from title if left empty'),

                        Forms\Components\Textarea::make('meta_description')
                            ->rows(3)
                            ->maxLength(160)
                            ->helperText('Auto-generated from content if left empty (max 160 characters)'),

                        Forms\Components\TextInput::make('meta_keywords')
                            ->maxLength(255)
                            ->helperText('Comma-separated keywords for SEO'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Related Contents')
                    ->description('Select up to 3 related contents to display under the blog post')
                    ->schema([
                        Forms\Components\Select::make('related_content_ids')
                            ->label('Related Contents')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return Content::whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
                                    ->orderBy('title')
                                    ->pluck('title', 'id');
                            })
                            ->maxItems(3)
                            ->helperText('Select up to 3 contents to display. If left empty, the system will auto-suggest related contents based on post keywords.')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('auto_discovery_note')
                            ->label('')
                            ->content('**Auto-Discovery:** If you select fewer than 3 contents (or none), the system will automatically suggest related contents based on post title and content keywords.')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('author.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'warning' => 'archived',
                    ]),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('Not published'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('author')
                    ->relationship('author', 'name'),

                Tables\Filters\Filter::make('published')
                    ->query(fn (Builder $query): Builder => $query->published()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Post $record): string => route('blog.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Post $record): bool => $record->isPublished()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
