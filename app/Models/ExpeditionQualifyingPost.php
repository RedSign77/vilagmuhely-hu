<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webtechsolutions\BlogPackage\Models\Post;

class ExpeditionQualifyingPost extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'expedition_id',
        'enrollment_id',
        'post_id',
        'qualified_at',
    ];

    protected $casts = [
        'qualified_at' => 'datetime',
    ];

    /**
     * Get the expedition this qualification belongs to
     */
    public function expedition(): BelongsTo
    {
        return $this->belongsTo(Expedition::class);
    }

    /**
     * Get the enrollment this qualification belongs to
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ExpeditionEnrollment::class, 'enrollment_id');
    }

    /**
     * Get the post that qualified
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
