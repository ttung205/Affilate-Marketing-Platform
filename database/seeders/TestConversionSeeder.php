<?php

namespace Database\Seeders;

use App\Models\AffiliateLink;
use App\Models\Conversion;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestConversionSeeder extends Seeder
{
    public function run(): void
    {
        $publisherId = 3;

        /** @var \App\Models\User|null $publisher */
        $publisher = User::find($publisherId);

        if (!$publisher) {
            $this->command->error("Publisher with ID {$publisherId} not found.");
            $this->command->warn('Create the publisher user first, then rerun the seeder.');
            return;
        }

        if ($publisher->role !== 'publisher') {
            $this->command->error("User ID {$publisherId} is not a publisher. Current role: {$publisher->role}");
            return;
        }

        /** @var \App\Models\Product|null $product */
        $product = Product::whereNotNull('user_id')->latest()->first();

        if (!$product) {
            $this->command->error('No product with an assigned shop owner was found.');
            $this->command->warn('Create a product in the shop first, then rerun the seeder.');
            return;
        }

        $shopId = $product->user_id;

        if (!$shopId) {
            $this->command->error('The selected product does not have an associated shop user.');
            return;
        }

        $affiliateLink = AffiliateLink::firstOrCreate(
            [
                'publisher_id' => $publisher->id,
                'product_id' => $product->id,
            ],
            [
                'campaign_id' => null,
                'original_url' => $product->affiliate_link ?? (string) $product->id,
                'tracking_code' => 'TEST-' . Str::upper(Str::random(10)),
                'short_code' => Str::upper(Str::random(6)),
                'status' => 'active',
                'commission_rate' => $product->commission_rate ?? 10,
            ]
        );

        $amount = $product->price ?? 100000;
        $commissionRate = $product->commission_rate ?? 10;

        $conversion = Conversion::updateOrCreate(
            [
                'order_id' => 'TEST-ORDER-' . $product->id,
                'affiliate_link_id' => $affiliateLink->id,
            ],
            [
                'publisher_id' => $publisher->id,
                'product_id' => $product->id,
                'tracking_code' => $affiliateLink->tracking_code,
                'amount' => $amount,
                'commission' => round($amount * ($commissionRate / 100), 2),
                'converted_at' => now()->subMinutes(5),
                'status' => 'pending',
                'status_changed_by' => null,
                'status_changed_at' => null,
                'status_note' => null,
                'is_commission_processed' => false,
                'commission_processed_at' => null,
                'shop_id' => $shopId,
            ]
        );

        $this->command->info('Test conversion data created successfully.');
        $this->command->info("Product ID: {$product->id}");
        $this->command->info("Affiliate Link ID: {$affiliateLink->id}");
        $this->command->info("Conversion ID: {$conversion->id}");
        $this->command->info('Login as the shop owner and open the conversions page to review the pending order.');
    }
}


