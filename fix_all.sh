#!/bin/bash

cd /var/www/store-app

# ==========================================
# 1. Fix Pulse ingest interval (was 0 = every request)
# ==========================================
sed -i 's/^PULSE_INGEST_INTERVAL=0/PULSE_INGEST_INTERVAL=60/' .env

# ==========================================
# 2. Switch cache + session to Redis (faster than file)
# ==========================================
sed -i 's/^CACHE_STORE=file/CACHE_STORE=redis/' .env
sed -i 's/^SESSION_DRIVER=file/SESSION_DRIVER=redis/' .env

# ==========================================
# 3. Test provider API - fetch category names
# ==========================================
echo "=== Testing provider API ==="
php artisan tinker --execute="
\$p = DB::table('providers')->where('sync_active', true)->first();
echo 'Provider: ' . json_encode(\$p, JSON_UNESCAPED_UNICODE) . PHP_EOL;
if (\$p) {
    \$svc = new App\Services\World4CardService(App\Models\Provider::find(\$p->id));
    \$cats = \$svc->getContent(0);
    \$test = array_slice(\$cats['categories'] ?? [], 0, 3);
    echo 'Sample categories from provider: ' . json_encode(\$test, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
" 2>&1

echo ""
echo "=== Done! ==="
