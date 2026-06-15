<?php

namespace Tests\Unit\P1_Fraud;

use Tests\TestCase;
use App\Models\User;
use App\Models\Click;
use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\Campaign;
use App\Models\PublisherWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ClickUserUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Click model attributes and relationships.
     */
    public function test_click_model_functionalities(): void
    {
        // 1. Create dependencies
        $publisher = User::create([
            'name' => 'John Publisher',
            'email' => 'john@test.com',
            'password' => bcrypt('password'),
            'role' => 'publisher'
        ]);

        $campaign = Campaign::create([
            'name' => 'Summer Camp',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'budget' => 50000000,
            'status' => 'active',
            'commission_rate' => 15
        ]);

        $product = Product::create([
            'name' => 'iPhone 15 Pro',
            'price' => 25000000,
            'stock' => 10,
            'user_id' => $publisher->id, // Owner
            'status' => 'active'
        ]);

        $affiliateLink = AffiliateLink::create([
            'publisher_id' => $publisher->id,
            'campaign_id' => $campaign->id,
            'product_id' => $product->id,
            'tracking_code' => 'TRACK_XYZ',
            'short_code' => 'xyz',
            'original_url' => 'https://shop.com/iphone',
            'status' => 'active'
        ]);

        // 2. Create Click
        $click = Click::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => $product->id,
            'tracking_code' => 'TRACK_XYZ',
            'ip_address' => '192.168.1.50',
            'user_agent' => 'Mozilla/5.0 Chrome/115.0',
            'referrer' => 'https://facebook.com',
            'clicked_at' => now(),
        ]);

        // 3. Assert Relationships
        $this->assertEquals($affiliateLink->id, $click->affiliateLink->id);
        $this->assertEquals($publisher->id, $click->publisher->id);
        $this->assertEquals($product->id, $click->product->id);

        // 4. Assert helper: Unique Click Attribute
        // Since it's the only click from this IP for this link, it should be unique
        $this->assertTrue($click->is_unique_click);

        // Create another click from the SAME IP for the SAME link
        $duplicateClick = Click::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => $product->id,
            'tracking_code' => 'TRACK_XYZ',
            'ip_address' => '192.168.1.50', // Same IP
            'user_agent' => 'Mozilla/5.0 Chrome/115.0',
            'referrer' => 'https://facebook.com',
            'clicked_at' => now(),
        ]);

        // The second click from the same IP is not unique anymore!
        $this->assertFalse($duplicateClick->is_unique_click);

        // A click from a DIFFERENT IP should be unique
        $differentIpClick = Click::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => $product->id,
            'tracking_code' => 'TRACK_XYZ',
            'ip_address' => '10.0.0.1', // Different IP
            'user_agent' => 'Mozilla/5.0 Chrome/115.0',
            'referrer' => 'https://facebook.com',
            'clicked_at' => now(),
        ]);

        $this->assertTrue($differentIpClick->is_unique_click);
    }

    /**
     * Test User model roles, stats helpers and wallet creation.
     */
    public function test_user_model_functionalities(): void
    {
        // 1. Create a Publisher User
        $user = User::create([
            'name' => 'Alice Publisher',
            'email' => 'alice@test.com',
            'password' => bcrypt('password'),
            'role' => 'publisher'
        ]);

        // Test roles helper
        $this->assertTrue($user->isPublisher());
        $this->assertFalse($user->isShop());

        // Test default wallet initialization
        $wallet = $user->getOrCreateWallet();
        $this->assertInstanceOf(PublisherWallet::class, $wallet);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals(0, $user->getAvailableBalance());

        // Test clicks count and conversions count properties
        $this->assertEquals(0, $user->total_clicks);
        $this->assertEquals(0, $user->total_conversions);
        $this->assertEquals(0.0, $user->conversion_rate);

        // 2. Create a Shop User
        $shop = User::create([
            'name' => 'Bob Shop',
            'email' => 'bob@test.com',
            'password' => bcrypt('password'),
            'role' => 'shop'
        ]);

        $this->assertTrue($shop->isPublisher()); // Shop is also considered publisher in isPublisher helper
        $this->assertTrue($shop->isShop());
    }
}
