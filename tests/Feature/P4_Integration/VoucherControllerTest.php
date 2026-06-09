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
}
