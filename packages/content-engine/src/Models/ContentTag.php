<?php

namespace Webtechsolutions\ContentEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ContentTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get contents with this tag
     */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_tag', 'content_tag_id', 'content_id')
            ->withTimestamps();
    }
}
