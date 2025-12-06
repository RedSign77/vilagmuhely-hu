<?php

namespace Webtechsolutions\ContentEngine\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Content extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'type',
        'status',
        'excerpt',
        'body',
        'category_id',
        'creator_id',
        'file_path',
        'file_type',
        'file_size',
        'gallery_images',
        'metadata',
        'meta_title',
        'meta_description',
        'featured_image',
        'views_count',
        'downloads_count',
        'published_at',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'metadata' => 'array',
        'views_count' => 'integer',
        'downloads_count' => 'integer',
        'file_size' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Content type constants
     */
    public const TYPE_DIGITAL_FILE = 'digital_file';
    public const TYPE_IMAGE_GALLERY = 'image_gallery';
    public const TYPE_MARKDOWN_POST = 'markdown_post';
    public const TYPE_ARTICLE = 'article';
    public const TYPE_RPG_MODULE = 'rpg_module';

    /**
     * Content status constants
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PREVIEW = 'preview';
    public const STATUS_MEMBERS_ONLY = 'members_only';
    public const STATUS_PUBLIC = 'public';

    /**
     * Get content types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_DIGITAL_FILE => 'Digital File (PDF, ZIP)',
            self::TYPE_IMAGE_GALLERY => 'Image Gallery',
            self::TYPE_MARKDOWN_POST => 'Markdown Post',
            self::TYPE_ARTICLE => 'Long Article / Tutorial',
            self::TYPE_RPG_MODULE => 'RPG Module / Card Pack / Worldbuilding',
        ];
    }

    /**
     * Get content statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PREVIEW => 'Public Preview',
            self::STATUS_MEMBERS_ONLY => 'Members Only (Full)',
            self::STATUS_PUBLIC => 'Public (Full)',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($content) {
            if (empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
            if (empty($content->creator_id) && auth()->check()) {
                $content->creator_id = auth()->id();
            }
        });

        static::updating(function ($content) {
            if ($content->isDirty('title') && empty($content->slug)) {
                $content->slug = Str::slug($content->title);
            }
        });
    }

    /**
     * Get the creator (user) of the content
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the category of the content
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    /**
     * Get the tags for the content
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContentTag::class, 'content_tag', 'content_id', 'content_tag_id')
            ->withTimestamps();
    }

    /**
     * Get ratings for this content
     */
    public function ratings()
    {
        return $this->hasMany(ContentRating::class);
    }

    /**
     * Get average rating for this content
     */
    public function getAverageRatingAttribute(): ?float
    {
        $average = $this->ratings()->avg('rating');
        return $average ? round($average, 2) : null;
    }

    /**
     * Get count of helpful ratings
     */
    public function getHelpfulRatingsCountAttribute(): int
    {
        return $this->ratings()->where('is_helpful', true)->count();
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to only published content
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to only public content
     */
    public function scopePublic($query)
    {
        return $query->where('status', self::STATUS_PUBLIC);
    }

    /**
     * Scope to content available for members
     */
    public function scopeForMembers($query)
    {
        return $query->whereIn('status', [
            self::STATUS_MEMBERS_ONLY,
            self::STATUS_PUBLIC,
        ]);
    }

    /**
     * Increment view count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment download count
     */
    public function incrementDownloads(): void
    {
        $this->increment('downloads_count');
    }

    /**
     * Check if content is published
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at <= now();
    }

    /**
     * Check if content is public
     */
    public function isPublic(): bool
    {
        return $this->status === self::STATUS_PUBLIC;
    }

    /**
     * Check if content requires authentication
     */
    public function requiresAuth(): bool
    {
        return in_array($this->status, [
            self::STATUS_MEMBERS_ONLY,
            self::STATUS_PREVIEW,
        ]);
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }
}
