<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Services\Tenant\TenantCache;

class TenantObserver
{
    public function __construct(
        private TenantCache $cache,
    ) {}

    public function updated(Tenant $tenant): void
    {
        if ($tenant->wasChanged('plan')) {
            $this->cache->flush($tenant);

            activity()
                ->performedOn($tenant)
                ->log("Plan changed to {$tenant->plan}");
        }
    }
}
