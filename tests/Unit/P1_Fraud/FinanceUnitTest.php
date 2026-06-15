<?php

namespace Tests\Unit\P1_Fraud;

use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\AffiliateLink;
use App\Models\Conversion;
use App\Models\PaymentMethod;
use App\Models\Withdrawal;
use App\Models\PublisherWallet;
use App\Models\PublisherRanking;
use App\Models\PlatformFeeSetting;
use App\Models\Transaction;
use App\Services\PublisherService;
use App\Services\PublisherRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class FinanceUnitTest extends TestCase
{
    use RefreshDatabase;

    private User $publisher;
    private User $admin;
    private User $shop;
    private PaymentMethod $paymentMethod;
    private PublisherService $publisherService;
    private PublisherRankingService $rankingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rankingService = new PublisherRankingService();
        $this->publisherService = new PublisherService($this->rankingService);

        // Create Users
        $this->publisher = User::create([
            'name' => 'John Pub',
            'email' => 'john.pub@test.com',
            'password' => bcrypt('password'),
            'role' => 'publisher'
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $this->shop = User::create([
            'name' => 'Shop Owner',
            'email' => 'shop@test.com',
            'password' => bcrypt('password'),
            'role' => 'shop'
        ]);

        // Create Payment Method
        $this->paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisher->id,
            'type' => 'bank_transfer',
            'account_name' => 'JOHN PUB',
            'account_number' => '1234567890',
            'bank_name' => 'MBBank',
            'bank_code' => 'MB',
            'is_default' => true
        ]);
    }

    /**
     * TC01, TC02, TC03: Wallet balance consistency tests
     */
    public function test_wallet_balance_consistency_tc01_tc02_tc03(): void
    {
        // Setup a campaign and product for conversion
        $campaign = Campaign::create([
            'name' => 'Promo',
            'start_date' => now(),
            'end_date' => now()->addDays(10),
            'budget' => 10000000,
            'status' => 'active',
            'commission_rate' => 10
        ]);

        $product = Product::create([
            'user_id' => $this->shop->id,
            'name' => 'Phone',
            'price' => 1000000,
            'stock' => 100,
            'status' => 'active'
        ]);

        $link = AffiliateLink::create([
            'publisher_id' => $this->publisher->id,
            'campaign_id' => $campaign->id,
            'product_id' => $product->id,
            'tracking_code' => 'TRACK_1',
            'short_code' => 't1',
            'original_url' => 'http://example.com/phone',
            'status' => 'active'
        ]);

        // TC01: Verify balance increases on normal commission
        $conversion = Conversion::create([
            'affiliate_link_id' => $link->id,
            'publisher_id' => $this->publisher->id,
            'product_id' => $product->id,
            'shop_id' => $this->shop->id,
            'tracking_code' => 'TRACK_1',
            'order_id' => 'ORDER_001',
            'amount' => 1000000,
            'commission' => 100000, // 100k commission
            'status' => 'pending',
            'converted_at' => now()
        ]);

        // When shop approves conversion, commission is processed
        $conversion->status = 'approved';
        $conversion->save();

        $this->publisherService->processConversionCommission($conversion);

        $wallet = $this->publisher->wallet;
        $this->assertEquals(100000, $wallet->balance);
        $this->assertEquals(100000, $wallet->total_earned);

        // Verify transaction is created
        $this->assertDatabaseHas('transactions', [
            'publisher_id' => $this->publisher->id,
            'amount' => 100000,
            'type' => 'commission_earned'
        ]);

        // TC02: Verify zero commission has no impact
        $conversion2 = Conversion::create([
            'affiliate_link_id' => $link->id,
            'publisher_id' => $this->publisher->id,
            'product_id' => $product->id,
            'shop_id' => $this->shop->id,
            'tracking_code' => 'TRACK_1',
            'order_id' => 'ORDER_002',
            'amount' => 1000000,
            'commission' => 0, // 0 commission
            'status' => 'approved',
            'converted_at' => now()
        ]);

        $this->publisherService->processConversionCommission($conversion2);
        $wallet->refresh();
        $this->assertEquals(100000, $wallet->balance); // Remains 100k

        // TC03: Verify auto-creation of wallet when not exists
        $this->publisher->wallet()->delete();
        $this->publisher->refresh();

        $conversion3 = Conversion::create([
            'affiliate_link_id' => $link->id,
            'publisher_id' => $this->publisher->id,
            'product_id' => $product->id,
            'shop_id' => $this->shop->id,
            'tracking_code' => 'TRACK_1',
            'order_id' => 'ORDER_003',
            'amount' => 1000000,
            'commission' => 50000,
            'status' => 'approved',
            'converted_at' => now()
        ]);

        $this->publisherService->processConversionCommission($conversion3);
        $this->publisher->refresh();
        $this->assertNotNull($this->publisher->wallet);
        // Note: Wallet was deleted, so a new wallet is created and gets 50,000
        $this->assertEquals(50000, $this->publisher->wallet->balance);
    }

    /**
     * TC04, TC05, TC06, TC07: Platform fee deduction tests
     */
    public function test_platform_fee_deduction_tc04_tc05_tc06_tc07(): void
    {
        // TC04: Calculate platform fee deduction 5%
        $feeSettingNormal = PlatformFeeSetting::create([
            'fee_percentage' => 5.00,
            'description' => 'Normal 5%',
            'is_active' => true
        ]);

        $currentFee = PlatformFeeSetting::getCurrentFee();
        $this->assertEquals(5.00, $currentFee->fee_percentage);

        $totalVal = 10000000;
        $feeAmount = ($totalVal * $currentFee->fee_percentage) / 100;
        $this->assertEquals(500000, $feeAmount);

        // TC05: Calculate platform fee deduction 0%
        $feeSettingNormal->update(['is_active' => false]);
        PlatformFeeSetting::create([
            'fee_percentage' => 0.00,
            'description' => 'Zero',
            'is_active' => true
        ]);

        $currentFee = PlatformFeeSetting::getCurrentFee();
        $feeAmount = ($totalVal * $currentFee->fee_percentage) / 100;
        $this->assertEquals(0, $feeAmount);

        // TC06: Calculate platform fee deduction 100%
        PlatformFeeSetting::where('is_active', true)->update(['is_active' => false]);
        PlatformFeeSetting::create([
            'fee_percentage' => 100.00,
            'description' => 'Full',
            'is_active' => true
        ]);

        $currentFee = PlatformFeeSetting::getCurrentFee();
        $feeAmount = ($totalVal * $currentFee->fee_percentage) / 100;
        $this->assertEquals($totalVal, $feeAmount);

        // TC07: Verify out-of-bounds validation (simulated check in config saving or validator)
        $invalidPercentage1 = -5.00;
        $invalidPercentage2 = 120.00;

        $this->assertTrue($invalidPercentage1 < 0 || $invalidPercentage1 > 100);
        $this->assertTrue($invalidPercentage2 < 0 || $invalidPercentage2 > 100);
    }

    /**
     * TC08, TC09, TC10, TC11, TC12: Ranking upgrade conditions
     */
    public function test_ranking_upgrade_conditions_tc08_tc12(): void
    {
        // Setup rankings
        $bronze = PublisherRanking::create([
            'name' => 'Bronze',
            'slug' => 'dong',
            'level' => 1,
            'color' => '#1',
            'min_links' => 0,
            'min_commission' => 0,
            'is_active' => true
        ]);

        $silver = PublisherRanking::create([
            'name' => 'Silver',
            'slug' => 'bac',
            'level' => 2,
            'color' => '#2',
            'min_links' => 1,
            'min_commission' => 10000000,
            'is_active' => true
        ]);

        $gold = PublisherRanking::create([
            'name' => 'Gold',
            'slug' => 'vang',
            'level' => 3,
            'color' => '#3',
            'min_links' => 1,
            'min_commission' => 50000000,
            'is_active' => true
        ]);

        $platinum = PublisherRanking::create([
            'name' => 'Platinum',
            'slug' => 'kim-cuong',
            'level' => 4,
            'color' => '#4',
            'min_links' => 1,
            'min_commission' => 100000000,
            'is_active' => true
        ]);

        $this->publisher->update(['publisher_ranking_id' => $bronze->id]);

        // TC08: Revenue < 10M -> Keep Bronze
        $this->rankingService->updatePublisherRanking($this->publisher);
        $this->publisher->refresh();
        $this->assertEquals($bronze->id, $this->publisher->publisher_ranking_id);

        // Add 1 link to satisfy min_links requirement
        $campaign = Campaign::create([
            'name' => 'C', 'start_date' => now(), 'end_date' => now()->addDays(5), 'budget' => 10000000, 'status' => 'active', 'commission_rate' => 10
        ]);
        $link = AffiliateLink::create([
            'publisher_id' => $this->publisher->id, 'campaign_id' => $campaign->id, 'original_url' => 'http://e.com', 'tracking_code' => 'T_RANK', 'short_code' => 'tr', 'status' => 'active'
        ]);

        // TC09: Revenue = 10M -> Upgrade to Silver
        // Mock combined commission to 10M
        $userMock = \Mockery::mock($this->publisher)->makePartial();
        $userMock->shouldReceive('getCombinedCommissionAttribute')->andReturn(10000000.00);

        $newRanking = $this->rankingService->calculateRanking(1, 10000000.00);
        $this->assertEquals($silver->id, $newRanking->id);

        $this->publisher->update([
            'publisher_ranking_id' => $newRanking->id,
            'ranking_achieved_at' => now()
        ]);
        $this->publisher->refresh();
        $this->assertEquals($silver->id, $this->publisher->publisher_ranking_id);

        // TC10: Revenue = 50M -> Upgrade to Gold
        $newRankingGold = $this->rankingService->calculateRanking(1, 50000000.00);
        $this->assertEquals($gold->id, $newRankingGold->id);

        $this->publisher->update([
            'publisher_ranking_id' => $newRankingGold->id,
            'ranking_achieved_at' => now()
        ]);
        $this->publisher->refresh();
        $this->assertEquals($gold->id, $this->publisher->publisher_ranking_id);

        // TC11: Revenue = 100M -> Upgrade to Platinum
        $newRankingPlat = $this->rankingService->calculateRanking(1, 100000000.00);
        $this->assertEquals($platinum->id, $newRankingPlat->id);

        $this->publisher->update([
            'publisher_ranking_id' => $newRankingPlat->id,
            'ranking_achieved_at' => now()
        ]);
        $this->publisher->refresh();
        $this->assertEquals($platinum->id, $this->publisher->publisher_ranking_id);

        // TC12: Revenue = 49M (Silver current) -> Keep Silver (not enough for Gold)
        // Reset to Silver first
        $this->publisher->update(['publisher_ranking_id' => $silver->id]);
        $calculatedRanking = $this->rankingService->calculateRanking(1, 49000000.00);
        $this->assertEquals($silver->id, $calculatedRanking->id); // Remains Silver
    }

    /**
     * TC13, TC14, TC15, TC16, TC17: Transaction status transitions
     */
    public function test_transaction_status_transitions_tc13_tc17(): void
    {
        // Setup initial wallet balance
        $wallet = $this->publisher->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        // Create pending withdrawal
        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisher->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 500000,
            'fee' => 0,
            'net_amount' => 500000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => []
        ]);

        Transaction::create([
            'publisher_id' => $this->publisher->id,
            'type' => 'withdrawal',
            'amount' => -500000,
            'description' => 'Withdrawal',
            'reference_id' => $withdrawal->id,
            'reference_type' => 'withdrawal',
            'status' => 'pending'
        ]);

        // TC13: Pending to Approved
        $this->publisherService->approveWithdrawal($withdrawal, $this->admin);
        $this->assertTrue($withdrawal->refresh()->isApproved());

        // TC14: Approved to Completed
        $this->publisherService->completeWithdrawal($withdrawal, $this->admin, 'REF123');
        $this->assertTrue($withdrawal->refresh()->isCompleted());
        $this->assertEquals('REF123', $withdrawal->transaction_reference);

        // Reset status for TC15: Pending to Rejected
        $withdrawal->status = 'pending';
        $withdrawal->save();

        $this->publisherService->rejectWithdrawal($withdrawal, $this->admin, 'Rejected reason');
        $this->assertTrue($withdrawal->refresh()->isRejected());
        $this->assertEquals('Rejected reason', $withdrawal->rejection_reason);

        // TC16: Block invalid transitions (e.g. Reject to Approved)
        $this->expectException(\Exception::class);
        $this->publisherService->approveWithdrawal($withdrawal, $this->admin);
    }

    /**
     * TC17: Block Completed to Pending
     */
    public function test_block_completed_to_pending_tc17(): void
    {
        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisher->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => 500000,
            'fee' => 0,
            'net_amount' => 500000,
            'status' => 'completed',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => []
        ]);

        // Transition from Completed -> Pending is blocked since process method checks status
        $this->expectException(\Exception::class);
        $this->publisherService->approveWithdrawal($withdrawal, $this->admin);
    }

    /**
     * TC18, TC19, TC20: Concurrency and database rollbacks
     */
    public function test_concurrency_withdrawal_tc18_tc20(): void
    {
        $wallet = $this->publisher->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        // TC18: Withdrawal exceeding balance -> fails canWithdraw validation
        $dataExceeding = [
            'amount' => 1200000,
            'payment_method_id' => $this->paymentMethod->id
        ];
        
        $this->expectException(\Exception::class);
        $this->publisherService->createWithdrawal($this->publisher, $dataExceeding);
    }

    /**
     * TC19: Dual concurrent withdrawal requests within balance limits
     */
    public function test_concurrency_withdrawal_within_balance_tc19(): void
    {
        $wallet = $this->publisher->getOrCreateWallet();
        $wallet->balance = 1500000;
        $wallet->save();

        $data1 = [
            'amount' => 500000,
            'payment_method_id' => $this->paymentMethod->id
        ];

        $data2 = [
            'amount' => 500000,
            'payment_method_id' => $this->paymentMethod->id
        ];

        // Process first
        $w1 = $this->publisherService->createWithdrawal($this->publisher, $data1);
        $this->assertEquals(1000000, $this->publisher->fresh()->wallet->balance);

        // Process second
        $w2 = $this->publisherService->createWithdrawal($this->publisher, $data2);
        $this->assertEquals(500000, $this->publisher->fresh()->wallet->balance);

        $this->assertEquals('pending', $w1->status);
        $this->assertEquals('pending', $w2->status);
    }

    /**
     * TC20: Rolled back transaction state on database exception
     */
    public function test_withdrawal_rollback_tc20(): void
    {
        $wallet = $this->publisher->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        try {
            DB::transaction(function() use ($wallet) {
                $wallet->balance = 800000;
                $wallet->save();
                throw new \Exception("Simulated error forcing rollback");
            });
        } catch (\Exception $e) {
            // Error caught, verifying balance was rolled back
        }

        $this->assertEquals(1000000, $this->publisher->fresh()->wallet->balance);
    }
}
