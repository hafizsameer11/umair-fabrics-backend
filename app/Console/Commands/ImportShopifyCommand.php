<?php

namespace App\Console\Commands;

use App\Services\ShopifyImportService;
use Illuminate\Console\Command;

class ImportShopifyCommand extends Command
{
    protected $signature = 'shop:import-shopify
                            {store_url : Shopify store base URL}
                            {--limit=50 : Number of products to import}
                            {--download-images : Download images to local storage}';

    protected $description = 'Import products from a Shopify store JSON API (dev seeding only)';

    public function handle(ShopifyImportService $importer): int
    {
        $url = $this->argument('store_url');
        $limit = (int) $this->option('limit');
        $download = $this->option('download-images');

        $this->info("Importing up to {$limit} products from {$url}...");

        $count = $importer->importFromStore($url, $limit, $download);

        $this->info("Imported {$count} products successfully.");

        return self::SUCCESS;
    }
}
