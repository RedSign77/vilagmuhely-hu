<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Webtechsolutions\ContentEngine\Models\Content;

class Post extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'related_content_ids',
        'featured_image',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'related_content_ids' => 'array',
    ];

    protected static function booted(): void
    {
        // Auto-generate slug if not provided
        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            // Ensure unique slug
            $originalSlug = $post->slug;
            $count = 1;
            while (static::where('slug', $post->slug)->exists()) {
                $post->slug = $originalSlug.'-'.$count++;
            }
        });

        static::updating(function (Post $post) {
            if ($post->isDirty('title') && ! $post->isDirty('slug')) {
                $post->slug = Str::slug($post->title);

                // Ensure unique slug
                $originalSlug = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->where('id', '!=', $post->id)->exists()) {
                    $post->slug = $originalSlug.'-'.$count++;
                }
            }
        });
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope to only include published posts.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to only include draft posts.
     */
    public function scopeDraft(Builder $query): void
    {
        $query->where('status', 'draft');
    }

    /**
     * Scope to only include archived posts.
     */
    public function scopeArchived(Builder $query): void
    {
        $query->where('status', 'archived');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Get related contents (manual selection or auto-discovery)
     */
    public function getRelatedContents(int $limit = 3): Collection
    {
        // Priority 1: Manually selected related contents
        if (! empty($this->related_content_ids)) {
            $selected = Content::whereIn('id', $this->related_content_ids)
                ->whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
                ->orderByRaw('FIELD(id, '.implode(',', $this->related_content_ids).')')
                ->take($limit)
                ->get();

            if ($selected->count() >= $limit) {
                return $selected;
            }

            // If we have some but not enough, fill remaining with auto-discovery
            $remaining = $limit - $selected->count();
            $excludeIds = array_merge($this->related_content_ids, $selected->pluck('id')->toArray());

            $autoDiscovered = $this->discoverRelatedContents($remaining, $excludeIds);

            return $selected->concat($autoDiscovered);
        }

        // Priority 2: Auto-discovery based on keywords
        return $this->discoverRelatedContents($limit);
    }

    /**
     * Auto-discover related contents based on keywords and similarity
     */
    protected function discoverRelatedContents(int $limit = 3, array $excludeIds = []): Collection
    {
        // Extract keywords from post title and content
        $keywords = $this->extractKeywords();

        if (empty($keywords)) {
            return $this->getFallbackContents($limit, $excludeIds);
        }

        // Search for contents matching keywords
        $query = Content::whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY])
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('title', 'LIKE', "%{$keyword}%")
                        ->orWhere('body', 'LIKE', "%{$keyword}%")
                        ->orWhere('excerpt', 'LIKE', "%{$keyword}%");
                }
            });

        if (! empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->orderBy('views_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Extract keywords from post for matching
     */
    protected function extractKeywords(): array
    {
        // Simple keyword extraction from title and first paragraph
        $text = $this->title.' '.strip_tags($this->excerpt ?? '');

        // Remove common words (stopwords)
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'can', 'this', 'that', 'these', 'those'];

        // Extract words
        preg_match_all('/\b\w{4,}\b/u', strtolower($text), $matches);
        $words = $matches[0] ?? [];

        // Remove stopwords and get top 5 most frequent
        $filtered = array_diff($words, $stopwords);
        $counts = array_count_values($filtered);
        arsort($counts);

        return array_slice(array_keys($counts), 0, 5);
    }

    /**
     * Get fallback contents when no keywords match
     */
    protected function getFallbackContents(int $limit = 3, array $excludeIds = []): Collection
    {
        // Return most popular public contents as fallback
        $query = Content::whereIn('status', [Content::STATUS_PUBLIC, Content::STATUS_MEMBERS_ONLY]);

        if (! empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->orderBy('views_count', 'desc')
            ->take($limit)
            ->get();
    }
}
