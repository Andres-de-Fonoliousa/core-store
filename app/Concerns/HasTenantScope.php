<?php

namespace App\Concerns;

use App\Services\Tenant\TenantManager;
use App\Services\Tenant\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

trait HasTenantScope
{
    public static function bootHasTenantScope(): void
    {
        static::addGlobalScope('tenant', App::make(TenantScope::class));
    }

    public function isTenantScoped(): bool
    {
        return true;
    }

    public function scopeWithoutTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->withoutTenant()->where((new static)->getTable() . '.tenant_id', $tenantId);
    }

    public static function resolveTenantId(): ?int
    {
        return App::make(TenantManager::class)->getCurrentId();
    }
}
