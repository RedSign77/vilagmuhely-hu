<div class="content-card">
    <div class="content-card-header">
        <span class="content-type-badge">{{ $content->type }}</span>
        @if($content->category)
            <span class="content-category" style="color: {{ $content->category->color }}">
                {{ $content->category->name }}
            </span>
        @endif
    </div>

    <h3 class="content-card-title">{{ $content->title }}</h3>

    @if($content->description)
        <p class="content-card-description">{{ Str::limit($content->description, 100) }}</p>
    @endif

    <div class="content-card-stats">
        <span>👁️ {{ number_format($content->views_count ?? 0) }}</span>
        <span>⬇️ {{ number_format($content->downloads_count ?? 0) }}</span>
    </div>

    <div class="content-card-actions">
        <a href="{{ route('library.index') }}?highlight={{ $content->id }}" class="btn-view">View</a>
    </div>
</div>
