<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking for products without inventory records...\n";

// Get all products
$products = \Illuminate\Support\Facades\DB::table('products')->get();
echo "Found " . count($products) . " products\n";

$created = 0;
foreach ($products as $product) {
    // Check if inventory record exists
    $inventory = \Illuminate\Support\Facades\DB::table('inventories')->where('product_id', $product->id)->first();
    
    if (!$inventory) {
        // Create inventory record
        \Illuminate\Support\Facades\DB::table('inventories')->insert([
            'product_id' => $product->id,
            'quantity' => 0,
            'low_stock_threshold' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        echo "Created inventory record for product: {$product->name} (ID: {$product->id})\n";
        $created++;
    }
}

echo "Created {$created} inventory records\n";
echo "Done!\n";