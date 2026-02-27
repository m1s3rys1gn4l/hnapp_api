<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firebase_uid',
        'name',
        'email',
        'phone',
        'linked_providers',
        'is_phone_verified',
        'phone_verified_at',
        'is_active',
        'disabled_at',
        'subscription_plan',
        'subscription_cycle',
        'book_limit',
        'customer_limit',
        'show_ads',
        'subscription_started_at',
        'subscription_expires_at',
    ];

    public const PLAN_DEFINITIONS = [
        'free' => [
            'label' => 'Free',
            'book_limit' => 10,
            'customer_limit' => 100,
            'show_ads' => true,
            'yearly_price_bdt' => 0,
            'monthly_price_bdt' => 0,
        ],
        'premium' => [
            'label' => 'Premium',
            'book_limit' => 50,
            'customer_limit' => 1000,
            'show_ads' => false,
            'yearly_price_bdt' => 500,
            'monthly_price_bdt' => 42,
        ],
        'business' => [
            'label' => 'Business',
            'book_limit' => null,
            'customer_limit' => null,
            'show_ads' => false,
            'yearly_price_bdt' => 800,
            'monthly_price_bdt' => 67,
        ],
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'linked_providers' => 'json',
            'is_phone_verified' => 'boolean',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'disabled_at' => 'datetime',
            'show_ads' => 'boolean',
            'subscription_started_at' => 'datetime',
            'subscription_expires_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function getPlanDefinition(string $plan): array
    {
        return self::PLAN_DEFINITIONS[$plan] ?? self::PLAN_DEFINITIONS['free'];
    }

    public function applyPlan(string $plan, ?string $cycle = null, bool $resetDates = true): void
    {
        $definition = self::getPlanDefinition($plan);

        $this->subscription_plan = $plan;
        $this->subscription_cycle = $plan === 'free' ? null : $cycle;
        $this->book_limit = $definition['book_limit'];
        $this->customer_limit = $definition['customer_limit'];
        $this->show_ads = $definition['show_ads'];

        if ($resetDates) {
            $this->subscription_started_at = now();
            if ($plan === 'free') {
                $this->subscription_expires_at = null;
            } elseif ($cycle === 'monthly') {
                $this->subscription_expires_at = now()->addMonth();
            } else {
                $this->subscription_expires_at = now()->addYear();
            }
        }
    }

    public function canCreateBook(): bool
    {
        $limit = $this->effectiveBookLimit();
        return $limit === null || $this->books()->count() < $limit;
    }

    public function canCreateClient(): bool
    {
        $limit = $this->effectiveCustomerLimit();
        return $limit === null || $this->clients()->count() < $limit;
    }

    public function effectiveBookLimit(): ?int
    {
        if ($this->book_limit !== null) {
            return (int) $this->book_limit;
        }

        $definition = self::getPlanDefinition($this->subscription_plan ?? 'free');
        return $definition['book_limit'];
    }

    public function effectiveCustomerLimit(): ?int
    {
        if ($this->customer_limit !== null) {
            return (int) $this->customer_limit;
        }

        $definition = self::getPlanDefinition($this->subscription_plan ?? 'free');
        return $definition['customer_limit'];
    }

    public function effectiveShowAds(): bool
    {
        if ($this->show_ads !== null) {
            return (bool) $this->show_ads;
        }

        $definition = self::getPlanDefinition($this->subscription_plan ?? 'free');
        return (bool) $definition['show_ads'];
    }
}
