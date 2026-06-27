<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

#[Fillable(['provider_id', 'category_id', 'name', 'price', 'cost_price', 'external_id', 'image', 'params', 'qty_values', 'is_auto', 'status'])]
class Product extends Model
{
    use SoftDeletes, LogsActivity;

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'params' => 'array',
            'qty_values' => 'array',
            'is_auto' => 'boolean',
        ];
    }
}
