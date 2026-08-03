<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use App\Services\ProductPricingService;
use App\Services\Tenant\CatalogCache;
use App\Services\World4CardService;
use Illuminate\Console\Command;

class SyncProductsFromProvider extends Command
{
    protected $signature = 'products:sync-from-provider {provider_id?} {--fresh : Deactivate products no longer in catalog}';

    protected $description = 'Sync products and categories from a provider API';

    public function __construct(
        private CatalogCache $catalogCache,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $providerId = $this->argument('provider_id');

        if ($providerId) {
            $providers = [Provider::findOrFail($providerId)];
        } else {
            $providers = Provider::where('sync_active', true)->get();
        }

        foreach ($providers as $provider) {
            $this->info("Syncing {$provider->name}...");
            $api = new World4CardService($provider);

            // BFS queue: [providerParentId, localParentId]
            $queue = [[0, null]];
            // providerCatId => localCatId
            $catMap = [];

            while ($queue) {
                $batch = array_splice($queue, 0, 10);
                $ids = array_column($batch, 0);

                $this->line('  Fetching '.count($ids).' content pages...');
                $contents = $api->getContentMany($ids);

                foreach ($batch as $i => [$pId, $localParentId]) {
                    $content = $contents[$pId] ?? [];

                    foreach ($content['categories'] ?? [] as $cat) {
                        $local = Category::updateOrCreate(
                            ['provider_category_id' => $cat['id']],
                            [
                                'name' => $cat['name'],
                                'image' => $cat['image'] ?? null,
                                'parent_id' => $localParentId,
                                'status' => 'active',
                            ]
                        );
                        $catMap[$cat['id']] = $local->id;
                        $queue[] = [$cat['id'], $local->id];
                    }
                }

                $this->output->write('.');
            }

            $this->newLine();
            $catCount = count($catMap);
            $this->line("  {$catCount} categories synced.");

            // Fetch all products
            $this->line('  Fetching products...');
            $products = $api->getProducts();
            $this->line('  '.count($products).' products received.');

            $syncedIds = [];
            foreach ($products as $p) {
                $extId = (string) $p['id'];
                $syncedIds[] = $extId;

                Product::updateOrCreate(
                    ['external_id' => $extId, 'provider_id' => $provider->id],
                    [
                        'name' => $p['name'],
                        'category_id' => $p['parent_id'] > 0 ? ($catMap[$p['parent_id']] ?? null) : null,
                        'price' => 0,
                        'cost_price' => $p['base_price'] ?? $p['price'],
                        'params' => $p['params'] ?? null,
                        'qty_values' => $this->normalizeQty($p['qty_values'] ?? []),
                        'is_auto' => true,
                        'status' => ($p['available'] ?? true) ? 'active' : 'inactive',
                        'image' => $p['category_img'] ?? null,
                    ]
                );
            }

            if ($this->option('fresh')) {
                $gone = Product::where('provider_id', $provider->id)
                    ->whereNotIn('external_id', $syncedIds)
                    ->update(['status' => 'inactive']);
                if ($gone) {
                    $this->warn("  {$gone} stale products deactivated.");
                }
            }

            $this->line('  Applying profit margins...');
            app(ProductPricingService::class)->updateAllProductPrices();

            $this->catalogCache->bustAll();

            $this->info('  Done.');
        }

        return Command::SUCCESS;
    }

    private function normalizeQty(mixed $raw): array
    {
        if ($raw === null) {
            return [1];
        }
        if (is_array($raw)) {
            if (isset($raw['min']) || isset($raw['max'])) {
                return [
                    'min' => (int) ($raw['min'] ?? 1),
                    'max' => (int) ($raw['max'] ?? 999999),
                ];
            }

            return array_map('intval', $raw);
        }

        return [1];
    }
}
