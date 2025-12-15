@php
    $record = $getRecord();
    $type = $record->type;
@endphp

@switch($type)
    @case('digital_file')
        @include('filament.tables.columns.content-cards.digital-file', ['record' => $record])
        @break
    @case('image_gallery')
        @include('filament.tables.columns.content-cards.image-gallery', ['record' => $record])
        @break
    @case('markdown_post')
        @include('filament.tables.columns.content-cards.markdown-post', ['record' => $record])
        @break
    @case('article')
        @include('filament.tables.columns.content-cards.article', ['record' => $record])
        @break
    @case('rpg_module')
        @include('filament.tables.columns.content-cards.rpg-module', ['record' => $record])
        @break
    @default
        {{-- Fallback to article card for unknown types --}}
        @include('filament.tables.columns.content-cards.article', ['record' => $record])
@endswitch
