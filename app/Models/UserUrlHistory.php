<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUrlHistory extends Model
{
    protected $table = 'user_url_history';

    protected $fillable = [
        'user_id',
        'old_username',
        'new_username',
        'changed_at',
        'redirect_enabled',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'redirect_enabled' => 'boolean',
    ];

    /**
     * Get the user this history belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Find redirect target for a username
     */
    public static function findRedirectTarget(string $username): ?User
    {
        $history = static::where('old_username', $username)
            ->where('redirect_enabled', true)
            ->orderBy('changed_at', 'desc')
            ->first();

        return $history ? $history->user : null;
    }

    /**
     * Disable redirect
     */
    public function disableRedirect(): void
    {
        $this->update(['redirect_enabled' => false]);
    }
}
