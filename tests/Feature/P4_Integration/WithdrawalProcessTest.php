<?php

namespace Tests\Feature\P4_Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\AffiliateLink;
use App\Models\Conversion;
use App\Models\PaymentMethod;
use App\Models\Withdrawal;
use App\Models\PublisherWallet;
use App\Models\Transaction;
use App\Mail\WithdrawalOTPMail;
use App\Notifications\WithdrawalRequestNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class WithdrawalProcessTest extends TestCase
{
    use RefreshDatabase;

    private User $shopUser;
    private User $publisherUser;
    private User $adminUser;
    private Product $product;
    private AffiliateLink $affiliateLink;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tạo các vai trò người dùng
        $this->shopUser = User::create([
            'name' => 'Shop A',
            'email' => 'shop_a@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);

        $this->publisherUser = User::create([
            'name' => 'Publisher A',
            'email' => 'publisher_a@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin A',
            'email' => 'admin_a@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 2. Tạo sản phẩm của shop
        $this->product = Product::create([
            'user_id' => $this->shopUser->id,
            'name' => 'Laptop Dell XPS',
            'description' => 'Laptop cao cấp',
            'price' => 20000000,
            'sku' => 'DELL-XPS-13',
            'status' => 'approved',
        ]);

        // 3. Tạo link affiliate cho publisher
        $this->affiliateLink = AffiliateLink::create([
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'original_url' => 'http://example.com/product/1',
            'tracking_code' => 'TRACK123',
            'short_code' => 'sh123',
            'commission_rate' => 10.00, // 10% hoa hồng
            'status' => 'active',
        ]);

        // Đảm bảo ví publisher được khởi tạo
        $this->publisherUser->getOrCreateWallet();
    }

    /**
     * Test phân bổ hoa hồng (Conversion Attribution)
     */
    public function test_conversion_attribution()
    {
        // Gửi webhook tạo conversion ở trạng thái pending
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK123',
            'order_id' => 'ORDER-999',
            'amount' => 10000000, // Đơn hàng 10 triệu
            'commission_rate' => 10.00,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'pending');

        $conversion = Conversion::where('order_id', 'ORDER-999')->first();
        $this->assertNotNull($conversion);
        $this->assertEquals($this->publisherUser->id, $conversion->publisher_id);
        $this->assertEquals(1000000, $conversion->commission); // 10% của 10 triệu là 1 triệu

        // Số dư ví publisher vẫn chưa đổi vì mới chỉ là pending
        $this->publisherUser->getOrCreateWallet()->refresh();
        $this->assertEquals(0, $this->publisherUser->getOrCreateWallet()->balance);

        // Shop phê duyệt conversion
        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'approved',
                'status_note' => 'Đơn hàng hợp lệ',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));

        // Kiểm tra xem conversion đã được cập nhật thành approved và đã xử lý hoa hồng
        $conversion->refresh();
        $this->assertEquals('approved', $conversion->status);
        $this->assertTrue($conversion->is_commission_processed);

        // Kiểm tra số dư ví publisher được cộng tiền
        $this->publisherUser->getOrCreateWallet()->refresh();
        $this->assertEquals(1000000, $this->publisherUser->getOrCreateWallet()->balance);

        // Kiểm tra xem giao dịch transaction đã được ghi nhận trong DB
        $this->assertDatabaseHas('transactions', [
            'publisher_id' => $this->publisherUser->id,
            'type' => 'commission_earned',
            'amount' => 1000000,
            'status' => 'completed',
            'reference_type' => 'conversion_commission',
            'reference_id' => $conversion->id,
        ]);
    }

    /**
     * Test luồng rút tiền đầy đủ có OTP và phê duyệt từ Admin
     */
    public function test_full_withdrawal_process_with_otp_and_approval()
    {
        Mail::fake();
        Notification::fake();

        // Cấp tiền cho ví publisher (1 triệu)
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        // Tạo phương thức thanh toán ngân hàng
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        // --- BƯỚC 1: Publisher yêu cầu rút tiền lần đầu (tạo OTP) ---
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000, // Rút 500k
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('requires_otp', true);
        
        $sessionKey = $response->json('withdrawal_session_key');
        $this->assertNotEmpty($sessionKey);

        // Kiểm tra email OTP được gửi
        Mail::assertSent(WithdrawalOTPMail::class, function ($mail) {
            return $mail->hasTo($this->publisherUser->email);
        });

        // Lấy mã OTP trong Cache
        $otpKey = "withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}";
        $otpData = Cache::get($otpKey);
        $this->assertNotNull($otpData);
        $otp = $otpData['otp'];

        // --- BƯỚC 2: Xác thực OTP và tạo giao dịch rút tiền ---
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Số dư ví bị trừ ngay lập tức (giữ tạm thời)
        $wallet->refresh();
        $this->assertEquals(500000, $wallet->balance);

        // Kiểm tra bản ghi Withdrawal được tạo ở trạng thái pending
        $withdrawal = Withdrawal::where('publisher_id', $this->publisherUser->id)->first();
        $this->assertNotNull($withdrawal);
        $this->assertEquals('pending', $withdrawal->status);
        $this->assertEquals(500000, $withdrawal->amount);

        // Kiểm tra thông báo gửi tới Admin
        Notification::assertSentTo($this->adminUser, WithdrawalRequestNotification::class);

        // --- BƯỚC 3: Admin duyệt yêu cầu rút tiền qua API ---
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.approve', $withdrawal), [
                'notes' => 'Hồ sơ hợp lệ',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $withdrawal->refresh();
        $this->assertEquals('approved', $withdrawal->status);

        // --- BƯỚC 4: Admin xác nhận hoàn thành chuyển tiền qua API ---
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.complete', $withdrawal), [
                'transaction_reference' => 'FT123456789',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $withdrawal->refresh();
        $this->assertEquals('completed', $withdrawal->status);
        $this->assertEquals('FT123456789', $withdrawal->transaction_reference);
    }

    /**
     * Test validate các giá trị biên đầu vào và các trường hợp lỗi rút tiền
     */
    public function test_invalid_withdrawal_inputs()
    {
        Mail::fake();

        // Ví publisher có 1 triệu
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        // 1. Rút tiền nhỏ hơn mức tối thiểu 100k -> báo lỗi validate
        $response = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 50000, // 50k
                'payment_method_id' => $paymentMethod->id,
            ]);
        $response->assertSessionHasErrors(['amount']);

        // 2. Rút tiền lớn hơn mức tối đa 5M -> báo lỗi validate
        $response = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 6000000, // 6 triệu
                'payment_method_id' => $paymentMethod->id,
            ]);
        $response->assertSessionHasErrors(['amount']);

        // 3. Rút số tiền lớn hơn số dư ví (rút 2 triệu trong khi ví có 1 triệu)
        // Đầu tiên gửi yêu cầu 2 triệu để sinh OTP (validate max là 5M nên request đầu tiên qua)
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 2000000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        
        $sessionKey = $response->json('withdrawal_session_key');
        $otpKey = "withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}";
        $otpData = Cache::get($otpKey);
        $otp = $otpData['otp'];

        // Xác thực với OTP -> Trả về lỗi 422 hoặc quăng Exception do ví không đủ tiền
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);
        
        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $this->assertStringContainsString('Số dư không đủ', $response->json('message'));

        // 4. Nhập sai OTP
        // Tạo yêu cầu hợp lệ 500k
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $response->json('withdrawal_session_key');

        // Xác thực với OTP sai -> Trả về lỗi 422 / Exception
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => '999999', // OTP sai
                'withdrawal_session_key' => $sessionKey,
            ]);
        
        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Mã OTP không đúng hoặc đã hết hạn', $response->json('message'));

        // 5. Thử lại sai OTP quá 3 lần -> Bị block OTP
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => '111111',
                'withdrawal_session_key' => $sessionKey,
            ]);
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => '222222',
                'withdrawal_session_key' => $sessionKey,
            ]);

        // Lần sai thứ 3 sẽ xóa cache OTP và báo lỗi
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => '333333',
                'withdrawal_session_key' => $sessionKey,
            ]);
        
        $response->assertStatus(422);
        // Cache OTP bị xóa sạch
        $this->assertNull(Cache::get($otpKey));
    }

    /**
     * Test Publisher tự hủy yêu cầu rút tiền đang chờ duyệt -> được hoàn tiền vào ví
     */
    public function test_publisher_cancel_pending_withdrawal()
    {
        // Cấp ví 1 triệu
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        // Tạo bản ghi rút tiền trực tiếp (giả lập đã qua bước OTP)
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 400000,
            'fee' => 0,
            'net_amount' => 400000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // Trừ tiền trong ví
        $wallet->balance = 600000;
        $wallet->save();

        // Tạo Transaction cho withdrawal
        Transaction::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'withdrawal',
            'amount' => -400000,
            'description' => "Rút tiền #{$withdrawal->id}",
            'reference_id' => $withdrawal->id,
            'reference_type' => 'withdrawal',
        ]);

        // Publisher gọi API hủy
        $response = $this->actingAs($this->publisherUser)
            ->postJson(route('publisher.withdrawal.cancel', $withdrawal));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Kiểm tra status
        $withdrawal->refresh();
        $this->assertEquals('cancelled', $withdrawal->status);

        // Kiểm tra ví được hoàn trả 400k -> quay lại 1 triệu
        $wallet->refresh();
        $this->assertEquals(1000000, $wallet->balance);

        // Kiểm tra transaction status đổi thành cancelled
        $this->assertDatabaseHas('transactions', [
            'reference_id' => $withdrawal->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * Test Admin từ chối yêu cầu rút tiền đang chờ duyệt -> hoàn tiền vào ví publisher
     */
    public function test_admin_reject_pending_withdrawal()
    {
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // Trừ ví
        $wallet->balance = 700000;
        $wallet->save();

        Transaction::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'withdrawal',
            'amount' => -300000,
            'description' => "Rút tiền #{$withdrawal->id}",
            'reference_id' => $withdrawal->id,
            'reference_type' => 'withdrawal',
        ]);

        // Admin từ chối qua API
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.reject', $withdrawal), [
                'reason' => 'Thông tin ngân hàng không chính xác',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Kiểm tra status
        $withdrawal->refresh();
        $this->assertEquals('rejected', $withdrawal->status);
        $this->assertEquals('Thông tin ngân hàng không chính xác', $withdrawal->rejection_reason);

        // Kiểm tra ví được hoàn trả 300k -> quay lại 1 triệu
        $wallet->refresh();
        $this->assertEquals(1000000, $wallet->balance);

        // Kiểm tra transaction status đổi thành failed
        $this->assertDatabaseHas('transactions', [
            'reference_id' => $withdrawal->id,
            'status' => 'failed',
        ]);
    }

    /**
     * TC-WD-001: Withdrawal Amount Below Minimum Boundary
     */
    public function test_tc_wd_001_amount_below_minimum_boundary()
    {
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 99999, // Below 100,000 VND
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('withdrawals', 0);
    }

    /**
     * TC-WD-002: Withdrawal Amount At Minimum Boundary
     */
    public function test_tc_wd_002_amount_at_minimum_boundary()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 100000, // Exactly 100,000 VND
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('requires_otp', true);
    }

    /**
     * TC-WD-003: Withdrawal Amount Above Minimum Boundary
     */
    public function test_tc_wd_003_amount_above_minimum_boundary()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 100001, // 100,001 VND
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('requires_otp', true);
    }

    /**
     * TC-WD-004: Withdrawal Amount Below Maximum Boundary
     */
    public function test_tc_wd_004_amount_below_maximum_boundary()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 6000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 4999999, // 4,999,999 VND
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('requires_otp', true);
    }

    /**
     * TC-WD-005: Withdrawal Amount At Maximum Boundary
     */
    public function test_tc_wd_005_amount_at_maximum_boundary()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 6000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 5000000, // Exactly 5,000,000 VND
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('requires_otp', true);
    }

    /**
     * TC-WD-006: Withdrawal Amount Above Maximum Boundary
     */
    public function test_tc_wd_006_amount_above_maximum_boundary()
    {
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 6000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 5000001, // Above 5,000,000 VND
                'payment_method_id' => $paymentMethod->id,
            ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseCount('withdrawals', 0);
    }

    /**
     * TC-WD-007: Unauthorized User Cannot Cancel Withdrawal
     */
    public function test_tc_wd_007_unauthorized_user_cannot_cancel_withdrawal()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        $publisherB = User::create([
            'name' => 'Publisher B',
            'email' => 'publisher_b@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);

        $response = $this->actingAs($publisherB)
            ->postJson(route('publisher.withdrawal.cancel', $withdrawal));

        $response->assertStatus(403);
        $response->assertJsonPath('success', false);
        
        $withdrawal->refresh();
        $this->assertEquals('pending', $withdrawal->status);
    }

    /**
     * TC-WD-008: Non-Admin Cannot Approve Withdrawal
     */
    public function test_tc_wd_008_non_admin_cannot_approve_withdrawal()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->postJson(route('admin.withdrawals.api.approve', $withdrawal), [
                'notes' => 'Hack approve',
            ]);

        $response->assertStatus(403);
        $withdrawal->refresh();
        $this->assertEquals('pending', $withdrawal->status);
    }

    /**
     * TC-WD-009: Non-Admin Cannot Reject Withdrawal
     */
    public function test_tc_wd_009_non_admin_cannot_reject_withdrawal()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->postJson(route('admin.withdrawals.api.reject', $withdrawal), [
                'reason' => 'Hack reject',
            ]);

        $response->assertStatus(403);
        $withdrawal->refresh();
        $this->assertEquals('pending', $withdrawal->status);
    }

    /**
     * TC-WD-010: Guest User Cannot Access Withdrawal APIs
     */
    public function test_tc_wd_010_guest_user_cannot_access_withdrawal_apis()
    {
        $response = $this->postJson(route('publisher.withdrawal.store'), [
            'amount' => 200000,
            'payment_method_id' => 1,
        ]);

        $response->assertStatus(401);
    }

    /**
     * TC-WD-011: Expired OTP
     */
    public function test_tc_wd_011_expired_otp()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);

        $sessionKey = $response->json('withdrawal_session_key');
        $otpData = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}");
        $otp = $otpData['otp'];

        // Simulate OTP expiration (11 minutes later)
        try {
            \Illuminate\Support\Carbon::setTestNow(now()->addMinutes(11));

            $responseVerify = $this->actingAs($this->publisherUser)
                ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->postJson(route('publisher.withdrawal.store'), [
                    'otp' => $otp,
                    'withdrawal_session_key' => $sessionKey,
                ]);

            $responseVerify->assertStatus(422);
            $this->assertFalse($responseVerify->json('success'));
            $this->assertStringContainsString('Mã OTP không đúng hoặc đã hết hạn', $responseVerify->json('message'));
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }

        $this->assertDatabaseCount('withdrawals', 0);
    }

    /**
     * TC-WD-012: Reuse Used OTP
     */
    public function test_tc_wd_012_reuse_used_otp()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $response->json('withdrawal_session_key');
        $otpData = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}");
        $otp = $otpData['otp'];

        // Verify successfully first time
        $response1 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);
        $response1->assertStatus(200);

        // Attempt verify second time with same parameters
        $response2 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);
        $response2->assertStatus(422);
    }

    /**
     * TC-WD-013: Invalid Session Key
     */
    public function test_tc_wd_013_invalid_session_key()
    {
        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => '123456',
                'withdrawal_session_key' => 'fake_session_key_123',
            ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Phiên rút tiền không hợp lệ hoặc đã hết hạn', $response->json('message'));
    }

    /**
     * TC-WD-014: OTP Brute Force Protection
     */
    public function test_tc_wd_014_otp_brute_force_protection()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $response->json('withdrawal_session_key');
        $otpKey = "withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}";

        // Input incorrect OTP 4 times to trigger cache block & clear
        for ($i = 0; $i < 4; $i++) {
            $responseFail = $this->actingAs($this->publisherUser)
                ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
                ->postJson(route('publisher.withdrawal.store'), [
                    'otp' => '999999',
                    'withdrawal_session_key' => $sessionKey,
                ]);
            $responseFail->assertStatus(422);
        }

        // Cache must be cleared
        $this->assertNull(Cache::get($otpKey));
    }

    /**
     * TC-WD-015: Duplicate Withdrawal Request
     */
    public function test_tc_wd_015_duplicate_withdrawal_request()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $response->json('withdrawal_session_key');
        $otp = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}")['otp'];

        // Send 2 identical OTP verification requests
        $response1 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);
        $response1->assertStatus(200);

        $response2 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);
        $response2->assertStatus(422);

        // Assert exactly 1 withdrawal record was created
        $this->assertDatabaseCount('withdrawals', 1);

        // Balance only deducted once (1M - 500k = 500k)
        $wallet->refresh();
        $this->assertEquals(500000, $wallet->balance);
    }

    /**
     * TC-WD-016: Wallet Balance Consistency
     */
    public function test_tc_wd_016_wallet_balance_consistency()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1500000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 450000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $response->json('withdrawal_session_key');
        $otp = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}")['otp'];

        $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);

        $wallet->refresh();
        $this->assertEquals(1050000, $wallet->balance); // 1,500,000 - 450,000 = 1,050,000
    }

    /**
     * TC-WD-017: Transaction Record Integrity
     */
    public function test_tc_wd_017_transaction_record_integrity()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 300000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $response->json('withdrawal_session_key');
        $otp = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey}")['otp'];

        $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $sessionKey,
            ]);

        $withdrawal = Withdrawal::where('publisher_id', $this->publisherUser->id)->first();

        $this->assertDatabaseHas('transactions', [
            'publisher_id' => $this->publisherUser->id,
            'type' => 'withdrawal',
            'amount' => -300000,
            'reference_id' => $withdrawal->id,
            'reference_type' => 'withdrawal',
        ]);
    }

    /**
     * TC-WD-018: Concurrent Withdrawal Requests
     */
    public function test_tc_wd_018_concurrent_withdrawal_requests()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        // Request 1 for 800k
        $response1 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 800000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey1 = $response1->json('withdrawal_session_key');
        $otp1 = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey1}")['otp'];

        // Sleep 1 second to ensure different timestamp for Request 2's session key
        sleep(1);

        // Request 2 for 800k
        $response2 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 800000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey2 = $response2->json('withdrawal_session_key');
        $otp2 = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$sessionKey2}")['otp'];

        // Verify Request 1 (Succeeds)
        $verify1 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp1,
                'withdrawal_session_key' => $sessionKey1,
            ]);
        $verify1->assertStatus(200);

        // Verify Request 2 (Fails with 422 - Insufficient Balance)
        $verify2 = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => $otp2,
                'withdrawal_session_key' => $sessionKey2,
            ]);
        $verify2->assertStatus(422);

        // Ensure balance is 200,000 (1M - 800k = 200k)
        $wallet->refresh();
        $this->assertEquals(200000, $wallet->balance);
    }

    /**
     * TC-WD-019: Concurrent Approval Requests
     */
    public function test_tc_wd_019_concurrent_approval_requests()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // First Approve request
        $response1 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.approve', $withdrawal), [
                'notes' => 'Approve 1',
            ]);
        $response1->assertStatus(200);

        // Second Approve request (Fails because status is no longer pending)
        $response2 = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.approve', $withdrawal), [
                'notes' => 'Approve 2',
            ]);
        $response2->assertStatus(400);
    }

    /**
     * TC-WD-020: Invalid State Transition
     */
    public function test_tc_wd_020_invalid_state_transition()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'completed',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // Approve should fail
        $responseApprove = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.approve', $withdrawal));
        $responseApprove->assertStatus(400);

        // Reject should fail
        $responseReject = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.reject', $withdrawal), [
                'reason' => 'Already completed',
            ]);
        $responseReject->assertStatus(400);
    }

    /**
     * TC-WD-021: Cancel Completed Withdrawal
     */
    public function test_tc_wd_021_cancel_completed_withdrawal()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'completed',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        $response = $this->actingAs($this->publisherUser)
            ->postJson(route('publisher.withdrawal.cancel', $withdrawal));

        $response->assertStatus(422);
    }

    /**
     * TC-WD-022: Approve Rejected Withdrawal
     */
    public function test_tc_wd_022_approve_rejected_withdrawal()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'rejected',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.withdrawals.api.approve', $withdrawal));

        $response->assertStatus(400);
    }

    /**
     * Test các view giao diện web rút tiền
     */
    public function test_withdrawal_web_views()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // 1. Index view
        $response = $this->actingAs($this->publisherUser)
            ->get(route('publisher.withdrawal.index'));
        $response->assertStatus(200);

        // 2. Create view
        $response = $this->actingAs($this->publisherUser)
            ->get(route('publisher.withdrawal.create'));
        $response->assertStatus(200);

        // 3. Show view
        $response = $this->actingAs($this->publisherUser)
            ->get(route('publisher.withdrawal.show', $withdrawal));
        $response->assertStatus(200);

        // 4. Show view (khác publisher -> 403)
        $anotherPublisher = User::create([
            'name' => 'Publisher B',
            'email' => 'pub_b_views@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);
        $response = $this->actingAs($anotherPublisher)
            ->get(route('publisher.withdrawal.show', $withdrawal));
        $response->assertStatus(403);
    }

    /**
     * Test các API endpoint hỗ trợ rút tiền
     */
    public function test_withdrawal_api_endpoints()
    {
        Mail::fake();
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // 1. Lấy danh sách API
        $response = $this->actingAs($this->publisherUser)
            ->getJson(route('publisher.withdrawal.api.list', [
                'status' => 'pending',
                'date_from' => now()->subDay()->format('Y-m-d'),
                'date_to' => now()->addDay()->format('Y-m-d')
            ]));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 2. Chi tiết API
        $response = $this->actingAs($this->publisherUser)
            ->getJson(route('publisher.withdrawal.api.show', $withdrawal));
        $response->assertStatus(200);

        // 3. Chi tiết API (khác publisher -> 403)
        $anotherPublisher = User::create([
            'name' => 'Publisher C',
            'email' => 'pub_c_api@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);
        $response = $this->actingAs($anotherPublisher)
            ->getJson(route('publisher.withdrawal.api.show', $withdrawal));
        $response->assertStatus(403);

        // 4. Lấy thống kê API
        $response = $this->actingAs($this->publisherUser)
            ->getJson(route('publisher.withdrawal.api.stats'));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 5. Tính phí rút tiền API
        $response = $this->actingAs($this->publisherUser)
            ->postJson(route('publisher.withdrawal.api.calculate-fee'), [
                'amount' => 200000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 6. Tính phí rút tiền API (khác owner -> 403)
        $response = $this->actingAs($anotherPublisher)
            ->postJson(route('publisher.withdrawal.api.calculate-fee'), [
                'amount' => 200000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $response->assertStatus(403);

        // 7. Lấy 2FA info API
        $response = $this->actingAs($this->publisherUser)
            ->getJson(route('publisher.withdrawal.2fa.info'));
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // 8. Resend OTP API (tạo request và resend)
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 1000000;
        $wallet->save();

        $responseStore = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $sessionKey = $responseStore->json('withdrawal_session_key');

        $responseResend = $this->actingAs($this->publisherUser)
            ->postJson(route('publisher.withdrawal.otp.resend'), [
                'withdrawal_session_key' => $sessionKey,
            ]);
        $responseResend->assertStatus(200);
        $responseResend->assertJsonPath('success', true);

        // Resend với session key sai
        $responseResendFail = $this->actingAs($this->publisherUser)
            ->postJson(route('publisher.withdrawal.otp.resend'), [
                'withdrawal_session_key' => 'invalid-session-key-1234',
            ]);
        $responseResendFail->assertStatus(400);
    }

    /**
     * Test trực tiếp các Notification class (via, toMail, toBroadcast, toArray) để đạt 100% coverage
     */
    public function test_withdrawal_notifications_coverage()
    {
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        $withdrawal = Withdrawal::create([
            'publisher_id' => $this->publisherUser->id,
            'payment_method_id' => $paymentMethod->id,
            'amount' => 300000,
            'fee' => 0,
            'net_amount' => 300000,
            'status' => 'pending',
            'payment_method_type' => 'bank_transfer',
            'payment_details' => [],
        ]);

        // 1. WithdrawalRequestNotification
        $reqNotification = new \App\Notifications\WithdrawalRequestNotification($withdrawal);
        $this->assertEquals(['database', 'broadcast'], $reqNotification->via($this->adminUser));
        $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $reqNotification->toMail($this->adminUser));
        $this->assertInstanceOf(\Illuminate\Notifications\Messages\BroadcastMessage::class, $reqNotification->toBroadcast($this->adminUser));
        $this->assertArrayHasKey('type', $reqNotification->toArray($this->adminUser));

        // 2. WithdrawalStatusNotification (kiểm tra các status: approved, rejected, completed, cancelled, default)
        $statuses = ['approved', 'rejected', 'completed', 'cancelled', 'unknown'];
        foreach ($statuses as $status) {
            $statusNotification = new \App\Notifications\WithdrawalStatusNotification($withdrawal, $status);
            $this->assertEquals(['database', 'broadcast'], $statusNotification->via($this->publisherUser));
            $this->assertInstanceOf(\Illuminate\Notifications\Messages\MailMessage::class, $statusNotification->toMail($this->publisherUser));
            $this->assertInstanceOf(\Illuminate\Notifications\Messages\BroadcastMessage::class, $statusNotification->toBroadcast($this->publisherUser));
            $this->assertArrayHasKey('type', $statusNotification->toArray($this->publisherUser));
        }
    }

    /**
     * Test các nhánh điều kiện khác của WithdrawalController để nâng coverage lên 100%
     */
    public function test_withdrawal_controller_remaining_branches()
    {
        Mail::fake();

        // 1. Publisher không có phương thức thanh toán -> redirect ở trang create
        $publisherNoPm = User::create([
            'name' => 'Pub No PM',
            'email' => 'pub_nopm@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);
        $responseCreate = $this->actingAs($publisherNoPm)
            ->get(route('publisher.withdrawal.create'));
        $responseCreate->assertRedirect(route('publisher.payment-methods.index'));
        $responseCreate->assertSessionHas('warning', 'Vui lòng thêm phương thức thanh toán trước khi rút tiền');

        // Setup PM và ví cho publisherUser để test tiếp
        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 5000000;
        $wallet->save();

        // 2. Gọi getWithdrawals (API list) không truyền filter để test nhánh query default
        $responseList = $this->actingAs($this->publisherUser)
            ->getJson(route('publisher.withdrawal.api.list'));
        $responseList->assertStatus(200);

        // 3. Test non-AJAX store error (ví dụ: amount quá thấp) -> redirect back with errors
        $responseStoreErr = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 50000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $responseStoreErr->assertSessionHasErrors(['amount']);

        // 4. Test non-AJAX store exception (ví dụ: payment method thuộc user khác)
        $anotherPublisher = User::create([
            'name' => 'Publisher Other',
            'email' => 'pub_other@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);
        $responseStoreException = $this->actingAs($anotherPublisher)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 200000,
                'payment_method_id' => $paymentMethod->id, // payment method của publisherUser
            ]);
        $responseStoreException->assertSessionHasErrors(['error']);

        // 5. Test non-AJAX store initial request thành công -> redirect kèm info message
        $responseStoreOk = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $responseStoreOk->assertRedirect(route('publisher.withdrawal.index'));
        $responseStoreOk->assertSessionHas('info');

        // Lấy session key
        $sessionKey = session()->all();
        $withdrawalSessionKey = null;
        foreach ($sessionKey as $key => $value) {
            if (str_starts_with($key, 'withdrawal_pending_')) {
                $withdrawalSessionKey = $key;
                break;
            }
        }
        $this->assertNotNull($withdrawalSessionKey);
        $otp = Cache::get("withdrawal_otp_session_{$this->publisherUser->id}_{$withdrawalSessionKey}")['otp'];

        // 6. Test non-AJAX OTP verification thành công -> redirect sang index với success message
        $responseVerifyOk = $this->actingAs($this->publisherUser)
            ->post(route('publisher.withdrawal.store'), [
                'otp' => $otp,
                'withdrawal_session_key' => $withdrawalSessionKey,
            ]);
        $responseVerifyOk->assertRedirect(route('publisher.withdrawal.index'));
        $responseVerifyOk->assertSessionHas('success', 'Yêu cầu rút tiền đã được gửi thành công');
    }

    /**
     * Test giới hạn giao dịch rút tiền thành công (Rate Limit) - tối đa 3 lần trong 10 phút
     */
    public function test_withdrawal_successful_rate_limit()
    {
        Mail::fake();
        $wallet = $this->publisherUser->getOrCreateWallet();
        $wallet->balance = 5000000;
        $wallet->save();

        $paymentMethod = PaymentMethod::create([
            'publisher_id' => $this->publisherUser->id,
            'type' => 'bank_transfer',
            'account_name' => 'NGUYEN VAN A',
            'account_number' => '1234567890',
            'bank_name' => 'Vietcombank',
            'bank_code' => 'VCB',
            'is_default' => true,
        ]);

        // Tạo cache rate limit giả lập đã hoàn thành 3 lần rút tiền thành công
        $rateLimitKey = 'successful_withdrawals:' . $this->publisherUser->id;
        Cache::put($rateLimitKey, 3, now()->addMinutes(10));

        // Gửi yêu cầu rút tiền thứ 4 (initial request để sinh OTP - vẫn được vì rate limit check nằm ở phần verify OTP)
        $responseStore = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'amount' => 500000,
                'payment_method_id' => $paymentMethod->id,
            ]);
        $responseStore->assertStatus(200);
        $sessionKey = $responseStore->json('withdrawal_session_key');

        // Tiến hành verify OTP lần thứ 4 -> Bị chặn với mã lỗi 429
        $responseVerify = $this->actingAs($this->publisherUser)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->postJson(route('publisher.withdrawal.store'), [
                'otp' => '123456', // OTP giả, thực ra chưa verify OTP đã bị chặn bởi rate limit check đầu tiên
                'withdrawal_session_key' => $sessionKey,
            ]);

        $responseVerify->assertStatus(429);
        $this->assertFalse($responseVerify->json('success'));
        $this->assertStringContainsString('vượt quá giới hạn', $responseVerify->json('message'));
        
        // Reset cache rate limit
        Cache::forget($rateLimitKey);
    }
}


