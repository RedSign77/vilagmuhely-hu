@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-all duration-200 h-full flex flex-col relative group hover:ring-2 hover:ring-amber-400">

        {{-- Action Buttons --}}
        @include('filament.tables.columns.content-cards._shared.action-buttons', ['record' => $record])

        {{-- Clickable Card Area --}}
        <div
            class="cursor-pointer flex-1 flex flex-col"
            wire:click="incrementContentView('{{ $record->getKey() }}'); mountTableAction('view', '{{ $record->getKey() }}')"
        >
            {{-- Featured Image --}}
            <div class="aspect-[3/4] bg-gray-100 dark:bg-gray-700 relative flex-shrink-0">
                @if($record->featured_image)
                    <img src="{{ Storage::url($record->featured_image) }}"
                         alt="{{ $record->title }}"
                         class="w-full h-full object-cover"
                         loading="lazy">
                @else
                    {{-- Gallery Icon Fallback --}}
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900 dark:to-green-800">
                        <svg class="w-16 h-16 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Content Section --}}
            <div class="p-4 flex-grow flex flex-col">
                {{-- Title --}}
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 break-words">
                    {{ $record->title }}
                </h3>

                {{-- Type Badge --}}
                @include('filament.tables.columns.content-cards._shared.type-badge', ['record' => $record])

                {{-- Gallery Count Indicator --}}
                @if($record->gallery_images)
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ count($record->gallery_images) }} {{ count($record->gallery_images) === 1 ? 'image' : 'images' }}
                        </span>
                    </div>
                @endif

                <div class="flex-grow"></div>

                {{-- Creator --}}
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-3">
                    <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="truncate">{{ $record->creator->name }}</span>
                </div>

                {{-- Stats Footer --}}
                @include('filament.tables.columns.content-cards._shared.stats-footer', ['record' => $record])
            </div>
        </div>
    </div>
</div>
