# TenantStore — Multi-Tenant SaaS Engineering Plan

**Project**: Convert single-tenant digital goods store into a multi-tenant SaaS platform
**Source**: [CoreS] Store-app (Laravel 13 + Vue 3 + Inertia + Sanctum + Fortify)
**Timeline**: 8 weeks to MVP
**Stack**: Same as source (Laravel, Vue 3, Inertia, Tailwind, MySQL, Redis)

---

## Table of Contents

1. [Multi-Tenancy Strategy](#1-multi-tenancy-strategy)
2. [Database Schema](#2-database-schema)
3. [Backend Architecture](#3-backend-architecture)
4. [Frontend Architecture](#4-frontend-architecture)
5. [Platform Landing Page](#5-platform-landing-page)
6. [Billing & Subscription](#6-billing--subscription)
7. [Data Migration Strategy](#7-data-migration-strategy)
8. [DevOps & Infrastructure](#8-devops--infrastructure)
9. [Implementation Phases](#9-implementation-phases)
10. [Risk & Mitigation](#10-risk--mitigation)
11. [Revenue Model](#11-revenue-model)

---

## 1. Multi-Tenancy Strategy

**Approach**: Single database, shared tables with `tenant_id` column (row-level isolation).

**Why not a package** (stancl/tenancy, spatie/multitenancy): The app uses custom auth (Fortify + Sanctum hybrid), custom middleware, and Inertia SSR. Tenant packages fight all three. Rolling your own gives full control and is straightforward with this codebase.

### Directory structure addition

```
app/
├── Services/
│   └── Tenant/
│       ├── TenantManager.php         # Central tenant context singleton
│       ├── TenantResolver.php        # Resolves tenant from subdomain/domain
│       ├── TenantScope.php           # Global scope for tenant_id filtering
│       └── TenantCache.php           # Per-tenant cache tagging
├── Models/
│   └── Tenant.php                    # New: the tenant entity itself
├── Http/
│   └── Middleware/
│       └── IdentifyTenant.php        # Middleware: resolves and sets tenant context
├── Console/
│   └── Commands/
│       ├── TenantMigrate.php         # Run migrations for a specific tenant
│       └── TenantSeed.php            # Seed default data for new tenant
database/
├── migrations/
│   └── 2026_07_01_000001_create_tenants_table.php
└── migrations_tenant/                # Tenant-specific migrations (run per-tenant)
```

---

## 2. Database Schema

### New `tenants` table

```sql
CREATE TABLE tenants (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid            CHAR(36) NOT NULL UNIQUE,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL UNIQUE,
    subdomain       VARCHAR(255) NOT NULL UNIQUE,
    domain          VARCHAR(255) NULL UNIQUE,
    logo_url        VARCHAR(500) NULL,
    favicon_url     VARCHAR(500) NULL,
    brand_color     VARCHAR(7) DEFAULT '#22d3ee',
    brand_color_dark VARCHAR(7) DEFAULT '#06b6d4',
    locale          VARCHAR(10) DEFAULT 'ar',
    currency        VARCHAR(3) DEFAULT 'USD',
    plan            ENUM('free', 'pro', 'enterprise') DEFAULT 'free',
    status          ENUM('active', 'trial', 'suspended', 'cancelled') DEFAULT 'trial',
    trial_ends_at   TIMESTAMP NULL,
    subscribed_at   TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,
    settings        JSON NULL,
    features        JSON NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL
);
```

### `tenant_id` added to ALL business tables

| Table | Column | Notes |
|-------|--------|-------|
| `users` | `tenant_id` BIGINT UNSIGNED NOT NULL | Users belong to one tenant |
| `products` | `tenant_id` BIGINT UNSIGNED NOT NULL | Products belong to one tenant |
| `categories` | `tenant_id` BIGINT UNSIGNED NOT NULL | Categories belong to one tenant |
| `orders` | `tenant_id` BIGINT UNSIGNED NOT NULL | Orders belong to one tenant |
| `transactions` | `tenant_id` BIGINT UNSIGNED NOT NULL | Transactions belong to one tenant |
| `providers` | `tenant_id` BIGINT UNSIGNED NULL | Can be global or per-tenant |
| `notifications` | `tenant_id` BIGINT UNSIGNED NOT NULL | Notifications scoped to tenant |
| `payment_methods` | `tenant_id` BIGINT UNSIGNED NULL | Global defaults or per-tenant |

### New pivot / associative tables

```sql
-- Which users belong to which tenant and their role
CREATE TABLE tenant_user (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    role        ENUM('owner', 'admin', 'member') DEFAULT 'member',
    invited_at  TIMESTAMP NULL,
    joined_at   TIMESTAMP NULL,
    UNIQUE KEY (tenant_id, user_id)
);

-- Which payment methods this tenant has enabled
CREATE TABLE tenant_payment_method (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id         BIGINT UNSIGNED NOT NULL,
    payment_method_id BIGINT UNSIGNED NOT NULL,
    config            JSON NULL,
    is_active         BOOLEAN DEFAULT TRUE,
    UNIQUE KEY (tenant_id, payment_method_id)
);

-- Custom domain mapping
CREATE TABLE domain_aliases (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   BIGINT UNSIGNED NOT NULL,
    domain      VARCHAR(255) NOT NULL UNIQUE,
    verified_at TIMESTAMP NULL,
    created_at  TIMESTAMP NULL
);
```

### Index strategy

```
PRIMARY INDEX ON (tenant_id, id) for every business table
UNIQUE INDEX ON tenants.subdomain
UNIQUE INDEX ON tenants.domain (WHERE domain IS NOT NULL)
UNIQUE INDEX ON domain_aliases.domain
INDEX ON tenant_user (tenant_id, user_id)
```

---

## 3. Backend Architecture

### 3.1 Tenant Resolution Flow

```
Request comes in
  → IdentifyTenant middleware runs
    → Check subdomain: {tenant}.cores.io
      → Look up Tenant by subdomain → set context
    → Check custom domain: store.example.com
      → Look up domain_aliases → resolve tenant → set context
    → Check header: X-Tenant-ID (for API clients)
      → Look up Tenant by id → set context
    → If no match: context is "platform" (SaaS marketing site)
  
  → TenantManager::setCurrent($tenant)
  → Load tenant config (branding, locale, currency, features, plan limits)
  → Share into Inertia props for frontend
```

### 3.2 TenantManager Service (singleton)

```php
<?php

namespace App\Services\Tenant;

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

    public function setPlatformMode(bool $mode): void
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
```

### 3.3 Global Scope Pattern

```php
<?php

namespace App\Services\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantScope
{
    public function __invoke(Builder $query): void
    {
        $tenantId = app(TenantManager::class)->getCurrentId();

        if ($tenantId) {
            $model = $query->getModel();
            $table = $model->getTable();
            $query->where("{$table}.tenant_id", $tenantId);
        }
    }
}
```

Registered in `AppServiceProvider::boot()`:

```php
foreach ([Product::class, Category::class, Order::class, /* ... */] as $modelClass) {
    $modelClass::addGlobalScope('tenant', app(TenantScope::class));
}
```

Bypass scope with:

```php
Product::withoutGlobalScope('tenant')->get();        // All tenants (super admin)
Product::withoutTenant()->get();                     // Custom macro
Product::forTenant($tenantId)->get();                // Specific tenant
```

### 3.4 Middleware Implementation

```php
<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;

class IdentifyTenant
{
    public function __construct(private TenantManager $manager) {}

    public function handle(Request $request, Closure $next)
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            $this->manager->setCurrent($tenant);
            $this->setTenantConfig($tenant);
        } else {
            $this->manager->setPlatformMode(true);
        }

        $response = $next($request);

        // Add tenant identifier header for debugging
        if ($tenant) {
            $response->headers->set('X-Tenant-ID', $tenant->uuid);
        }

        return $response;
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        // 1. Check header (API clients)
        if ($header = $request->header('X-Tenant-ID')) {
            return Tenant::where('uuid', $header)->first();
        }

        // 2. Check subdomain
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            $tenant = Tenant::where('subdomain', $subdomain)->first();
            if ($tenant) return $tenant;
        }

        // 3. Check custom domain
        $tenant = Tenant::where('domain', $host)->first();
        if ($tenant) return $tenant;

        $alias = \App\Models\DomainAlias::where('domain', $host)
            ->whereNotNull('verified_at')
            ->with('tenant')
            ->first();
        if ($alias) return $alias->tenant;

        return null;
    }

    private function setTenantConfig(Tenant $tenant): void
    {
        config([
            'app.name' => $tenant->name,
            'app.locale' => $tenant->locale,
        ]);
    }
}
```

### 3.5 Middleware Stack (new ordering)

```php
// bootstrap/app.php
$middleware->web(prepend: [
    \App\Http\Middleware\IdentifyTenant::class,
]);
$middleware->api(prepend: [
    \App\Http\Middleware\IdentifyTenant::class,
]);
```

The `HandleInertiaRequests` middleware (already in web group) will then have tenant context available to share.

### 3.6 Auth Changes

**Login — scoped to tenant:**

```php
// FortifyServiceProvider
Fortify::authenticateUsing(function ($request) {
    $manager = app(TenantManager::class);
    $tenant = $manager->getCurrent();

    if (!$tenant) return null;

    $user = User::where('email', $request->email)
        ->where('tenant_id', $tenant->id)
        ->first();

    if ($user && Hash::check($request->password, $user->password)) {
        return $user;
    }

    return null;
});
```

**Registration — joins tenant:**

```php
// CreateNewUser action (Fortify)
public function create(array $input): User
{
    $tenant = app(TenantManager::class)->getCurrent();

    return DB::transaction(function () use ($input, $tenant) {
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'tenant_id' => $tenant->id,
            'role' => 'member',
        ]);

        // Attach to tenant_user pivot
        $tenant->users()->attach($user, ['role' => 'member']);

        return $user;
    });
}
```

**Super Admin:**

Add `is_super_admin` boolean to users table. When true, the global scope and tenant auth checks do not apply. A separate super admin guard can be added for the platform panel.

```php
// auth.php guards
'super_admin' => [
    'driver' => 'session',
    'provider' => 'users',
],
```

**Invite flow:**

1. Tenant admin invites `email@example.com`
2. System creates `tenant_user` record with `invited_at` and no `joined_at`
3. Email sent with registration link containing tenant context
4. On registration, user is linked to the tenant via `tenant_user`

### 3.7 API Route Restructuring

```
/api/v1/{tenant}/
  /auth/*              # Login, register (scoped to tenant)
  /products/*          # Tenant's products
  /orders/*            # Tenant's orders
  /categories/*        # Tenant's categories
  /deposits/*          # Tenant's deposits
  /notifications/*     # Tenant's notifications
  /admin/*             # Tenant admin APIs

/api/v1/platform/      # Super admin APIs
  /tenants             # CRUD tenants
  /tenants/{id}/stats  # Tenant analytics
  /system/*            # Health checks, monitoring
```

Route binding:

```php
// In RouteServiceProvider or a dedicated TenantRoute file
Route::group(['prefix' => 'api/v1/{tenant}', 'middleware' => ['auth:sanctum']], function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    // ...
});
```

### 3.8 Cache Strategy (per-tenant)

```php
<?php

namespace App\Services\Tenant;

use Illuminate\Support\Facades\Cache;

class TenantCache
{
    public function remember(Tenant $tenant, string $key, int $ttl, callable $callback): mixed
    {
        return Cache::tags(["tenant:{$tenant->id}"])->remember($key, $ttl, $callback);
    }

    public function flush(Tenant $tenant): void
    {
        Cache::tags(["tenant:{$tenant->id}"])->flush();
    }

    public function userKey(Tenant $tenant, int $userId, string $key): string
    {
        return "tenant:{$tenant->id}:user:{$userId}:{$key}";
    }
}
```

This allows bulk invalidation of an entire tenant's cache when their config changes, without affecting other tenants.

### 3.9 Queue Strategy

```php
// Each job carries tenant context
class ProcessOrderJob implements ShouldQueue
{
    public function __construct(
        private int $orderId,
        private int $tenantId   // ← stored so workers resolve the right context
    ) {}

    public function handle(TenantManager $manager): void
    {
        $tenant = Tenant::find($this->tenantId);
        $manager->setCurrent($tenant);
        // ... process order
    }
}
```

Queue routing:

```php
// QueueServiceProvider or horizon config
'tenant-basic' => ['connection' => 'redis', 'queue' => 'basic'],
'tenant-pro' => ['connection' => 'redis', 'queue' => 'pro'],
'tenant-enterprise' => ['connection' => 'redis', 'queue' => 'enterprise'],
```

Job dispatched to tier-specific queue:

```php
ProcessOrderJob::dispatch($orderId, $tenant->id)->onQueue("tenant-{$tenant->plan}");
```

This prevents one tenant's heavy job load from blocking others.

### 3.10 Scheduled Tasks — per-tenant iteration

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Iterate active tenants
    $schedule->call(function () {
        Tenant::whereIn('status', ['active', 'trial'])->each(function (Tenant $tenant) {
            app(TenantManager::class)->setCurrent($tenant);

            // Provider balance sync
            $schedule->job(new SyncProviderBalances($tenant->id))
                ->hourly()
                ->onQueue("tenant-{$tenant->plan}");

            // Pending fulfillment check
            $schedule->job(new CheckPendingFulfillment($tenant->id))
                ->everyFiveMinutes()
                ->onQueue("tenant-{$tenant->plan}");

            // ShamCash retry
            $schedule->job(new RetryShamCashPayments($tenant->id))
                ->everyMinute()
                ->onQueue("tenant-{$tenant->plan}");
        });
    })->everyMinute()->withoutOverlapping();

    // Platform-level tasks (not tenant-scoped)
    $schedule->job(new PruneExpiredSessions)->daily();
    $schedule->job(new GeneratePlatformAnalytics)->dailyAt('03:00');
}
```

### 3.11 Feature Gating per Plan

```php
<?php

namespace App\Services\Tenant;

class PlanFeatures
{
    public static function limits(string $plan): array
    {
        return match ($plan) {
            'free' => [
                'max_products' => 10,
                'max_users' => 3,
                'custom_domain' => false,
                'auto_fulfillment' => false,
                'api_access' => false,
                'priority_support' => false,
            ],
            'pro' => [
                'max_products' => 500,
                'max_users' => 25,
                'custom_domain' => true,
                'auto_fulfillment' => true,
                'api_access' => false,
                'priority_support' => false,
            ],
            'enterprise' => [
                'max_products' => -1,   // unlimited
                'max_users' => -1,
                'custom_domain' => true,
                'auto_fulfillment' => true,
                'api_access' => true,
                'priority_support' => true,
            ],
        };
    }

    public static function canCreateProduct(Tenant $tenant): bool
    {
        $limits = self::limits($tenant->plan);
        if ($limits['max_products'] === -1) return true;
        return $tenant->products()->count() < $limits['max_products'];
    }
}
```

Gate registered in `AppServiceProvider`:

```php
Gate::define('create-product', fn (User $user) =>
    PlanFeatures::canCreateProduct($user->tenant)
);
```

---

## 4. Frontend Architecture

### 4.1 Tenant Context in Inertia

```php
// app/Http/Middleware/HandleInertiaRequests.php
public function share(Request $request): array
{
    $manager = app(TenantManager::class);
    $tenant = $manager->getCurrent();

    return [
        ...parent::share($request),
        'shop' => $tenant ? [
            'id'       => $tenant->uuid,
            'name'     => $tenant->name,
            'logo'     => $tenant->logo_url,
            'favicon'  => $tenant->favicon_url,
            'color'    => $tenant->brand_color,
            'colorDark'=> $tenant->brand_color_dark,
            'locale'   => $tenant->locale,
            'currency' => $tenant->currency,
            'plan'     => $tenant->plan,
            'features' => PlanFeatures::limits($tenant->plan),
        ] : null,
        'platform' => $manager->isPlatformRequest(),
    ];
}
```

### 4.2 Pinia Store — `shopStore.ts`

```ts
// resources/js/stores/shopStore.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export const useShopStore = defineStore('shop', () => {
  const shop = ref<any>(null)
  const isPlatform = ref(false)

  function hydrate(pageProps: any) {
    shop.value = pageProps?.shop ?? null
    isPlatform.value = pageProps?.platform ?? false
  }

  const name = computed(() => shop.value?.name ?? '')
  const logo = computed(() => shop.value?.logo ?? '')
  const brandColor = computed(() => shop.value?.color ?? '#22d3ee')
  const plan = computed(() => shop.value?.plan ?? 'free')
  const features = computed(() => shop.value?.features ?? {})
  const hasFeature = (f: string) => !!features.value[f]

  const isProOrHigher = computed(() => plan.value === 'pro' || plan.value === 'enterprise')

  return {
    shop, isPlatform, hydrate,
    name, logo, brandColor, plan, features, hasFeature, isProOrHigher,
  }
})
```

### 4.3 Dynamic Theming

```ts
// resources/js/app.ts (inside setup)
import { useShopStore } from '@/stores/shopStore'

const shopStore = useShopStore()
shopStore.hydrate((props as any)?.initialPage?.props)

// Sync shop context on every navigation
router.on('success', (event: any) => {
  shopStore.hydrate(event?.detail?.page?.props)
})

// Apply brand colors to CSS variables
watchEffect(() => {
  if (shopStore.shop) {
    document.documentElement.style.setProperty('--primary', shopStore.brandColor)
    document.documentElement.style.setProperty('--primary-foreground', '#000000')
    if (shopStore.logo) {
      const link = document.querySelector('link[rel="icon"]') || document.createElement('link')
      link.setAttribute('rel', 'icon')
      link.setAttribute('href', shopStore.logo)
      document.head.appendChild(link)
    }
    document.title = shopStore.name
  }
})
```

### 4.4 URL Structure

```
Production:
  https://{tenant}.cores.io           → Tenant storefront
  https://{tenant}.cores.io/admin     → Tenant admin panel
  https://cores.io                     → Platform landing / SaaS marketing
  https://cores.io/login               → Platform login (redirects to tenant)

Development:
  http://localhost:8000/app/{tenant}  → Path-based tenant (no wildcard DNS needed)
  http://{tenant}.localhost:8000      → Requires DNS config / /etc/hosts
```

Nginx wildcard vhost:

```nginx
server {
    listen 80;
    server_name ~^(?<subdomain>[^.]+)\.cores\.io$;
    root /var/www/store-app/public;
    # ... standard Laravel setup
    # $subdomain is available for the app via X-Forwarded-Host
}
```

### 4.5 Frontend Route / Layout Changes

```
layouts/
├── PlatformLayout.vue        # SaaS marketing site (no tenant)
├── TenantLayout.vue          # Tenant storefront (customer-facing)
├── TenantAdminLayout.vue     # Tenant admin panel
└── ... existing layouts
```

`TenantLayout.vue` replaces `AuthenticatedLayout.vue` and `GuestLayout.vue` for in-tenant pages. It loads shop context and applies brand styling.

```vue
<!-- TenantLayout.vue (outer wrapper for all tenant pages) -->
<script setup lang="ts">
import { useShopStore } from '@/stores/shopStore'
import { usePage } from '@inertiajs/vue3'

const shopStore = useShopStore()
const page = usePage()

// Determine if this is admin or customer area
const isAdminArea = computed(() => page.component.startsWith('Admin/'))
</script>

<template>
  <div :style="{ '--brand': shopStore.brandColor }">
    <component :is="isAdminArea ? AdminLayout : CustomerLayout">
      <slot />
    </component>
  </div>
</template>
```

---

## 5. Platform Landing Page

A new set of pages that serve the SaaS marketing site (outside any tenant context):

### Routes

| Route | Page | Purpose |
|-------|------|---------|
| `GET /` | `Platform/Welcome.vue` | Marketing hero, pricing, features, CTA |
| `GET /pricing` | `Platform/Pricing.vue` | Plan comparison table |
| `GET /features` | `Platform/Features.vue` | Detailed features |
| `GET /login` | `Platform/Login.vue` | Tenant admin login (asks for tenant slug first) |
| `GET /register` | `Platform/Register.vue` | Sign up new tenant |
| `GET /docs` | `Platform/Docs.vue` | API documentation |

### Registration Flow

```
1. User visits /register
2. Fills: store name, email, password
3. System:
   a. Generates subdomain from store name (slugified)
   b. Creates Tenant record with trial status
   c. Creates User record (owner role) with tenant_id
   d. Seeds default categories, payment methods
   e. Sends welcome email with magic link
4. Redirects to https://{subdomain}.cores.io/admin
```

### PlatformLayout.vue

```vue
<script setup lang="ts">
// No tenant context — pure SaaS marketing
const shopStore = useShopStore()
shopStore.hydrate({ shop: null, platform: true })
</script>

<template>
  <div class="platform-site">
    <!-- Hero, features, footer — your Welcome.vue reworked as SaaS landing -->
    <slot />
  </div>
</template>
```

---

## 6. Billing & Subscription

### 6.1 Pricing Model

| Tier | Price | Products | Users | Custom Domain | Auto-Fulfill | API | Support |
|------|-------|----------|-------|---------------|-------------|-----|---------|
| **Free** | $0/mo | 10 | 3 | ✗ | ✗ | ✗ | Community |
| **Pro** | $29/mo | 500 | 25 | ✓ | ✓ | ✗ | Email |
| **Enterprise** | $99/mo | Unlimited | Unlimited | ✓ | ✓ | ✓ | Priority |
| **+ Transaction fee** | Pro: 2%, Enterprise: 1.5% | | | | | | |

### 6.2 Implementation

**Phase 1 — Wallet-based billing (MVP):**

```
1. Platform has its own wallet (separate from tenant wallets)
2. Tenant admin deposits into platform wallet (via existing deposit flow, scoped to platform user)
3. Monthly subscription auto-deducted from wallet via scheduled command
4. Transaction fees deducted per-order via event listener
5. If wallet insufficient → tenant suspended → grace period → cancelled
```

**Tables:**

```sql
CREATE TABLE subscription_plans (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100) NOT NULL,
    code            VARCHAR(50) NOT NULL UNIQUE,         -- 'free', 'pro', 'enterprise'
    price_monthly   DECIMAL(10, 2) NOT NULL,
    transaction_fee DECIMAL(5, 4) NOT NULL,               -- e.g. 0.02 for 2%
    features        JSON NOT NULL,
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP NULL
);

CREATE TABLE subscription_invoices (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    plan_code       VARCHAR(50) NOT NULL,
    amount          DECIMAL(10, 2) NOT NULL,
    period_start    DATE NOT NULL,
    period_end      DATE NOT NULL,
    status          ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    paid_at         TIMESTAMP NULL,
    created_at      TIMESTAMP NULL
);

ALTER TABLE tenants
    ADD COLUMN platform_balance DECIMAL(10, 2) DEFAULT 0.00 AFTER features,
    ADD COLUMN platform_currency VARCHAR(3) DEFAULT 'USD' AFTER platform_balance;
```

**Recurring billing command:**

```php
// app/Console/Commands/ProcessSubscriptions.php
public function handle(): void
{
    $today = now()->startOfDay();

    Tenant::whereIn('status', ['active', 'trial'])
        ->where('trial_ends_at', '<', now())
        ->each(function (Tenant $tenant) use ($today) {
            $plan = PlanFeatures::limits($tenant->plan);
            $price = $plan['price_monthly'] ?? 0;
            $periodEnd = $today->copy()->addMonth();

            // Deduct from platform wallet
            if ($price > 0) {
                if ($tenant->platform_balance < $price) {
                    $tenant->update(['status' => 'suspended']);
                    // Notify tenant admin
                    return;
                }
                $tenant->decrement('platform_balance', $price);
            }

            // Create invoice
            SubscriptionInvoice::create([
                'tenant_id' => $tenant->id,
                'plan_code' => $tenant->plan,
                'amount' => $price,
                'period_start' => $today,
                'period_end' => $periodEnd,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Update expiration
            $tenant->update([
                'expires_at' => $periodEnd,
                'subscribed_at' => $tenant->subscribed_at ?? now(),
            ]);
        });
}
```

**Transaction fee listener:**

```php
// app/Listeners/DeductTransactionFee.php
public function handle(OrderCreated $event): void
{
    $order = $event->order;
    $fee = PlanFeatures::transactionFee($order->tenant);
    $amount = $order->price_at_time_of_order * $fee;

    $order->tenant->increment('platform_balance', $amount);
}
```

**Phase 2 — Stripe/Paddle integration (post-MVP):**

```php
// New column
ALTER TABLE tenants ADD COLUMN stripe_customer_id VARCHAR(255) NULL;

// SubscriptionController handles Stripe webhooks:
// - customer.subscription.created → update tenant plan
// - invoice.paid → create invoice record
// - customer.subscription.deleted → suspend tenant
```

### 6.3 Plan Upgrade/Downgrade

```php
// TenantService
public function changePlan(Tenant $tenant, string $newPlan): void
{
    DB::transaction(function () use ($tenant, $newPlan) {
        $oldPlan = $tenant->plan;
        $tenant->update(['plan' => $newPlan]);

        // Pro-rate billing
        // Flush tenant cache
        app(TenantCache::class)->flush($tenant);

        // Log plan change
        ActivityLog::log($tenant, 'plan_changed', [
            'from' => $oldPlan,
            'to' => $newPlan,
        ]);
    });
}
```

### 6.4 Downgrade Limits Enforcement

When a tenant downgrades from Pro to Free and has 50 products (Free max is 10):

- Products become "disabled" (status = inactive) rather than deleted
- Owner can re-enable them after upgrading
- Dashboard shows "Upgrade to Pro to activate X more products" banner

---

## 7. Data Migration Strategy

### Phase 1 — Developer setup (existing data)

```php
// database/migrations/2026_07_01_000002_migrate_existing_data_to_default_tenant.php
class MigrateExistingDataToDefaultTenant extends Migration
{
    public function up(): void
    {
        // 1. Create default tenant
        $tenant = Tenant::create([
            'name' => 'Default Store',
            'slug' => 'default',
            'subdomain' => 'store',
            'status' => 'active',
            'plan' => 'enterprise',
        ]);

        // 2. Add tenant_id columns as nullable first
        Schema::table('users', fn (Blueprint $t) => $t->foreignId('tenant_id')->nullable()->after('id'));
        Schema::table('products', fn (Blueprint $t) => $t->foreignId('tenant_id')->nullable()->after('id'));
        Schema::table('orders', fn (Blueprint $t) => $t->foreignId('tenant_id')->nullable()->after('id'));
        // ... all other tables

        // 3. Backfill tenant_id
        DB::table('users')->update(['tenant_id' => $tenant->id]);
        DB::table('products')->update(['tenant_id' => $tenant->id]);
        DB::table('orders')->update(['tenant_id' => $tenant->id]);
        // ... all other tables

        // 4. Make tenant_id NOT NULL
        Schema::table('users', fn (Blueprint $t) => $t->foreignId('tenant_id')->nullable(false)->change());
        // ... all other tables

        // 5. Create tenant_user pivot for existing admin users
        $admin = DB::table('users')->where('role', 'admin')->first();
        if ($admin) {
            DB::table('tenant_user')->insert([
                'tenant_id' => $tenant->id,
                'user_id' => $admin->id,
                'role' => 'owner',
            ]);
        }
    }
}
```

### Per new tenant signup — seeding

```php
// app/Listeners/SeedNewTenant.php
public function handle(TenantCreated $event): void
{
    $tenant = $event->tenant;

    TenantManager::setCurrent($tenant);

    // Create default categories
    Category::insert([
        ['tenant_id' => $tenant->id, 'name' => 'Games', 'status' => 'active'],
        ['tenant_id' => $tenant->id, 'name' => 'Gift Cards', 'status' => 'active'],
        ['tenant_id' => $tenant->id, 'name' => 'Software', 'status' => 'active'],
    ]);

    // Enable default payment methods
    $methods = PaymentMethod::whereIn('code', ['manual', 'sham_cash'])->get();
    $tenant->paymentMethods()->attach($methods->pluck('id'));

    // Create initial admin user from the owner
    $tenant->users()->attach($event->ownerId, ['role' => 'owner']);

    // Set trial period
    $tenant->update(['trial_ends_at' => now()->addDays(14)]);
}
```

---

## 8. DevOps & Infrastructure

### 8.1 Nginx Configuration

Platform server block:

```nginx
# Platform (SaaS marketing site)
server {
    listen 80;
    server_name cores.io www.cores.io;
    root /var/www/store-app/public;
    # ... standard Laravel config
}
```

Wildcard tenant server block:

```nginx
# Tenant subdomains
server {
    listen 80;
    server_name ~^(?<subdomain>[^.]+)\.cores\.io$;
    root /var/www/store-app/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param HTTP_HOST $host;
        include fastcgi_params;
    }
}
```

Custom domain server block (generated dynamically or via certbot):

```nginx
# Store-specific custom domain
server {
    listen 443 ssl;
    server_name store.example.com;
    ssl_certificate /etc/letsencrypt/live/store.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/store.example.com/privkey.pem;
    root /var/www/store-app/public;
    # ... same as above
}
```

### 8.2 Custom Domain Flow

```
1. Tenant enters domain in admin panel → POST /admin/domain
2. System creates DomainAlias record (unverified)
3. Returns DNS verification value: TXT "cores-verify={uuid}"
4. Tenant adds TXT record to their DNS
5. System cron checks DNS TXT records hourly
6. When verified → DomainAlias.verified_at = now()
7. System provisions Let's Encrypt certificate (certbot + post-hook)
8. Tenant points CNAME to cores.io
9. Domain is live
```

### 8.3 SSL Auto-Provisioning

```bash
#!/bin/bash
# scripts/provision-ssl.sh
# Called after domain verification
DOMAIN=$1
LE_DIR="/etc/letsencrypt/live/$DOMAIN"

if [ ! -d "$LE_DIR" ]; then
    certbot certonly --webroot -w /var/www/store-app/public -d "$DOMAIN" --non-interactive --agree-tos --email admin@cores.io
fi

# Generate nginx config snippet or reload
nginx -s reload
```

### 8.4 Monitoring

Laravel Pulse already installed — extend with per-tenant views:

```php
// app/Providers/PulseServiceProvider.php
Pulse::user(fn ($user) => [
    'name' => $user->name,
    'tenant_id' => $user->tenant_id,
    'extra' => $user->tenant->name,
]);
```

Custom Pulse cards:

```php
// app/Livewire/Pulse/TenantStats.php
class TenantStats extends Card
{
    public function card(): string
    {
        return view('livewire.pulse.tenant-stats', [
            'totalTenants' => Tenant::count(),
            'activeTenants' => Tenant::where('status', 'active')->count(),
            'trialingTenants' => Tenant::where('status', 'trial')->count(),
            'mrr' => Tenant::whereIn('status', ['active', 'trial'])
                ->get()
                ->sum(fn ($t) => PlanFeatures::limits($t->plan)['price_monthly'] ?? 0),
        ]);
    }
}
```

### 8.5 Rate Limiting (per-tenant)

```php
// app/Http/Middleware/ThrottleTenant.php
class ThrottleTenant
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $tenantId = app(TenantManager::class)->getCurrentId();
        $key = "tenant-api:{$tenantId}";

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw new HttpException(429, 'Too many requests');
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
```

### 8.6 CDN / File Storage

```php
// config/filesystems.php
'tenant_public' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'root' => 'public/{tenant_uuid}',  // dynamic root per tenant
    'url' => env('AWS_URL'),
],

'tenant_private' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'root' => 'private/{tenant_uuid}',
    'url' => env('AWS_URL'),
    'visibility' => 'private',
],
```

---

## 9. Implementation Phases

### Phase 1 (2 weeks) — Core Multi-Tenancy

| Day | Task | Deliverable |
|-----|------|-------------|
| 1-2 | Create `tenants` table + `Tenant` model + `TenantManager` service | Tenant CRUD working in tinker |
| 3-4 | Build `IdentifyTenant` middleware + subdomain resolution | Request routing by tenant |
| 5-6 | Add `tenant_id` to ALL tables + Global Scope + migration backfill | Data isolation verified |
| 7-8 | Seed default tenant + migrate existing data + test auth scoping | Single-tenant upgrade complete |
| 9-10 | Build `tenant_user` pivot + invitation flow | User membership working |

**Checkpoint**: Existing store runs on `http://store.cores.io`, new tenant can be created via tinker.

### Phase 2 (2 weeks) — Platform & Branding

| Day | Task | Deliverable |
|-----|------|-------------|
| 11-12 | Build platform landing page (SaaS marketing) + `/register` tenant signup form | Tenant self-onboarding |
| 13-14 | Dynamic theming (logo, color, locale from tenant config) | Branded storefronts |
| 15-16 | Build `shopStore` Pinia store + `TenantLayout.vue` | Frontend tenant context |
| 17-18 | Custom domain verification flow + SSL provisioning | `store.example.com` works |
| 19-20 | Per-tenant settings + feature flags | Tenant-configurable features |

**Checkpoint**: New tenant can sign up, get a branded store instantly.

### Phase 3 (2 weeks) — Billing & Limits

| Day | Task | Deliverable |
|-----|------|-------------|
| 21-22 | Subscription plans table + plan feature config | Billing infrastructure |
| 23-24 | Platform wallet (separate from tenant wallet) + top-up flow | Wallet payments working |
| 25-26 | Monthly subscription deduction command | Recurring billing |
| 27-28 | Per-plan limits enforcement (products, users, API) + feature gates | Feature gating working |
| 29-30 | Transaction fee calculation + event listener | Revenue per order tracked |

**Checkpoint**: Platform charging and collecting money end-to-end.

### Phase 4 (2 weeks) — Polish & Scale

| Day | Task | Deliverable |
|-----|------|-------------|
| 31-32 | Per-tenant cache tags + queue isolation | Performance isolation |
| 33-34 | Super admin panel (list tenants, impersonate, view stats) | Platform ops ready |
| 35-36 | Rate limiting per tenant + abuse detection | Security hardened |
| 37-38 | Tenant usage analytics (orders, revenue, users over time) | Insights dashboard |
| 39-40 | Onboarding email sequence + setup wizard | User activation improved |

**Checkpoint**: Platform ready for beta customers.

### Phase 5 (Post-MVP — ongoing)

- Stripe/Paddle credit card payment integration
- Public API with per-tenant API keys + rate limits
- Coupon codes, referral program for tenants
- AI-generated product descriptions
- Multi-currency support
- Tenant export/migration tools
- Zapier/n8n webhook integrations

---

## 10. Risk & Mitigation

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| **Row-level tenant isolation broken** | Low | Critical | Integration tests on every query that assert tenant scope; periodic cross-tenant audit script that checks no data leaks |
| **One tenant DoS's the database** | Medium | High | Queue per tier, connection pool limits, per-tenant rate limiting |
| **DNS/custom domain complexity** | Medium | Medium | Launch without custom domains (subdomain only in MVP); add domain feature in Phase 2 |
| **Schema migration on shared table** | Low | High | All migrations backward-compatible; deploy with zero-downtime strategy (expand then contract) |
| **Tenant onboarding abandonment** | Medium | Medium | 3-step guided wizard with demo data pre-fill; email reminders for incomplete setup |
| **Free tenants consume too many resources** | High | Medium | Strict resource limits per plan; automated suspension of inactive tenants (30d) |
| **Platform wallet insufficient for refunds** | Low | Medium | Separate reserve fund; hold percentage of transaction fees in reserve account |
| **Billing errors (overcharged/undercharged)** | Low | High | Idempotent billing commands; manual adjustment tool in super admin panel; full audit log |

---

## 11. Revenue Model

### Projection at modest scale

```
Tier         Price     Tenants      MRR
────────────────────────────────────────
Free         $0        100          $0
Pro          $29       50           $1,450
Enterprise   $99       10           $990
────────────────────────────────────────
Subscriptions:                        $2,440/mo

Transaction fees:
  Pro (50 tenants × 200 orders × $20 avg × 2%)       = $4,000/mo
  Enterprise (10 tenants × 500 orders × $20 avg × 1.5%) = $1,500/mo
────────────────────────────────────────
Total MRR:                          $7,940/mo
```

### Key metrics to track

```
MRR (Monthly Recurring Revenue)
ARR (Annual Run Rate)
LTV (Lifetime Value per tenant)
CAC (Customer Acquisition Cost)
Churn rate (monthly % of paying tenants lost)
Net Revenue Retention (NRR)
Average order value per tenant
Orders per tenant per month
```

### Cost structure

```
Monthly costs at 60 paying tenants:
  Server (VPS/Cloud)        $100-200/mo
  Redis                     $25-50/mo
  S3/CDN                    $20-50/mo
  Email (SendGrid/Mailgun)  $30-50/mo
  SMS (if needed)           $20/mo
  Monitoring (Pulse self-hosted)  $0
  Payment processing (Stripe: 2.9% + $0.30)  variable

Total base: ~$200-400/mo
Gross margin: 95%+
```

---

## Appendix A: File Changes Summary

### New files

```
app/Console/Commands/TenantMigrate.php
app/Console/Commands/TenantSeed.php
app/Console/Commands/ProcessSubscriptions.php
app/Events/TenantCreated.php
app/Events/TenantPlanChanged.php
app/Exceptions/TenantException.php
app/Http/Middleware/IdentifyTenant.php
app/Http/Middleware/ThrottleTenant.php
app/Listeners/DeductTransactionFee.php
app/Listeners/SeedNewTenant.php
app/Listeners/SendTenantWelcomeEmail.php
app/Models/DomainAlias.php
app/Models/SubscriptionInvoice.php
app/Models/SubscriptionPlan.php
app/Models/Tenant.php
app/Models/TenantUser.php (pivot)
app/Observers/TenantObserver.php
app/Policies/TenantPolicy.php
app/Services/Tenant/PlanFeatures.php
app/Services/Tenant/TenantCache.php
app/Services/Tenant/TenantManager.php
app/Services/Tenant/TenantResolver.php
app/Services/Tenant/TenantScope.php
database/migrations/2026_07_01_000001_create_tenants_table.php
database/migrations/2026_07_01_000002_create_tenant_user_table.php
database/migrations/2026_07_01_000003_create_domain_aliases_table.php
database/migrations/2026_07_01_000004_create_subscription_plans_table.php
database/migrations/2026_07_01_000005_create_subscription_invoices_table.php
database/migrations/2026_07_01_000006_create_tenant_payment_method_table.php
database/migrations/2026_07_01_000007_add_tenant_id_to_all_tables.php
database/migrations/2026_07_01_000008_add_is_super_admin_to_users.php
database/migrations/2026_07_01_000009_migrate_existing_data_to_default_tenant.php
resources/js/layouts/PlatformLayout.vue
resources/js/layouts/TenantLayout.vue
resources/js/layouts/TenantAdminLayout.vue
resources/js/pages/Platform/Welcome.vue
resources/js/pages/Platform/Pricing.vue
resources/js/pages/Platform/Features.vue
resources/js/pages/Platform/Login.vue
resources/js/pages/Platform/Register.vue
resources/js/stores/shopStore.ts
resources/js/types/shop.ts
routes/platform.php
routes/tenant.php
```

### Modified files

```
app/Http/Middleware/HandleInertiaRequests.php   ← add shop/tenant context
app/Http/Controllers/Api/OrderController.php    ← scope queries to tenant
app/Http/Controllers/Api/ProductController.php  ← scope queries to tenant
... (all controllers — add tenant scoping to queries and creations)
bootstrap/app.php                                ← register IdentifyTenant middleware
composer.json                                    ← no new deps needed
config/app.php                                   ← no changes (tenant handles name/locale)
config/auth.php                                  ← add super_admin guard (optional)
config/sanctum.php                               ← no changes needed
resources/js/app.ts                              ← add shopStore hydration + theme
routes/api.php                                   ← prefix with {tenant}, add routes
routes/web.php                                   ← add platform routes, restructure
```

---

*End of plan. Ready for Phase 1 implementation.*
