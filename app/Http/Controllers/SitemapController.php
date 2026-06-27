<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $staticPages = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => url('/browse'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => url('/about'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => url('/terms'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => url('/privacy'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['loc' => url('/support'), 'priority' => '0.4', 'changefreq' => 'monthly'],
        ];

        $categories = Category::whereNotNull('status')
            ->where('status', '!=', 'inactive')
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'updated_at']);

        $categoryUrls = $categories->map(fn ($cat) => [
            'loc' => url("/browse/{$cat->id}"),
            'priority' => '0.7',
            'changefreq' => 'daily',
            'lastmod' => $cat->updated_at->toW3cString(),
        ]);

        $products = Product::where('status', 'active')
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'updated_at']);

        $productUrls = $products->map(fn ($p) => [
            'loc' => url("/products/{$p->id}"),
            'priority' => '0.6',
            'changefreq' => 'weekly',
            'lastmod' => $p->updated_at->toW3cString(),
        ]);

        $urls = collect($staticPages)
            ->concat($categoryUrls)
            ->concat($productUrls);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            if (isset($url['lastmod'])) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
