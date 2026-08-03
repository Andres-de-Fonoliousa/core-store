<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'name', 'slug', 'subdomain', 'domain',
        'logo_url', 'favicon_url', 'brand_color', 'brand_color_dark',
        'locale', 'currency',
        'plan', 'status',
        'trial_ends_at', 'subscribed_at', 'expires_at',
        'settings', 'features',
        'platform_balance',
        'onboarding_completed_at', 'seen_onboarding_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'features' => 'array',
            'trial_ends_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'expires_at' => 'datetime',
            'platform_balance' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (! $tenant->uuid) {
                $tenant->uuid = (string) Str::uuid();
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role', 'invited_at', 'joined_at')
            ->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function domainAliases(): HasMany
    {
        return $this->hasMany(DomainAlias::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trial']);
    }
}
