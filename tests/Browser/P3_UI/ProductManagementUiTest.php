<?php

namespace Tests\Browser\P3_UI;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\ProductPage;

class ProductManagementUiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test luồng Admin tạo sản phẩm (Product) thành công.
     * Xác nhận sản phẩm được tạo thành công với danh mục và hiển thị trong danh sách.
     */
    public function test_admin_create_product_successfully(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Thời trang',
            'slug' => 'thoi-trang',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $category) {
            $browser->loginAs($admin)
                    ->visit(new ProductPage)
                    ->createProduct(
                        'Áo Khoác Bomber Nam',
                        'Áo khoác gió thời trang bomber phong cách trẻ trung',
                        350000, // Giá 350.000 VNĐ (Hợp lệ)
                        50,     // Kho hàng 50 sản phẩm (Hợp lệ)
                        (string)$category->id
                    )
                    ->waitForLocation('/admin/products')
                    ->waitForText('Sản phẩm đã được tạo thành công!');
        });
    }

    /**
     * Test kiểm tra giá trị biên và validation của Sản phẩm (Product).
     * Kiểm tra chi tiết mọi giá trị cận biên của Product theo đặc tả BVA ở mục 2.
     */
    public function test_admin_create_product_validation_rules(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $category = Category::create([
            'name' => 'Gia dụng',
            'slug' => 'gia-dung',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $category) {
            $browser->loginAs($admin);
                    
            // 1. Biên giá: price = -1 (Dưới biên dưới -> Bị từ chối)
            $browser->visit(new ProductPage)
                    ->type('@name', 'Sản phẩm giá -1')
                    ->clear('@price')
                    ->type('@price', '-1')
                    ->clear('@stock')
                    ->type('@stock', '10')
                    ->click('@submit')
                    ->waitForLocation('/admin/products/create');

            // 2. Biên giá: price = 0 (Biên dưới -> Hợp lệ)
            $browser->visit(new ProductPage)
                    ->createProduct('Sản phẩm giá 0', 'Mô tả', 0, 10, (string)$category->id);
            $browser->waitForLocation('/admin/products');

            // 3. Biên tồn kho: stock = -1 (Dưới biên dưới -> Bị từ chối)
            $browser->visit(new ProductPage)
                    ->type('@name', 'Sản phẩm kho -1')
                    ->clear('@price')
                    ->type('@price', '100000')
                    ->clear('@stock')
                    ->type('@stock', '-1')
                    ->click('@submit')
                    ->waitForLocation('/admin/products/create');

            // 4. Biên tồn kho: stock = 0 (Biên dưới -> Hợp lệ)
            $browser->visit(new ProductPage)
                    ->createProduct('Sản phẩm kho 0', 'Mô tả', 100000, 0, (string)$category->id);
            $browser->waitForLocation('/admin/products');
        });
    }
}
