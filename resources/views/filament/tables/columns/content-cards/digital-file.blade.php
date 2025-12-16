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
            {{-- Featured Image --}}
            <div class="aspect-[3/4] bg-gray-100 dark:bg-gray-700 relative flex-shrink-0">
                @if($record->featured_image)
                    <img src="{{ Storage::url($record->featured_image) }}"
                         alt="{{ $record->title }}"
                         class="w-full h-full object-cover"
                         loading="lazy">
                @else
                    {{-- File Type Icon Fallback --}}
                    <div class="w-full h-full flex flex-col items-center justify-center
                        {{ $record->file_type === 'pdf'
                            ? 'bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800'
                            : ($record->file_type === 'zip'
                                ? 'bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800'
                                : 'bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800')
                        }}">
                        @if($record->file_type === 'pdf')
                            <svg class="w-20 h-20 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                            </svg>
                            <span class="mt-2 px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded-full">PDF</span>
                        @elseif($record->file_type === 'zip')
                            <svg class="w-20 h-20 text-purple-600 dark:text-purple-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="mt-2 px-3 py-1 bg-purple-600 text-white text-sm font-medium rounded-full">ZIP</span>
                        @else
                            <svg class="w-20 h-20 text-gray-600 dark:text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                            <span class="mt-2 px-3 py-1 bg-gray-600 text-white text-sm font-medium rounded-full">FILE</span>
                        @endif
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

                {{-- File Info --}}
                <div class="flex flex-wrap gap-2 mb-3">
                    @if($record->file_type)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $record->file_type === 'pdf'
                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                : ($record->file_type === 'zip'
                                    ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')
                            }}">
                            {{ strtoupper($record->file_type) }}
                        </span>
                    @endif
                    @if($record->formatted_file_size)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $record->formatted_file_size }}
                        </span>
                    @endif
                </div>

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
