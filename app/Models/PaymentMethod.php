<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = ['code', 'name', 'is_active', 'sort'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(PaymentAddress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
