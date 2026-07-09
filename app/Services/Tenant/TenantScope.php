<?php

namespace App\Services\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

class TenantScope implements Scope
{
    private TenantManager $manager;

    private array $exceptTables;

    public function __construct(array $exceptTables = [])
    {
        $this->manager = App::make(TenantManager::class);
        $this->exceptTables = $exceptTables;
    }

    public function apply(Builder $query, Model $model): void
    {
        $tenantId = $this->manager->getCurrentId();

        if (!$tenantId) {
            return;
        }

        $model = $query->getModel();
        $table = $model->getTable();

        if (in_array($table, $this->exceptTables)) {
            return;
        }

        if (!$model->isTenantScoped()) {
            return;
        }

        if (method_exists($model, 'isTenantScopeNullable') && $model->isTenantScopeNullable()) {
            $query->where(function ($q) use ($table, $tenantId) {
                $q->where("{$table}.tenant_id", $tenantId)
                  ->orWhereNull("{$table}.tenant_id");
            });
        } else {
            $query->where("{$table}.tenant_id", $tenantId);
        }
    }
}
