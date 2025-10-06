<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AffiliateLink;
use App\Models\Click;
use App\Models\Conversion;
use App\Models\Transaction;
use App\Services\CommissionService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CreateTestCommissionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-commission-data {--publisher-id= : Publisher ID to create data for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test commission data for testing wallet sync';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $publisherId = $this->option('publisher-id');
        
        if (!$publisherId) {
            $publisher = User::whereIn('role', ['publisher', 'shop'])->first();
            if (!$publisher) {
                $this->error('No publisher found. Please create a publisher first.');
                return 1;
            }
            $publisherId = $publisher->id;
        }
        
        $publisher = User::find($publisherId);
        if (!$publisher) {
            $this->error("Publisher with ID {$publisherId} not found");
            return 1;
        }
        
        $this->info("Creating test commission data for publisher: {$publisher->name} (ID: {$publisherId})");
        
        // Tạo affiliate link test
        $affiliateLink = AffiliateLink::create([
            'publisher_id' => $publisher->id,
            'product_id' => 1,
            'tracking_code' => 'TEST_' . time(),
            'short_code' => 'S' . rand(1000, 9999),
            'original_url' => 'https://example.com/product',
            'status' => 'active',
            'commission_rate' => 15.00,
            'campaign_id' => null
        ]);
        
        $this->info("Created affiliate link: {$affiliateLink->tracking_code}");
        
        // Tạo click cũ (35 ngày trước) - sẽ có available balance
        $oldClick = Click::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => 1,
            'tracking_code' => $affiliateLink->tracking_code,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'referrer' => 'https://test.com',
            'clicked_at' => Carbon::now()->subDays(35),
        ]);
        
        // Tạo transaction cũ cho click
        Transaction::create([
            'publisher_id' => $publisher->id,
            'type' => 'commission_earned',
            'amount' => 100000, // 100,000 VNĐ
            'status' => 'completed',
            'description' => 'Hoa hồng từ click cũ (35 ngày trước)',
            'reference_type' => 'click_commission',
            'reference_id' => $oldClick->id,
            'metadata' => [
                'type' => 'click_commission',
                'click_id' => $oldClick->id,
                'affiliate_link_id' => $affiliateLink->id,
                'cpc' => 100000,
            ],
            'processed_at' => Carbon::now()->subDays(35),
        ]);
        
        $this->info("Created old click transaction (35 days ago): 100,000 VNĐ");
        
        // Tạo click mới (5 ngày trước) - sẽ có pending balance
        $newClick = Click::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => 1,
            'tracking_code' => $affiliateLink->tracking_code,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Browser',
            'referrer' => 'https://test.com',
            'clicked_at' => Carbon::now()->subDays(5),
        ]);
        
        // Tạo transaction mới cho click
        Transaction::create([
            'publisher_id' => $publisher->id,
            'type' => 'commission_earned',
            'amount' => 200000, // 200,000 VNĐ
            'status' => 'completed',
            'description' => 'Hoa hồng từ click mới (5 ngày trước)',
            'reference_type' => 'click_commission',
            'reference_id' => $newClick->id,
            'metadata' => [
                'type' => 'click_commission',
                'click_id' => $newClick->id,
                'affiliate_link_id' => $affiliateLink->id,
                'cpc' => 200000,
            ],
            'processed_at' => Carbon::now()->subDays(5),
        ]);
        
        $this->info("Created new click transaction (5 days ago): 200,000 VNĐ");
        
        // Tạo conversion cũ (40 ngày trước) - sẽ có available balance
        $oldConversion = Conversion::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => 1,
            'shop_id' => $affiliateLink->product->user_id ?? null,
            'tracking_code' => $affiliateLink->tracking_code,
            'order_id' => 'ORDER_OLD_' . time(),
            'amount' => 1000000, // 1,000,000 VNĐ
            'commission' => 150000, // 15% = 150,000 VNĐ
            'converted_at' => Carbon::now()->subDays(40),
            'status' => 'approved',
            'is_commission_processed' => true,
            'commission_processed_at' => Carbon::now()->subDays(39),
        ]);
        
        // Tạo transaction cũ cho conversion
        Transaction::create([
            'publisher_id' => $publisher->id,
            'type' => 'commission_earned',
            'amount' => 150000, // 150,000 VNĐ
            'status' => 'completed',
            'description' => 'Hoa hồng từ conversion cũ (40 ngày trước)',
            'reference_type' => 'conversion_commission',
            'reference_id' => $oldConversion->id,
            'metadata' => [
                'type' => 'conversion_commission',
                'conversion_id' => $oldConversion->id,
                'affiliate_link_id' => $affiliateLink->id,
                'order_amount' => 1000000,
                'commission_rate' => 15,
            ],
            'processed_at' => Carbon::now()->subDays(40),
        ]);
        
        $this->info("Created old conversion transaction (40 days ago): 150,000 VNĐ");
        
        // Tạo conversion mới (10 ngày trước) - sẽ có pending balance
        $newConversion = Conversion::create([
            'affiliate_link_id' => $affiliateLink->id,
            'publisher_id' => $publisher->id,
            'product_id' => 1,
            'shop_id' => $affiliateLink->product->user_id ?? null,
            'tracking_code' => $affiliateLink->tracking_code,
            'order_id' => 'ORDER_NEW_' . time(),
            'amount' => 2000000, // 2,000,000 VNĐ
            'commission' => 300000, // 15% = 300,000 VNĐ
            'converted_at' => Carbon::now()->subDays(10),
            'status' => 'pending',
            'is_commission_processed' => false,
        ]);
        
        // Tạo transaction mới cho conversion
        Transaction::create([
            'publisher_id' => $publisher->id,
            'type' => 'commission_earned',
            'amount' => 300000, // 300,000 VNĐ
            'status' => 'completed',
            'description' => 'Hoa hồng từ conversion mới (10 ngày trước)',
            'reference_type' => 'conversion_commission',
            'reference_id' => $newConversion->id,
            'metadata' => [
                'type' => 'conversion_commission',
                'conversion_id' => $newConversion->id,
                'affiliate_link_id' => $affiliateLink->id,
                'order_amount' => 2000000,
                'commission_rate' => 15,
            ],
            'processed_at' => Carbon::now()->subDays(10),
        ]);
        
        $this->info("Created new conversion transaction (10 days ago): 300,000 VNĐ");
        
        // Sync wallet
        $commissionService = new CommissionService();
        $commissionService->syncWalletFromTransactions($publisher);
        
        $this->info("✅ Test commission data created successfully!");
        $this->info("📊 Expected results:");
        $this->info("   - Total Earned: 750,000 VNĐ (100k + 200k + 150k + 300k)");
        $this->info("   - Available Balance: 250,000 VNĐ (100k + 150k from old transactions)");
        $this->info("   - Pending Balance: 500,000 VNĐ (200k + 300k from new transactions)");
        
        return 0;
    }
}
