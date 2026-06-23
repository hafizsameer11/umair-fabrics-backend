<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Console\Command;

class AssignProductsToCollections extends Command
{
    protected $signature = 'shop:assign-collections';

    protected $description = 'Assign products evenly to collections for demo';

    public function handle(): int
    {
        $collections = Collection::all();
        $products = Product::all();

        foreach ($collections as $i => $collection) {
            $slice = $products->slice($i * 12, 12)->pluck('id');
            $collection->products()->syncWithoutDetaching($slice);
            $this->info("Assigned {$slice->count()} products to {$collection->name}");
        }

        return self::SUCCESS;
    }
}
