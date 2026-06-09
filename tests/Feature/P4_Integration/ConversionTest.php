<?php

namespace Tests\Feature\P4_Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\AffiliateLink;
use App\Models\Conversion;
use App\Models\Transaction;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class ConversionTest extends TestCase
{
    use RefreshDatabase;

    private User $shopUser;
    private User $publisherUser;
    private Product $product;
    private AffiliateLink $affiliateLink;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo người dùng test
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

        // Tạo sản phẩm
        $this->product = Product::create([
            'user_id' => $this->shopUser->id,
            'name' => 'Điện thoại iPhone 15',
            'description' => 'iPhone mới nhất',
            'price' => 30000000,
            'sku' => 'IPHONE-15',
            'status' => 'approved',
        ]);

        // Tạo link affiliate
        $this->affiliateLink = AffiliateLink::create([
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'original_url' => 'http://example.com/product/1',
            'tracking_code' => 'TRACK-IPHONE',
            'short_code' => 'sh-iphone',
            'commission_rate' => 5.00, // 5% hoa hồng
            'status' => 'active',
        ]);

        $this->publisherUser->getOrCreateWallet();
    }

    /**
     * Test tạo Conversion thành công qua Webhook API và Phân bổ hoa hồng
     */
    public function test_conversion_creation_webhook_and_attribution()
    {
        // 1. Tạo conversion thành công qua Webhook
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-101',
            'amount' => 20000000, // Đơn hàng 20 triệu
            'commission_rate' => 5.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Conversion đã được tạo thành công',
        ]);

        $this->assertDatabaseHas('conversions', [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-101',
            'amount' => 20000000,
            'commission' => 1000000, // 5% of 20M is 1M
            'status' => 'pending',
            'publisher_id' => $this->publisherUser->id,
            'shop_id' => $this->shopUser->id,
        ]);
    }

    /**
     * Test validate các tham số của Webhook API
     */
    public function test_conversion_webhook_validation_rules()
    {
        // Thiếu các trường bắt buộc
        $response = $this->postJson(route('conversion.create'), []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tracking_code', 'order_id', 'amount']);

        // Sai định dạng số tiền hoặc commission_rate vượt ngưỡng
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-INVALID',
            'amount' => -100, // Số tiền âm
            'commission_rate' => 150, // Tỷ lệ > 100%
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount', 'commission_rate']);
    }

    /**
     * Test kiểm tra tracking code không tồn tại hoặc không hoạt động
     */
    public function test_conversion_webhook_inactive_tracking_code()
    {
        // 1. Mã tracking không tồn tại
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-NOT-EXIST',
            'order_id' => 'ORDER-000',
            'amount' => 1000000,
        ]);
        $response->assertStatus(404);
        $response->assertJsonPath('success', false);

        // 2. Link affiliate bị vô hiệu hóa
        $this->affiliateLink->update(['status' => 'inactive']);

        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-000',
            'amount' => 1000000,
        ]);
        $response->assertStatus(404);
    }

    /**
     * Test Shop duyệt Conversion và hoa hồng được xử lý
     */
    public function test_shop_approve_conversion()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-202',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        // Shop duyệt đơn hàng
        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'approved',
                'status_note' => 'Khách đã nhận hàng và thanh toán',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));

        // Kiểm tra trạng thái và hoa hồng trong DB
        $conversion->refresh();
        $this->assertEquals('approved', $conversion->status);
        $this->assertTrue($conversion->is_commission_processed);

        // Kiểm tra ví publisher được cộng tiền
        $this->publisherUser->getOrCreateWallet()->refresh();
        $this->assertEquals(500000, $this->publisherUser->getOrCreateWallet()->balance);

        // Ghi nhận transaction
        $this->assertDatabaseHas('transactions', [
            'publisher_id' => $this->publisherUser->id,
            'type' => 'commission_earned',
            'amount' => 500000,
            'status' => 'completed',
        ]);
    }

    /**
     * Test Shop từ chối Conversion
     */
    public function test_shop_reject_conversion()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-303',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        // Shop từ chối đơn hàng
        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'rejected',
                'status_note' => 'Khách hàng hoàn trả sản phẩm',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));

        $conversion->refresh();
        $this->assertEquals('rejected', $conversion->status);
        $this->assertFalse($conversion->is_commission_processed);

        // Ví publisher vẫn là 0
        $this->publisherUser->getOrCreateWallet()->refresh();
        $this->assertEquals(0, $this->publisherUser->getOrCreateWallet()->balance);
    }

    /**
     * Test Publisher lấy danh sách và xem thống kê Conversion qua API
     */
    public function test_publisher_conversion_list_and_stats()
    {
        // Tạo conversion approved cho publisher
        Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-APPROVED',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'approved',
            'converted_at' => now(),
        ]);

        // Tạo conversion pending cho publisher
        Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-PENDING',
            'amount' => 5000000,
            'commission' => 250000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        // 1. Lấy danh sách conversions
        $response = $this->actingAs($this->publisherUser)
            ->getJson(route('conversions.list'));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertCount(2, $response->json('data.data'));

        // 2. Lấy thống kê conversions
        $response = $this->actingAs($this->publisherUser)
            ->getJson(route('conversions.stats'));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.total_conversions', 2);
        $response->assertJsonPath('data.total_amount', 15000000); // 10M + 5M
        $response->assertJsonPath('data.total_commission', 750000); // 500k + 250k
    }

    /**
     * Test không thể tạo conversion với order_id trùng lặp
     */
    public function test_duplicate_order_id_cannot_create_conversion()
    {
        // Tạo conversion lần đầu
        $response1 = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-DUP-1',
            'amount' => 1000000,
        ]);
        $response1->assertStatus(200);

        // Thử tạo lại với order_id trùng lặp
        $response2 = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-DUP-1',
            'amount' => 2000000,
        ]);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['order_id']);
    }

    /**
     * Test conversion đã duyệt thì không thể duyệt lại lần thứ hai
     */
    public function test_approved_conversion_cannot_be_approved_twice()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-DOUBLE-APP',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'approved', // Đã duyệt rồi
            'converted_at' => now(),
        ]);

        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'approved',
                'status_note' => 'Cố gắng duyệt lần 2',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));
        $response->assertSessionHas('error', 'Chỉ có thể xử lý các đơn hàng đang chờ duyệt.');
    }

    /**
     * Test Shop khác (không phải chủ sở hữu sản phẩm/conversion) không thể duyệt conversion
     */
    public function test_non_owner_shop_cannot_approve_conversion()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-NON-OWNER',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        $otherShop = User::create([
            'name' => 'Shop B',
            'email' => 'shop_b@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);

        $response = $this->actingAs($otherShop)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'approved',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test chuyển đổi trạng thái conversion không hợp lệ (không phải approved hay rejected)
     */
    public function test_invalid_conversion_state_transition()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-INVALID-STATE',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'pending', // Trạng thái không hợp lệ trong input
            ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * Test gửi thông báo cho publisher khi conversion được duyệt hoặc từ chối
     */
    public function test_conversion_notification_sent()
    {
        Notification::fake();

        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-NOTIFY',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'approved',
                'status_note' => 'Đã duyệt',
            ]);

        Notification::assertSentTo(
            $this->publisherUser,
            \App\Notifications\RealTimeNotification::class,
            function ($notification, $channels, $notifiable) {
                $data = $notification->toDatabase($notifiable);
                return $data['title'] === 'Đơn hàng đã được duyệt' && 
                       str_contains($data['message'], 'ORDER-NOTIFY');
            }
        );
    }

    /**
     * Test phân tích giá trị biên (BVA) cho tỷ lệ hoa hồng (commission_rate) của conversion
     */
    public function test_commission_rate_boundary_values()
    {
        // 1. Dưới biên dưới: commission_rate = -0.01 -> Không hợp lệ
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-BVA-1',
            'amount' => 1000000,
            'commission_rate' => -0.01,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commission_rate']);

        // 2. Biên dưới: commission_rate = 0.00 -> Hợp lệ
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-BVA-2',
            'amount' => 1000000,
            'commission_rate' => 0.00,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('conversions', [
            'order_id' => 'ORDER-BVA-2',
            'commission' => 0.00,
        ]);

        // 3. Trong khoảng: commission_rate = 15.50 -> Hợp lệ
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-BVA-3',
            'amount' => 1000000,
            'commission_rate' => 15.50,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('conversions', [
            'order_id' => 'ORDER-BVA-3',
            'commission' => 155000, // 15.5% of 1,000,000
        ]);

        // 4. Biên trên: commission_rate = 100.00 -> Hợp lệ
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-BVA-4',
            'amount' => 1000000,
            'commission_rate' => 100.00,
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('conversions', [
            'order_id' => 'ORDER-BVA-4',
            'commission' => 1000000, // 100% of 1,000,000
        ]);

        // 5. Vượt biên trên: commission_rate = 100.01 -> Không hợp lệ
        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-BVA-5',
            'amount' => 1000000,
            'commission_rate' => 100.01,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['commission_rate']);
    }

    /**
     * Test trang index của Shop Conversion với đầy đủ các bộ lọc và tìm kiếm
     */
    public function test_shop_conversions_index_filters()
    {
        // 1. Test index không lọc
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.conversions.index'));
        $response->assertStatus(200);

        // 2. Test index lọc theo status, date_from, date_to, search
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.conversions.index', [
                'status' => 'pending',
                'search' => 'iPhone',
                'date_from' => now()->subDay()->format('Y-m-d'),
                'date_to' => now()->addDay()->format('Y-m-d')
            ]));
        $response->assertStatus(200);

        // 3. Test index với search theo email của publisher
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.conversions.index', [
                'search' => $this->publisherUser->email
            ]));
        $response->assertStatus(200);
    }

    /**
     * Test từ chối một conversion pending mà giả lập đã xử lý hoa hồng (để test nhánh lỗi đặc biệt)
     */
    public function test_cannot_reject_commission_processed_pending_conversion()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-REJ-ERR-2',
            'amount' => 10000000,
            'commission' => 500000,
            'status' => 'pending',
            'is_commission_processed' => true,
            'converted_at' => now(),
        ]);

        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'rejected',
                'status_note' => 'Cố gắng từ chối',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));
        $response->assertSessionHas('error', 'Không thể từ chối đơn hàng đã xử lý hoa hồng.');
    }

    /**
     * Test index với bộ lọc status không hợp lệ và trường hợp shop không có conversion
     */
    public function test_shop_conversions_index_invalid_filters_and_empty_summary()
    {
        $anotherShop = User::create([
            'name' => 'Shop Empty',
            'email' => 'shop_empty@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);

        // index với status không hợp lệ
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.conversions.index', [
                'status' => 'invalid_status_value',
            ]));
        $response->assertStatus(200);

        // index với shop không có conversion nào để test summary count = 0
        $responseEmpty = $this->actingAs($anotherShop)
            ->get(route('shop.conversions.index'));
        $responseEmpty->assertStatus(200);
    }

    /**
     * Test updateStatus cho conversion không có publisher
     */
    public function test_conversion_without_publisher_notification()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-NO-PUB',
            'amount' => 1000000,
            'commission' => 50000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        // Ghi đè Route Model Binding cho `{conversion}` để trả về mối quan hệ publisher = null
        \Illuminate\Support\Facades\Route::bind('conversion', function ($value) use ($conversion) {
            $conversion->setRelation('publisher', null);
            return $conversion;
        });

        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'rejected',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));
        $conversion->refresh();
        $this->assertEquals('rejected', $conversion->status);
    }






    /**
     * Test catch exception block khi tạo conversion
     */
    public function test_conversion_create_catch_exception()
    {
        // Tạo một affiliate link mà product_id = null (để kích hoạt lỗi NOT NULL constraint failed ở DB khi chèn vào conversions)
        $linkWithoutProduct = AffiliateLink::create([
            'publisher_id' => $this->publisherUser->id,
            'product_id' => null, // Gây ra lỗi DB
            'original_url' => 'http://example.com/product/null',
            'tracking_code' => 'TRACK-EXC-TRIGGER',
            'short_code' => 'sh-exc',
            'commission_rate' => 5.00,
            'status' => 'active',
        ]);

        $response = $this->postJson(route('conversion.create'), [
            'tracking_code' => 'TRACK-EXC-TRIGGER',
            'order_id' => 'ORDER-EXC-1',
            'amount' => 1000000,
        ]);

        $response->assertStatus(500);
        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Có lỗi xảy ra khi tạo conversion', $response->json('message'));
    }

    /**
     * Test catch block của notifyPublisher khi gửi thông báo lỗi
     */
    public function test_conversion_notify_publisher_catch_exception()
    {
        $conversion = Conversion::create([
            'affiliate_link_id' => $this->affiliateLink->id,
            'publisher_id' => $this->publisherUser->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shopUser->id,
            'tracking_code' => 'TRACK-IPHONE',
            'order_id' => 'ORDER-NOTIFY-EXC',
            'amount' => 1000000,
            'commission' => 50000,
            'status' => 'pending',
            'converted_at' => now(),
        ]);

        // Mock NotificationService để quăng exception khi gọi sendCustomNotification
        $this->mock(\App\Services\NotificationService::class, function ($mock) {
            $mock->shouldReceive('sendCustomNotification')
                ->andThrow(new \Exception('Simulated notification sending failure'));
        });

        // Shop duyệt đơn hàng để kích hoạt notifyPublisher
        $response = $this->actingAs($this->shopUser)
            ->patch(route('shop.conversions.update-status', $conversion), [
                'status' => 'approved',
            ]);

        $response->assertRedirect(route('shop.conversions.index'));
        $conversion->refresh();
        $this->assertEquals('approved', $conversion->status);
    }
}



