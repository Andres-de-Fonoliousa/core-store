<?php

namespace App\Services\Tenant;

use App\Models\Tenant;

class TenantManager
{
    private ?Tenant $current = null;

    private bool $platformMode = false;

    public function setCurrent(Tenant $tenant): void
    {
        $this->current = $tenant;
        $this->platformMode = false;
    }

    public function getCurrent(): ?Tenant
    {
        return $this->current;
    }

    public function getCurrentId(): ?int
    {
        return $this->current?->id;
    }

    public function isPlatformRequest(): bool
    {
        return $this->platformMode;
    }

    public function setPlatformMode(bool $mode = true): void
    {
        $this->platformMode = $mode;
        $this->current = null;
    }

    public function forTenant(Tenant $tenant, callable $fn): mixed
    {
        $previous = $this->current;
        $this->setCurrent($tenant);
        $result = $fn($tenant);
        $this->current = $previous;
        return $result;
    }
}
