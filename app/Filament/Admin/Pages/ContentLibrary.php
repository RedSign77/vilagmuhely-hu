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
                    ->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
                    ->published()
                    ->with(['creator', 'category', 'ratings', 'reviews'])
            )
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=Content&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->limit(40),

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

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Creator')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('average_rating')
                    ->label('Rating')
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 1) . ' ★' : 'No ratings')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('downloaded')
                    ->label('✓')
                    ->boolean()
                    ->getStateUsing(fn (Content $record) => ContentDownload::hasUserDownloaded($record->id, auth()->id()))
                    ->alignCenter()
                    ->tooltip('Downloaded'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(Content::getTypes())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('has_file')
                    ->label('Has File')
                    ->query(fn (Builder $query) => $query->whereNotNull('file_path')),

                Tables\Filters\Filter::make('downloaded_by_me')
                    ->label('Downloaded by Me')
                    ->query(fn (Builder $query) => $query->whereHas('downloads', fn ($q) => $q->where('user_id', auth()->id()))),

                Tables\Filters\Filter::make('high_rated')
                    ->label('High Rated (4+ Stars)')
                    ->query(function (Builder $query) {
                        return $query->whereHas('ratings', function ($q) {
                            $q->havingRaw('AVG(rating) >= 4');
                        });
                    }),
            ])
            ->actions([
                ViewAction::make()
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
                    ]),

                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->disabled(fn (Content $record) => ! Gate::allows('download', $record))
                    ->tooltip(fn (Content $record) => Gate::allows('download', $record) ? null : 'Download first to access this content')
                    ->visible(fn (Content $record) => ! empty($record->file_path))
                    ->requiresConfirmation()
                    ->action(function (Content $record) {
                        // Record the download
                        ContentDownload::recordDownload($record->id, auth()->id());

                        // Increment download counter
                        $record->incrementDownloads();

                        // Fire event
                        event(new ContentDownloadedEvent($record));

                        Notification::make()
                            ->title('Download recorded')
                            ->success()
                            ->send();

                        // Trigger download
                        return response()->download(storage_path('app/public/' . $record->file_path));
                    }),

                Tables\Actions\Action::make('rate')
                    ->label('Rate')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->disabled(fn (Content $record) => ! Gate::allows('rate', $record))
                    ->tooltip(function (Content $record) {
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
                    ->label('Review')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->disabled(fn (Content $record) => ! Gate::allows('review', $record))
                    ->tooltip(function (Content $record) {
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
            ->poll('30s');
    }
}
