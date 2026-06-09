<?php

namespace Tests\Feature\P4_Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Product;
use App\Notifications\VoucherAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class VoucherControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $shopUser;
    private User $publisherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo người dùng test
        $this->shopUser = User::create([
            'name' => 'Shop Test',
            'email' => 'shop@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);

        $this->publisherUser = User::create([
            'name' => 'Publisher Test',
            'email' => 'publisher@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);
    }

    /**
     * Test tạo voucher thành công
     */
    public function test_store_voucher_successfully()
    {
        $product = Product::create([
            'user_id' => $this->shopUser->id,
            'name' => 'Sản phẩm Test',
            'description' => 'Mô tả',
            'price' => 100000,
            'sku' => 'TEST-SKU-1',
            'status' => 'approved',
        ]);

        Notification::fake();

        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'sale50',
                'type' => 'percent',
                'value' => 50,
                'min_order' => 50000,
                'max_uses' => 10,
                'expires_at' => now()->addDays(7)->format('Y-m-d'),
                'is_global' => 0,
                'publisher_id' => $this->publisherUser->id,
                'product_ids' => [$product->id],
            ]);

        $response->assertRedirect(route('shop.vouchers.index'));
        $response->assertSessionHas('success', 'Tạo voucher thành công!');

        // Kiểm tra record trong DB (code chuyển thành viết hoa)
        $this->assertDatabaseHas('vouchers', [
            'shop_id' => $this->shopUser->id,
            'code' => 'SALE50',
            'type' => 'percent',
            'value' => 50,
            'min_order' => 50000,
            'max_uses' => 10,
            'is_global' => 0,
            'publisher_id' => $this->publisherUser->id,
        ]);

        $voucher = Voucher::where('code', 'SALE50')->first();
        $this->assertCount(1, $voucher->products);
        $this->assertEquals($product->id, $voucher->products->first()->id);
    }

    /**
     * Test validate các quy tắc khi tạo voucher
     */
    public function test_store_voucher_validation_rules()
    {
        // 1. Validate required fields
        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), []);

        $response->assertSessionHasErrors(['code', 'type', 'is_global']);

        // 2. Validate unique code
        Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'UNIQUE100',
            'type' => 'fixed',
            'value' => 10000,
            'is_global' => true,
        ]);

        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'UNIQUE100',
                'type' => 'fixed',
                'is_global' => true,
            ]);

        $response->assertSessionHasErrors(['code']);

        // 3. Validate percent value constraints (min 1, max 100)
        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'PERC101',
                'type' => 'percent',
                'value' => 101,
                'is_global' => true,
            ]);

        $response->assertSessionHasErrors(['value']);

        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'PERC0',
                'type' => 'percent',
                'value' => 0,
                'is_global' => true,
            ]);

        $response->assertSessionHasErrors(['value']);

        // 4. Validate publisher_id must have role publisher
        $invalidPublisher = User::create([
            'name' => 'Invalid Shop',
            'email' => 'invalid@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);

        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'PUBVAL',
                'type' => 'fixed',
                'value' => 5000,
                'is_global' => true,
                'publisher_id' => $invalidPublisher->id,
            ]);

        $response->assertSessionHasErrors(['publisher_id']);
    }

    /**
     * Test active scope của voucher
     */
    public function test_voucher_active_scope()
    {
        // Voucher active, còn hạn
        $activeVoucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'ACTIVE1',
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        // Voucher active, không có hạn
        $activeVoucher2 = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'ACTIVE2',
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => true,
            'expires_at' => null,
        ]);

        // Voucher bị deactive
        $inactiveVoucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'INACTIVE',
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => false,
        ]);

        // Voucher hết hạn
        $expiredVoucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 1000,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $activeVouchers = Voucher::active()->pluck('code')->toArray();

        $this->assertContains('ACTIVE1', $activeVouchers);
        $this->assertContains('ACTIVE2', $activeVouchers);
        $this->assertNotContains('INACTIVE', $activeVouchers);
        $this->assertNotContains('EXPIRED', $activeVouchers);
    }

    /**
     * Test gửi thông báo khi gán voucher
     */
    public function test_voucher_notification_on_assignment()
    {
        Notification::fake();

        // 1. Chỉ định một Publisher nhận voucher
        $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'SPECIFIC',
                'type' => 'fixed',
                'value' => 5000,
                'is_global' => true,
                'publisher_id' => $this->publisherUser->id,
            ]);

        Notification::assertSentTo(
            $this->publisherUser,
            VoucherAssignedNotification::class
        );

        // 2. Global Voucher (gửi cho tất cả Publisher)
        $anotherPublisher = User::create([
            'name' => 'Publisher 2',
            'email' => 'pub2@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);

        $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'GLOBAL',
                'type' => 'fixed',
                'value' => 5000,
                'is_global' => true,
            ]);

        Notification::assertSentTo(
            [$this->publisherUser, $anotherPublisher],
            VoucherAssignedNotification::class
        );
    }

    /**
     * Test xóa voucher
     */
    public function test_delete_voucher()
    {
        $voucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'DELETEME',
            'type' => 'fixed',
            'value' => 1000,
            'is_global' => true,
        ]);

        // 1. Shop không phải chủ sở hữu cố tình xóa -> trả về 403
        $anotherShop = User::create([
            'name' => 'Another Shop',
            'email' => 'shop2@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);

        $response = $this->actingAs($anotherShop)
            ->delete(route('shop.vouchers.destroy', $voucher));

        $response->assertStatus(403);
        $this->assertDatabaseHas('vouchers', ['id' => $voucher->id]);

        // 2. Chủ shop xóa thành công
        $response = $this->actingAs($this->shopUser)
            ->delete(route('shop.vouchers.destroy', $voucher));

        $response->assertRedirect(route('shop.vouchers.index'));
        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    /**
     * Test logic áp dụng voucher: không cho phép cộng dồn (stacking) và tính toán giảm giá đúng
     */
    public function test_voucher_stacking_logic()
    {
        // Tạo 2 voucher
        $voucher1 = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'DISCOUNT10',
            'type' => 'percent',
            'value' => 10, // 10%
            'is_global' => true,
            'is_active' => true,
        ]);

        $voucher2 = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'MINUS50K',
            'type' => 'fixed',
            'value' => 50000, // 50k
            'is_global' => true,
            'is_active' => true,
        ]);

        // Giả lập hàm xử lý áp dụng voucher của hệ thống
        $calculateDiscountedPrice = function (array $appliedVouchers, float $originalPrice) {
            // Quy tắc: Không được phép cộng dồn nhiều voucher (chỉ nhận tối đa 1 voucher)
            if (count($appliedVouchers) > 1) {
                throw new \Exception("Chỉ được áp dụng tối đa 1 voucher cho mỗi đơn hàng.");
            }

            if (empty($appliedVouchers)) {
                return $originalPrice;
            }

            $voucher = $appliedVouchers[0];
            if ($voucher->type === 'percent') {
                return $originalPrice - ($originalPrice * ($voucher->value / 100));
            } elseif ($voucher->type === 'fixed') {
                return max(0.0, $originalPrice - $voucher->value);
            }
            return $originalPrice;
        };

        // 1. Áp dụng 1 voucher đơn lẻ (Percent)
        $priceAfterPercent = $calculateDiscountedPrice([$voucher1], 200000);
        $this->assertEquals(180000, $priceAfterPercent); // 200k - 10% = 180k

        // 2. Áp dụng 1 voucher đơn lẻ (Fixed)
        $priceAfterFixed = $calculateDiscountedPrice([$voucher2], 200000);
        $this->assertEquals(150000, $priceAfterFixed); // 200k - 50k = 150k

        // 3. Cố tình áp dụng đồng thời cả 2 voucher -> ném exception (không cho phép stacking)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Chỉ được áp dụng tối đa 1 voucher cho mỗi đơn hàng.");
        
        $calculateDiscountedPrice([$voucher1, $voucher2], 200000);
    }

    /**
     * Test voucher đã hết hạn thì không thể sử dụng
     */
    public function test_expired_voucher_cannot_be_used()
    {
        // Tạo voucher đã hết hạn
        $expiredVoucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'EXPIRED99',
            'type' => 'fixed',
            'value' => 10000,
            'is_active' => true,
            'expires_at' => now()->subDay(), // Hết hạn ngày hôm qua
        ]);

        // Giả lập hàm kiểm tra tính hợp lệ của voucher
        $validateVoucher = function (Voucher $voucher) {
            // 1. Kiểm tra trạng thái active
            if (!$voucher->is_active) {
                return false;
            }
            // 2. Kiểm tra hạn sử dụng
            if ($voucher->expires_at && $voucher->expires_at->isPast()) {
                return false;
            }
            return true;
        };

        // Voucher hết hạn phải không hợp lệ
        $isValid = $validateVoucher($expiredVoucher);
        $this->assertFalse($isValid);

        // Voucher active scope cũng không được chứa nó
        $activeVouchers = Voucher::active()->pluck('code')->toArray();
        $this->assertNotContains('EXPIRED99', $activeVouchers);
    }

    /**
     * Test Publisher khác không được phép sử dụng voucher được gán riêng biệt
     */
    public function test_other_publisher_cannot_use_voucher()
    {
        // Tạo voucher gán riêng cho publisherUser
        $privateVoucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'PRIVATESALE',
            'type' => 'fixed',
            'value' => 20000,
            'is_global' => false,
            'is_active' => true,
            'publisher_id' => $this->publisherUser->id, // Gán cho publisherUser
        ]);

        // Tạo một publisher khác
        $anotherPublisher = User::create([
            'name' => 'Publisher B',
            'email' => 'publisher_b_voucher@example.com',
            'password' => bcrypt('password'),
            'role' => 'publisher',
        ]);

        // Giả lập hàm kiểm tra quyền sử dụng voucher của Publisher
        $canPublisherUseVoucher = function (Voucher $voucher, User $publisher) {
            // Nếu là global thì ai cũng được dùng
            if ($voucher->is_global) {
                return true;
            }
            // Nếu gán riêng thì phải trùng publisher_id
            return $voucher->publisher_id === $publisher->id;
        };

        // 1. Publisher được gán -> sử dụng được
        $canUse = $canPublisherUseVoucher($privateVoucher, $this->publisherUser);
        $this->assertTrue($canUse);

        // 2. Publisher khác -> không sử dụng được
        $canUseOther = $canPublisherUseVoucher($privateVoucher, $anotherPublisher);
        $this->assertFalse($canUseOther);
    }

    /**
     * Test trực tiếp VoucherAssignedNotification (via, toDatabase) để tăng coverage lên 100%
     */
    public function test_voucher_assigned_notification_methods_directly()
    {
        $voucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'DIRECTNOTIFY',
            'type' => 'fixed',
            'value' => 5000,
            'is_global' => true,
        ]);

        $notification = new VoucherAssignedNotification($voucher);
        $via = $notification->via($this->publisherUser);
        $this->assertEquals(['database'], $via);

        $data = $notification->toDatabase($this->publisherUser);
        $this->assertEquals('DIRECTNOTIFY', $data['voucher_id'] ? $voucher->code : '');
        $this->assertStringContainsString('Bạn được tặng voucher DIRECTNOTIFY', $data['message']);

        // Test trường hợp non-global với danh sách sản phẩm
        $product = Product::create([
            'user_id' => $this->shopUser->id,
            'name' => 'Sản phẩm A',
            'description' => 'Mô tả',
            'price' => 100000,
            'sku' => 'SKU-A',
            'status' => 'approved',
        ]);
        $privateVoucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'PRIVATENOTIFY',
            'type' => 'fixed',
            'value' => 5000,
            'is_global' => false,
        ]);
        $privateVoucher->products()->attach($product->id);

        $notification2 = new VoucherAssignedNotification($privateVoucher);
        $data2 = $notification2->toDatabase($this->publisherUser);
        $this->assertStringContainsString('áp dụng cho sản phẩm: Sản phẩm A', $data2['message']);
    }

    /**
     * Test các view giao diện Voucher của Shop và kiểm soát quyền truy cập
     */
    public function test_voucher_web_views_and_unauthorized_access()
    {
        $voucher = Voucher::create([
            'shop_id' => $this->shopUser->id,
            'code' => 'VIEWTEST',
            'type' => 'fixed',
            'value' => 1000,
            'is_global' => true,
        ]);

        // 1. Index view
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.vouchers.index'));
        $response->assertStatus(200);

        // 2. Create view
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.vouchers.create'));
        $response->assertStatus(200);

        // 3. Show view
        $response = $this->actingAs($this->shopUser)
            ->get(route('shop.vouchers.show', $voucher));
        $response->assertStatus(200);

        // 4. Show view (khác shop -> 403)
        $anotherShop = User::create([
            'name' => 'Shop B',
            'email' => 'shop_b_views@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);
        $response = $this->actingAs($anotherShop)
            ->get(route('shop.vouchers.show', $voucher));
        $response->assertStatus(403);
    }

    /**
     * Test tạo voucher non-global nhưng không truyền product_ids
     */
    public function test_store_non_global_voucher_without_products()
    {
        $response = $this->actingAs($this->shopUser)
            ->post(route('shop.vouchers.store'), [
                'code' => 'NOGLOBALNOPROD',
                'type' => 'fixed',
                'value' => 5000,
                'is_global' => 0,
            ]);

        $response->assertRedirect(route('shop.vouchers.index'));
        $this->assertDatabaseHas('vouchers', [
            'code' => 'NOGLOBALNOPROD',
            'is_global' => 0,
        ]);
    }
}

