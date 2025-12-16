<?php

namespace App\Filament\Admin\Pages;

use App\Models\ContentDownload;
use App\Models\ContentRating;
use App\Models\ContentReview;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Webtechsolutions\ContentEngine\Events\ContentDownloadedEvent;
use Webtechsolutions\ContentEngine\Events\ContentReviewedEvent;
use Webtechsolutions\ContentEngine\Models\Content;

class ContentLibrary extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.content-library';

    public static function getNavigationLabel(): string
    {
        return 'Content Library';
    }

    public function getTitle(): string
    {
        return 'Content Library';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Content::query()
                    ->where('status', '!=', Content::STATUS_DRAFT)
                    ->published()
                    ->with(['creator', 'category', 'ratings', 'reviews', 'tags'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->hidden(),
                Tables\Columns\TextColumn::make('excerpt')
                    ->searchable()
                    ->hidden(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->searchable()
                    ->hidden(),
                Tables\Columns\ViewColumn::make('content_card')
                    ->view('filament.tables.columns.content-card')
                    ->label(''),
            ])
            ->filters([

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Categories'),

                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->label('Tags'),

                Tables\Filters\SelectFilter::make('creator')
                    ->relationship('creator', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label('Creator'),

                Tables\Filters\SelectFilter::make('rating')
                    ->label('Rating')
                    ->options([
                        '5' => '5 Stars',
                        '4' => '4+ Stars',
                        '3' => '3+ Stars',
                        '2' => '2+ Stars',
                        '1' => '1+ Stars',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $rating = (int) $data['value'];
                            return $query->whereHas('ratings', function ($q) use ($rating) {
                                $q->select('content_id')
                                    ->groupBy('content_id')
                                    ->havingRaw('AVG(rating) >= ?', [$rating]);
                            });
                        }
                        return $query;
                    }),

                Tables\Filters\SelectFilter::make('type')
                    ->options(Content::getTypes())
                    ->multiple()
                    ->label('Type'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->hiddenLabel()
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->slideOver()
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Content Details')
                            ->schema([
                                \Filament\Infolists\Components\ImageEntry::make('featured_image')
                                    ->label('Featured Image')
                                    ->hiddenLabel()
                                    ->height(200),
                                \Filament\Infolists\Components\TextEntry::make('title')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                \Filament\Infolists\Components\TextEntry::make('excerpt')
                                    ->markdown()
                                    ->columnSpanFull(),
                                \Filament\Infolists\Components\TextEntry::make('type_label')
                                    ->label('Type')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('creator.name')
                                    ->label('Creator'),
                                \Filament\Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('average_rating')
                                    ->label('Average Rating')
                                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . ' ★' : 'No ratings'),
                                \Filament\Infolists\Components\TextEntry::make('views_count')
                                    ->label('Views'),
                                \Filament\Infolists\Components\TextEntry::make('downloads_count')
                                    ->label('Downloads'),
                                \Filament\Infolists\Components\TextEntry::make('published_at')
                                    ->label('Published')
                                    ->dateTime('M d, Y'),
                            ])
                            ->columns(2),
                    ])
                    ->modalFooterActions(fn (Content $record): array => [
                        Tables\Actions\Action::make('download')
                            ->label('Download')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('success')
                            ->disabled(fn () => ! Gate::allows('download', $record))
                            ->requiresConfirmation()
                            ->action(function () use ($record) {
                                $this->js('window.location.href = "' . route('content.download', $record) . '"');
                            }),
                        Tables\Actions\Action::make('rate')
                            ->label('Rate')
                            ->icon('heroicon-o-star')
                            ->color('warning')
                            ->disabled(fn () => ! Gate::allows('rate', $record))
                            ->tooltip(function () use ($record) {
                                if (! Gate::allows('rate', $record)) {
                                    if (! ContentDownload::hasUserDownloaded($record->id, auth()->id())) {
                                        return 'Download first to rate';
                                    }
                                    if ($record->creator_id === auth()->id()) {
                                        return 'Cannot rate own content';
                                    }
                                    if ($record->hasBeenRatedBy(auth()->id())) {
                                        return 'Already rated';
                                    }
                                }
                                return null;
                            })
                            ->form([
                                Forms\Components\Select::make('rating')
                                    ->label('Your Rating')
                                    ->options([
                                        1 => '1 Star - Poor',
                                        2 => '2 Stars - Fair',
                                        3 => '3 Stars - Good',
                                        4 => '4 Stars - Very Good',
                                        5 => '5 Stars - Excellent',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\Textarea::make('critique_text')
                                    ->label('Feedback (Optional)')
                                    ->rows(3)
                                    ->maxLength(500),
                            ])
                            ->action(function (array $data) use ($record) {
                                ContentRating::create([
                                    'content_id' => $record->id,
                                    'user_id' => auth()->id(),
                                    'rating' => $data['rating'],
                                    'critique_text' => $data['critique_text'] ?? null,
                                ]);

                                Notification::make()
                                    ->title('Rating submitted')
                                    ->success()
                                    ->send();
                            }),
                        Tables\Actions\Action::make('review')
                            ->label('Review')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->color('info')
                            ->disabled(fn () => ! Gate::allows('review', $record))
                            ->tooltip(function () use ($record) {
                                if (! Gate::allows('review', $record)) {
                                    if (! ContentDownload::hasUserDownloaded($record->id, auth()->id())) {
                                        return 'Download first to review';
                                    }
                                    if ($record->creator_id === auth()->id()) {
                                        return 'Cannot review own content';
                                    }
                                    if ($record->hasBeenReviewedBy(auth()->id())) {
                                        return 'Already reviewed';
                                    }
                                }
                                return null;
                            })
                            ->form([
                                Forms\Components\TextInput::make('title')
                                    ->label('Review Title (Optional)')
                                    ->maxLength(255),
                                Forms\Components\MarkdownEditor::make('review_text')
                                    ->label('Your Review')
                                    ->required()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'bulletList',
                                        'orderedList',
                                    ])
                                    ->minLength(50)
                                    ->maxLength(2000)
                                    ->helperText('Minimum 50 characters'),
                            ])
                            ->action(function (array $data) use ($record) {
                                $review = ContentReview::create([
                                    'content_id' => $record->id,
                                    'user_id' => auth()->id(),
                                    'title' => $data['title'] ?? null,
                                    'review_text' => $data['review_text'],
                                    'status' => ContentReview::STATUS_APPROVED,
                                ]);

                                // Fire event
                                event(new ContentReviewedEvent($record, $review));

                                Notification::make()
                                    ->title('Review submitted')
                                    ->success()
                                    ->send();
                            }),
                    ])
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Content Details')
                            ->schema([
                                \Filament\Infolists\Components\ImageEntry::make('featured_image')
                                    ->label('Featured Image')
                                    ->hiddenLabel()
                                    ->height(200),
                                \Filament\Infolists\Components\TextEntry::make('title')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpanFull(),
                                \Filament\Infolists\Components\TextEntry::make('excerpt')
                                    ->markdown()
                                    ->columnSpanFull(),
                                \Filament\Infolists\Components\TextEntry::make('type_label')
                                    ->label('Type')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('creator.name')
                                    ->label('Creator'),
                                \Filament\Infolists\Components\TextEntry::make('category.name')
                                    ->label('Category')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('average_rating')
                                    ->label('Average Rating')
                                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . ' ★' : 'No ratings'),
                                \Filament\Infolists\Components\TextEntry::make('views_count')
                                    ->label('Views'),
                                \Filament\Infolists\Components\TextEntry::make('downloads_count')
                                    ->label('Downloads'),
                                \Filament\Infolists\Components\TextEntry::make('published_at')
                                    ->label('Published')
                                    ->dateTime('M d, Y'),
                            ])
                            ->columns(2),
                    ]),

                Tables\Actions\Action::make('download')
                    ->hiddenLabel()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->disabled(fn (Content $record) => ! Gate::allows('download', $record))
                    ->requiresConfirmation()
                    ->action(function (Content $record) {
                        $this->js('window.location.href = "' . route('content.download', $record) . '"');
                    }),

                Tables\Actions\Action::make('rate')
                    ->hiddenLabel()
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->disabled(fn (Content $record) => ! Gate::allows('rate', $record))
                    ->form([
                        Forms\Components\Select::make('rating')
                            ->label('Your Rating')
                            ->options([
                                1 => '1 Star - Poor',
                                2 => '2 Stars - Fair',
                                3 => '3 Stars - Good',
                                4 => '4 Stars - Very Good',
                                5 => '5 Stars - Excellent',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Textarea::make('critique_text')
                            ->label('Feedback (Optional)')
                            ->rows(3)
                            ->maxLength(500),
                    ])
                    ->action(function (Content $record, array $data) {
                        ContentRating::create([
                            'content_id' => $record->id,
                            'user_id' => auth()->id(),
                            'rating' => $data['rating'],
                            'critique_text' => $data['critique_text'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Rating submitted')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('review')
                    ->hiddenLabel()
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->disabled(fn (Content $record) => ! Gate::allows('review', $record))
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label('Review Title (Optional)')
                            ->maxLength(255),
                        Forms\Components\MarkdownEditor::make('review_text')
                            ->label('Your Review')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ])
                            ->minLength(50)
                            ->maxLength(2000)
                            ->helperText('Minimum 50 characters'),
                    ])
                    ->action(function (Content $record, array $data) {
                        $review = ContentReview::create([
                            'content_id' => $record->id,
                            'user_id' => auth()->id(),
                            'title' => $data['title'] ?? null,
                            'review_text' => $data['review_text'],
                            'status' => ContentReview::STATUS_APPROVED,
                        ]);

                        // Fire event
                        event(new ContentReviewedEvent($record, $review));

                        Notification::make()
                            ->title('Review submitted')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->paginated([12, 24, 48, 96]);
    }

    public function getTableRecordKey($record): string
    {
        return (string) $record->getKey();
    }

    public function incrementContentView($recordId): void
    {
        $content = Content::find($recordId);
        if ($content) {
            $content->incrementViews();
        }
    }
}
