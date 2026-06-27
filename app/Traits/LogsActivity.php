<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAuditAttributes());
        });

        static::updated(function ($model) {
            if ($model->isNotAuditableChange()) {
                return;
            }
            $changed = $model->getAuditChanges();
            if (!empty($changed['old']) || !empty($changed['new'])) {
                $model->logAudit('updated', $changed['old'], $changed['new']);
            }
        });

        static::deleted(function ($model) {
            // Avoid double-logging when soft-delete is just an update
            if ($model->isForceDeleting() || !method_exists($model, 'isForceDeleting') || !$model->isForceDeleting()) {
                if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model))) {
                    $model->logAudit('deleted', $model->getAuditAttributes(), null);
                }
            }
        });

        // Soft delete specific
        static::retrieved(function ($model) {
            // no-op, but needed to keep the boot method
        });
    }

    protected function logAudit(string $action, ?array $oldValues, ?array $newValues): void
    {
        if (app()->runningInConsole() && $action !== 'deleted') {
            // Don't log background job updates (e.g. provider sync) unless it's a delete
            return;
        }

        AuditLog::create([
            'user_id'        => Auth::id(),
            'action'         => $action,
            'auditable_type' => static::class,
            'auditable_id'   => $this->getKey(),
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }

    protected function getAuditAttributes(): array
    {
        return $this->attributesToArray();
    }

    protected function getAuditChanges(): array
    {
        $old = [];
        $new = [];
        foreach ($this->getChanges() as $key => $value) {
            if (in_array($key, $this->getAuditIgnore())) {
                continue;
            }
            $old[$key] = $this->getOriginal($key);
            $new[$key] = $value;
        }
        return ['old' => $old, 'new' => $new];
    }

    protected function isNotAuditableChange(): bool
    {
        $changes = $this->getChanges();
        if (empty($changes)) {
            return true;
        }
        // Only updated_at change → skip
        return count($changes) === 1 && isset($changes['updated_at']);
    }

    protected function getAuditIgnore(): array
    {
        return ['updated_at'];
    }
}
