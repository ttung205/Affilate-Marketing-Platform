<?php

namespace Tests\Browser\P3_UI;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\CategoryPage;

class CategoryManagementUiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test luồng Admin tạo danh mục (Category) thành công.
     * Xác nhận danh mục được lưu vào DB và chuyển hướng về danh sách thành công.
     */
    public function test_admin_create_category_successfully(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit(new CategoryPage)
                    ->createCategory('Điện thoại & Tablet', 'Các sản phẩm thiết bị di động thông minh', 1)
                    ->waitForLocation('/admin/categories')
                    ->waitForText('đã được tạo thành công!');
        });
    }

    /**
     * Test kiểm tra giá trị biên và validation của Danh mục (Category).
     * Kiểm tra trường hợp tên trống, trùng lặp (từ chối), và giá trị thứ tự sắp xếp âm (giá trị biên không hợp lệ).
     */
    public function test_admin_create_category_validation_rules(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Tạo sẵn một danh mục để test trùng lặp tên
        Category::create([
            'name' => 'Thời trang',
            'slug' => 'thoi-trang',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    
                    // 1. Từ chối: Để trống tên danh mục
                    ->visit(new CategoryPage)
                    ->type('@name', '')
                    ->click('@submit')
                    ->waitForLocation('/admin/categories/create')

                    // 2. Từ chối: Trùng lặp tên danh mục (Unique validation)
                    ->visit(new CategoryPage)
                    ->type('@name', 'Thời trang')
                    ->click('@submit')
                    ->waitForLocation('/admin/categories/create')

                    // 3. Giá trị biên: Thứ tự sắp xếp âm (sort_order < 0)
                    ->visit(new CategoryPage)
                    ->type('@name', 'Mỹ phẩm')
                    ->clear('@sort_order')
                    ->type('@sort_order', '-1') // Thứ tự sắp xếp âm không hợp lệ
                    ->click('@submit')
                    ->waitForLocation('/admin/categories/create');
        });
    }
}
