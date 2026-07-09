<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name', 'code', 'price_monthly', 'transaction_fee',
        'limits', 'features', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'float',
            'transaction_fee' => 'float',
            'limits' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public static function getByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }
}
