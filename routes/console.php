<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Provider balance sync
Schedule::command('providers:sync-balance')->hourly();

// Check pending fulfillment orders
Schedule::command('orders:check-pending-fulfillment')->everyFiveMinutes();

// Retry pending Sham Cash deposit verifications
Schedule::command('sham-cash:retry-pending')->everyMinute();

// Sync incoming Sham Cash transactions
Schedule::command('sham-cash:sync-incoming')->everyFiveMinutes();

// Daily database backup at 3 AM (keep last 14 backups)
Schedule::command('db:backup --keep=14')->dailyAt('03:00');

// Prune audit logs older than 90 days weekly (Sunday at 4 AM)
Schedule::command('model:prune', ['--model' => [\App\Models\AuditLog::class]])->weeklyOn(0, '04:00');
