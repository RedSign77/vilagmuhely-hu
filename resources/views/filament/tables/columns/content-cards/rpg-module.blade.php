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
             wire:click="incrementContentView('{{ $record->getKey() }}'); $wire.mountTableAction('view', '{{ $record->getKey() }}')"
        >
            {{-- Featured Image with RPG themed fallback --}}
            <div class="aspect-[3/4] bg-gradient-to-br from-red-900 to-purple-900 relative flex-shrink-0">
                @if($record->featured_image)
                    <img src="{{ Storage::url($record->featured_image) }}"
                         alt="{{ $record->title }}"
                         class="w-full h-full object-cover"
                         loading="lazy">
                @else
                    {{-- RPG themed placeholder --}}
                    <div class="w-full h-full flex items-center justify-center text-red-200">
                        {{-- Dice/Puzzle icon --}}
                        <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
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

                {{-- Category Badge (prominent for RPG modules) --}}
                @if($record->category)
                    <div class="mb-2">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            {{ $record->category->name }}
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

                {{-- Tags (first 3) --}}
                @if($record->tags && $record->tags->count() > 0)
                    <div class="flex flex-wrap gap-1 mb-3">
                        @foreach($record->tags->take(3) as $tag)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                #{{ $tag->name }}
                            </span>
                        @endforeach
                        @if($record->tags->count() > 3)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                +{{ $record->tags->count() - 3 }}
                            </span>
                        @endif
                    </div>
                @endif

                {{-- Stats Footer --}}
                @include('filament.tables.columns.content-cards._shared.stats-footer', ['record' => $record])
            </div>
        </div>
    </div>
</div>
