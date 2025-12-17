@extends('layouts.app')

@section('title', 'Content Library')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-purple-400 via-violet-400 to-indigo-400 bg-clip-text text-transparent mb-2">
            Content Library
        </h1>
        <p class="text-gray-300">
            Explore our latest public content from the community
        </p>
    </div>

    <div class="content-library-grid">
        @forelse($contents as $content)
            <article class="content-card" data-content-id="{{ $content->id }}">
                <div class="content-card-image">
                    @if($content->featured_image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($content->featured_image) }}"
                             alt="{{ $content->title }}"
                             loading="lazy">
                    @else
                        <div class="content-placeholder">
                            @if($content->type === 'digital_file')
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            @elseif($content->type === 'image_gallery')
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            @elseif($content->type === 'markdown_post')
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @elseif($content->type === 'article')
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            @elseif($content->type === 'rpg_module')
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="content-card-body">
                    <h3 class="content-title">{{ $content->title }}</h3>

                    <span class="content-type-badge {{ $content->type }}">
                        {{ $content->type_label }}
                    </span>

                    @if($content->category)
                        <div class="content-category">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            {{ $content->category->name }}
                        </div>
                    @endif

                    <button
                        class="view-details-btn"
                        onclick="openContentModal({{ $content->id }})"
                        aria-label="View details for {{ $content->title }}">
                        View Details
                    </button>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">
                    No public content available yet.
                </p>
            </div>
        @endforelse
    </div>
</div>

<div id="content-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="modal-title" class="text-xl font-bold text-white"></h2>
            <button onclick="closeContentModal()" class="modal-close" aria-label="Close modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <img id="modal-image" class="modal-featured-image" alt="" style="display: none;">
            <div id="modal-excerpt" class="modal-excerpt"></div>

            <div class="modal-meta">
                <span id="modal-type" class="content-type-badge"></span>
                <span id="modal-category" class="content-category-text"></span>
            </div>

            <div id="modal-tags" class="modal-tags" style="display: none;">
                <div class="text-sm mb-2" style="color: #d1d5db;">Tags:</div>
                <div id="modal-tags-list" class="flex flex-wrap gap-2"></div>
            </div>

            <div class="modal-stats">
                <div class="stat-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span id="modal-views">0</span>
                    <span class="text-xs">views</span>
                </div>
                <div class="stat-item">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span id="modal-downloads">0</span>
                    <span class="text-xs">downloads</span>
                </div>
                <div class="stat-item">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                    <span id="modal-rating" class="text-yellow-400 font-semibold">0.0</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const contentData = @json($contents->keyBy('id'));

    function convertMarkdownToHtml(markdown) {
        if (!markdown) return 'No description available';

        // Basic markdown conversions
        let html = markdown
            // Bold
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            // Italic
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            // Code
            .replace(/`(.+?)`/g, '<code>$1</code>')
            // Line breaks
            .replace(/\n/g, '<br>');

        return html;
    }

    function openContentModal(contentId) {
        const content = contentData[contentId];
        if (!content) return;

        document.getElementById('modal-title').textContent = content.title;

        // Convert markdown to HTML for excerpt
        const excerptHtml = convertMarkdownToHtml(content.excerpt);
        document.getElementById('modal-excerpt').innerHTML = excerptHtml;

        const modalType = document.getElementById('modal-type');
        modalType.textContent = content.type_label;
        modalType.className = 'content-type-badge ' + content.type;

        document.getElementById('modal-category').textContent = content.category?.name || 'Uncategorized';

        const modalImage = document.getElementById('modal-image');
        if (content.featured_image) {
            modalImage.src = '/storage/' + content.featured_image;
            modalImage.style.display = 'block';
        } else {
            modalImage.style.display = 'none';
        }

        // Populate tags
        const tagsContainer = document.getElementById('modal-tags');
        const tagsList = document.getElementById('modal-tags-list');
        if (content.tags && content.tags.length > 0) {
            tagsList.innerHTML = content.tags.map(tag =>
                `<span class="tag-badge">${tag.name}</span>`
            ).join('');
            tagsContainer.style.display = 'block';
        } else {
            tagsContainer.style.display = 'none';
        }

        // Populate stats
        document.getElementById('modal-views').textContent = content.views_count || 0;
        document.getElementById('modal-downloads').textContent = content.downloads_count || 0;
        document.getElementById('modal-rating').textContent = content.average_rating ? content.average_rating.toFixed(1) : '0.0';

        document.getElementById('content-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeContentModal() {
        document.getElementById('content-modal').style.display = 'none';
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeContentModal();
    });

    document.getElementById('content-modal')?.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) closeContentModal();
    });
</script>
@endpush
