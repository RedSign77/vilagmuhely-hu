<div class="review-card">
    <div class="review-header">
        <h4 class="review-title">{{ $review->title ?? 'Review' }}</h4>
        <span class="review-helpful">👍 {{ $review->helpful_votes }}</span>
    </div>

    <p class="review-text">{{ Str::limit($review->review_text, 200) }}</p>

    <div class="review-footer">
        <span class="review-content">On: <a href="{{ route('library.index') }}?highlight={{ $review->content->id }}">{{ $review->content->title }}</a></span>
        <time class="review-date">{{ $review->created_at->diffForHumans() }}</time>
    </div>
</div>
