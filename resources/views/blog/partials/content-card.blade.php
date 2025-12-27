<div class="content-card bg-white/10 backdrop-blur-lg border border-white/20 rounded-lg shadow-lg overflow-hidden hover:border-purple-400 transition h-full flex flex-col">
    @if($content->featured_image)
        <div class="aspect-video overflow-hidden bg-gray-800">
            <img src="{{ asset('storage/' . $content->featured_image) }}"
                 alt="{{ $content->title }}"
                 class="w-full h-full object-cover">
        </div>
    @else
        <div class="aspect-video bg-gradient-to-br from-purple-900/50 to-blue-900/50 flex items-center justify-center">
            <svg class="w-16 h-16 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>
    @endif

    <div class="p-4 flex-1 flex flex-col">
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            <span class="px-2 py-1 bg-purple-600/30 text-purple-300 rounded text-xs font-semibold">
                {{ ucfirst(str_replace('_', ' ', $content->type)) }}
            </span>
            @if($content->category)
                <span class="px-2 py-1 rounded text-xs font-semibold" style="background-color: {{ $content->category->color }}20; color: {{ $content->category->color }};">
                    {{ $content->category->name }}
                </span>
            @endif
        </div>

        <h3 class="text-lg font-bold text-white mb-2 line-clamp-2">
            {{ $content->title }}
        </h3>

        @if($content->excerpt)
            <p class="text-sm text-gray-400 mb-4 line-clamp-2 flex-1">
                {{ Str::limit($content->excerpt, 100) }}
            </p>
        @endif

        <div class="flex items-center justify-between text-sm text-gray-400 mb-4">
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                {{ number_format($content->views_count ?? 0) }}
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                {{ number_format($content->downloads_count ?? 0) }}
            </span>
        </div>

        <a href="{{ route('library.index') }}?highlight={{ $content->id }}"
           class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition font-semibold">
            View Details
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>
</div>
