@php
    use App\Models\ContentDownload;
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="w-full">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 h-full flex flex-col">
        {{-- Image --}}
        <div class="aspect-video bg-gray-100 dark:bg-gray-700 relative flex-shrink-0">
            @if($getRecord()->featured_image)
                <img src="{{ Storage::url($getRecord()->featured_image) }}"
                     alt="{{ $getRecord()->title }}"
                     class="w-full h-full object-cover">
            @else
                <div class="w-full h-full flex items-center justify-center text-gray-400">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="p-4 flex-grow flex flex-col">
            {{-- Title --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                {{ $getRecord()->title }}
            </h3>

            {{-- Type Badge --}}
            <div class="mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ match($getRecord()->type_label) {
                        'Digital File (PDF, ZIP)' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'Image Gallery' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'Markdown Post' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'Long Article / Tutorial' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                        'RPG Module / Card Pack / Worldbuilding' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                    } }}">
                    {{ $getRecord()->type_label }}
                </span>
            </div>

            <div class="flex-grow"></div>

            {{-- Creator --}}
            <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 mb-2">
                <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="truncate">{{ $getRecord()->creator->name }}</span>
            </div>

            {{-- Rating --}}
            <div class="flex items-center text-sm mb-3">
                @if($getRecord()->average_rating > 0)
                    <div class="flex items-center text-yellow-500">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="ml-1 font-medium">{{ number_format($getRecord()->average_rating, 1) }}</span>
                        <span class="ml-1 text-gray-500 dark:text-gray-400">({{ $getRecord()->ratings->count() }})</span>
                    </div>
                @else
                    <span class="text-gray-500 dark:text-gray-400 text-xs">No ratings yet</span>
                @endif
            </div>
        </div>

    </div>
</div>
