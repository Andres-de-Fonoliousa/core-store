<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchCategoryImages extends Command
{
    protected $signature = 'categories:fetch-images {apiKey : Pexels API key}';

    protected $description = 'Fetch category images from Pexels and store locally';

    public function handle(): void
    {
        $apiKey = $this->argument('apiKey');

        $categories = Category::whereNull('image')->orWhere('image', '')->get();

        if ($categories->isEmpty()) {
            $this->info('All categories already have images.');
            return;
        }

        $this->info("Fetching images for {$categories->count()} categories...");

        $storageDir = public_path('storage/categories');
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        foreach ($categories as $category) {
            $this->line("  [{$category->id}] {$category->name}...");

            try {
                $response = Http::withHeaders([
                    'Authorization' => $apiKey,
                ])->timeout(10)->get('https://api.pexels.com/v1/search', [
                    'query' => $category->name . ' digital',
                    'per_page' => 1,
                    'orientation' => 'landscape',
                ]);

                if ($response->failed()) {
                    $this->warn("    API error: {$response->status()}");
                    continue;
                }

                $photos = $response->json('photos');
                if (empty($photos)) {
                    $this->warn("    No results");
                    continue;
                }

                $imageUrl = $photos[0]['src']['medium'];

                $imgResponse = Http::timeout(10)->get($imageUrl);
                if ($imgResponse->failed()) {
                    $this->warn("    Download failed");
                    continue;
                }

                $filename = "{$category->id}.jpg";
                file_put_contents("{$storageDir}/{$filename}", $imgResponse->body());

                $category->update(['image' => "storage/categories/{$filename}"]);

                $this->info("    Saved → storage/categories/{$filename}");
            } catch (\Exception $e) {
                $this->warn("    Error: {$e->getMessage()}");
            }

            usleep(600_000);
        }

        $this->newLine();
        $this->info('Done!');
    }
}
