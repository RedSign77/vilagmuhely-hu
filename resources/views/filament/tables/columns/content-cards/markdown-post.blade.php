@php
    use Illuminate\Support\Str;
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
                    {{-- Markdown Icon Fallback --}}
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900 dark:to-yellow-800">
                        <svg class="w-16 h-16 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
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

                {{-- Word Count Indicator --}}
                @php
                    $bodyHtml = $record->body ? Str::markdown($record->body) : '';
                    $wordCount = str_word_count(strip_tags($bodyHtml));
                @endphp
                @if($wordCount > 0)
                    <div class="mb-2">
                        <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            ~{{ number_format($wordCount) }} words
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
