<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
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
}
