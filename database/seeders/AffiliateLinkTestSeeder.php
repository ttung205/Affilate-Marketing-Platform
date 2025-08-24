<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\AffiliateLink;
use Illuminate\Support\Str;

class AffiliateLinkTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test publisher
        $publisher = User::firstOrCreate(
            ['email' => 'publisher@test.com'],
            [
                'name' => 'Test Publisher',
                'password' => bcrypt('password'),
                'role' => 'publisher',
                'is_active' => true,
            ]
        );

        // Create test product
        $product = Product::firstOrCreate(
            ['name' => 'Test Product'],
            [
                'description' => 'Test product for affiliate testing',
                'price' => 100000,
                'commission_rate' => 15.00,
                'is_active' => true,
                'user_id' => 1, // Assuming shop owner exists
            ]
        );

        // Create test affiliate link
        $affiliateLink = AffiliateLink::firstOrCreate(
            [
                'publisher_id' => $publisher->id,
                'product_id' => $product->id,
            ],
            [
                'original_url' => 'https://example.com/test-product',
                'tracking_code' => 'TEST_' . Str::random(10),
                'short_code' => Str::random(6),
                'commission_rate' => 15.00,
                'status' => 'active',
            ]
        );

        $this->command->info('Test data created successfully!');
        $this->command->info("Publisher ID: {$publisher->id}");
        $this->command->info("Product ID: {$product->id}");
        $this->command->info("Affiliate Link ID: {$affiliateLink->id}");
    }
}
