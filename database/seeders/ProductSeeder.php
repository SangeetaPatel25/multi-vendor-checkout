<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = [
            'TechStore Pro' => [
                ['name' => 'Wireless Headphones', 'price' => 79.99, 'stock' => 50],
                ['name' => 'Smartphone Case', 'price' => 24.99, 'stock' => 100],
                ['name' => 'Bluetooth Speaker', 'price' => 49.99, 'stock' => 30],
                ['name' => 'USB-C Cable', 'price' => 12.99, 'stock' => 200],
            ],
            'FashionHub' => [
                ['name' => 'Cotton T-Shirt', 'price' => 19.99, 'stock' => 75],
                ['name' => 'Denim Jeans', 'price' => 59.99, 'stock' => 40],
                ['name' => 'Leather Wallet', 'price' => 34.99, 'stock' => 60],
                ['name' => 'Sunglasses', 'price' => 89.99, 'stock' => 25],
            ],
            'HomeDecor Plus' => [
                ['name' => 'Ceramic Vase', 'price' => 45.99, 'stock' => 20],
                ['name' => 'Throw Pillow Set', 'price' => 29.99, 'stock' => 35],
                ['name' => 'Wall Art Print', 'price' => 39.99, 'stock' => 15],
                ['name' => 'Table Lamp', 'price' => 64.99, 'stock' => 12],
                ['name' => 'Decorative Candle', 'price' => 16.99, 'stock' => 80],
            ],
        ];

        foreach ($catalog as $vendorName => $products) {
            $vendor = Vendor::where('name', $vendorName)->first();

            if (!$vendor) {
                continue;
            }

            foreach ($products as $product) {
                Product::updateOrCreate(
                    [
                        'vendor_id' => $vendor->id,
                        'name' => $product['name'],
                    ],
                    [
                        'price' => $product['price'],
                        'stock' => $product['stock'],
                    ]
                );
            }
        }
    }
}
