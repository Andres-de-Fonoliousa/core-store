<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Services\World4CardService;
use Illuminate\Console\Command;

class SyncProviderBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providers:sync-balance {provider_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync provider balance from API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $providerId = $this->argument('provider_id');

        if ($providerId) {
            $providers = [Provider::findOrFail($providerId)];
        } else {
            $providers = Provider::where('sync_active', true)->get();
        }

        foreach ($providers as $provider) {
            $this->info("Syncing balance for provider: {$provider->name}");

            try {
                $api = new World4CardService($provider);
                $profile = $api->getProfile();

                $balance = $profile['balance'] ?? 0;

                $provider->update(['balance' => $balance]);

                $this->info("Balance updated: {$balance}");
            } catch (\Exception $e) {
                $this->error("Failed to sync balance for {$provider->name}: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
