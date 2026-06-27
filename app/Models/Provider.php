<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

#[Fillable(['name', 'base_url', 'token', 'image', 'sync_active', 'status', 'balance'])]
#[Hidden(['token'])]
class Provider extends Model
{
    use SoftDeletes, LogsActivity;

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function casts(): array
    {
        return [
            'sync_active' => 'boolean',
            'balance' => 'decimal:2',
        ];
    }
}
