<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Services\Tenant\TenantCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class TenantObserver
{
    public function __construct(
        private TenantCache $cache,
    ) {}

    public function updated(Tenant $tenant): void
    {
        if ($tenant->wasChanged('plan')) {
            $this->cache->flush($tenant);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'auditable_type' => Tenant::class,
                'auditable_id' => $tenant->id,
                'new_values' => ['plan' => $tenant->plan],
                'old_values' => ['plan' => $tenant->getOriginal('plan')],
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }
    }
}
