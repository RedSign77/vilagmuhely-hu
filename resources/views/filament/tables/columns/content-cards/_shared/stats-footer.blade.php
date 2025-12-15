{{-- Stats Row --}}
<div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
    {{-- Rating --}}
    <div class="flex items-center gap-1" title="Average Rating">
        <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
        </svg>
        @if($record->average_rating > 0)
            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($record->average_rating, 1) }}</span>
            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $record->ratings->count() }})</span>
        @else
            <span class="text-xs text-gray-500 dark:text-gray-400">0</span>
        @endif
    </div>

    {{-- Downloads --}}
    <div class="flex items-center gap-1" title="Downloads">
        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($record->downloads_count) }}</span>
    </div>

    {{-- Reviews --}}
    <div class="flex items-center gap-1" title="Reviews">
        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->reviews->count() }}</span>
    </div>
</div>
