<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'label',
        'book_limit',
        'customer_limit',
        'show_ads',
        'monthly_price_bdt',
        'yearly_price_bdt',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'show_ads' => 'boolean',
            'is_active' => 'boolean',
            'book_limit' => 'integer',
            'customer_limit' => 'integer',
            'monthly_price_bdt' => 'integer',
            'yearly_price_bdt' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function paymentRequests()
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function priceFor(string $cycle): int
    {
        return $cycle === 'monthly' ? $this->monthly_price_bdt : $this->yearly_price_bdt;
    }
}
