<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BookShare extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'book_id',
        'shared_by_user_id',
        'shared_to_user_id',
        'permission',
        'status',
        'shared_at',
        'revoked_at',
    ];

    protected $casts = [
        'shared_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the book that is being shared
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user who shared the book (owner)
     */
    public function sharedByUser()
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    /**
     * Get the user who received the shared book
     */
    public function sharedToUser()
    {
        return $this->belongsTo(User::class, 'shared_to_user_id');
    }

    /**
     * Scope to get only active shares
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Revoke the share
     */
    public function revoke()
    {
        $this->status = 'revoked';
        $this->revoked_at = now();
        $this->save();
    }
}
