<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'cycle',
        'amount_bdt',
        'payment_method',
        'sender_number',
        'transaction_id',
        'screenshot_path',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_bdt' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
