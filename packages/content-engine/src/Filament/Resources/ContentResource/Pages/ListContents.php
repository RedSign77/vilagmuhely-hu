<?php

namespace Webtechsolutions\ContentEngine\Filament\Resources\ContentResource\Pages;

use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Webtechsolutions\ContentEngine\Filament\Resources\ContentResource;
use Webtechsolutions\ContentEngine\Models\Content;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Content'),

            'digital_files' => Tab::make('Digital Files')
                ->icon('heroicon-o-document-arrow-down')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Content::TYPE_DIGITAL_FILE))
                ->badge(Content::ofType(Content::TYPE_DIGITAL_FILE)->count()),

            'galleries' => Tab::make('Image Galleries')
                ->icon('heroicon-o-photo')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Content::TYPE_IMAGE_GALLERY))
                ->badge(Content::ofType(Content::TYPE_IMAGE_GALLERY)->count()),

            'posts' => Tab::make('Markdown Posts')
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Content::TYPE_MARKDOWN_POST))
                ->badge(Content::ofType(Content::TYPE_MARKDOWN_POST)->count()),

            'articles' => Tab::make('Articles')
                ->icon('heroicon-o-newspaper')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Content::TYPE_ARTICLE))
                ->badge(Content::ofType(Content::TYPE_ARTICLE)->count()),

            'rpg' => Tab::make('RPG Modules')
                ->icon('heroicon-o-puzzle-piece')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', Content::TYPE_RPG_MODULE))
                ->badge(Content::ofType(Content::TYPE_RPG_MODULE)->count()),

            'draft' => Tab::make('Drafts')
                ->icon('heroicon-o-pencil')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', Content::STATUS_DRAFT))
                ->badge(Content::withStatus(Content::STATUS_DRAFT)->count())
                ->badgeColor('gray'),

            'published' => Tab::make('Published')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->published())
                ->badge(Content::published()->count())
                ->badgeColor('success'),
        ];
    }
}
