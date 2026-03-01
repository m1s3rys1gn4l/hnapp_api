<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Book extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'is_pinned',
        'default_client_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all shares for this book
     */
    public function shares()
    {
        return $this->hasMany(BookShare::class);
    }

    /**
     * Get active shares for this book
     */
    public function activeShares()
    {
        return $this->hasMany(BookShare::class)->where('status', 'active');
    }

    /**
     * Check if book is shared with a specific user
     */
    public function isSharedWith(string $userId): bool
    {
        return $this->activeShares()
            ->where('shared_to_user_id', $userId)
            ->exists();
    }

    /**
     * Get the owner of this book
     */
    public function getOwner(): User
    {
        return $this->user;
    }
}
