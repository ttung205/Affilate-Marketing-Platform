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
}
