<?php

namespace Tests\Browser\P3_UI;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\CampaignPage;

class CampaignCreationUiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test luồng tạo chiến dịch thành công bởi Admin.
     * Xác nhận dữ liệu hợp lệ được gửi đi và chiến dịch được tạo thành công.
     */
    public function test_ui_campaign_creation_flow(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin_test@example.com',
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit(new CampaignPage)
                    ->createCampaign(
                        'Chiến dịch Mùa Hè 2026',
                        'active',
                        'Chiến dịch tiếp thị các sản phẩm mùa hè.',
                        now()->format('Y-m-d'),
                        now()->addMonth()->format('Y-m-d'),
                        10000000, // Ngân sách 10 triệu (Hợp lệ)
                        12.5,     // Hoa hồng 12.5% (Hợp lệ)
                        200       // Chi phí mỗi click 200đ
                    )
                    ->waitForLocation('/admin/campaigns')
                    ->waitForText('Campaign đã được tạo thành công.');
        });
    }

    /**
     * Test kiểm tra giá trị biên và các trường hợp bị từ chối (Validation rules) khi tạo Campaign.
     * Kiểm tra chi tiết mọi giá trị cận biên của Campaign theo đặc tả BVA ở mục 2.
     */
    public function test_ui_campaign_validation_rules(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin);
                    
            // 1. Biên ngân sách: budget = -1 (Dưới biên dưới -> Bị từ chối)
            $browser->visit(new CampaignPage)
                    ->type('@name', 'Campaign budget -1')
                    ->select('@status', 'active');
            $browser->script([
                "document.getElementById('start_date').value = '2026-06-11'",
                "document.getElementById('end_date').value = '2026-07-11'",
                "document.getElementById('start_date').dispatchEvent(new Event('change'))",
                "document.getElementById('end_date').dispatchEvent(new Event('change'))",
            ]);
            $browser->clear('@budget')
                    ->type('@budget', '-1')
                    ->clear('@commission_rate')
                    ->type('@commission_rate', '10')
                    ->clear('@cost_per_click')
                    ->type('@cost_per_click', '100')
                    ->click('@submit')
                    ->waitForLocation('/admin/campaigns/create');

            // 2. Biên ngân sách: budget = 0 (Biên dưới -> Hợp lệ)
            $browser->visit(new CampaignPage)
                    ->createCampaign('Campaign budget 0', 'active', 'Mô tả', '2026-06-11', '2026-07-11', 0, 10.00, 100);
            $browser->waitForLocation('/admin/campaigns');

            // 3. Biên ngân sách: budget = 1 (Trong khoảng hợp lệ -> Hợp lệ)
            $browser->visit(new CampaignPage)
                    ->createCampaign('Campaign budget 1', 'active', 'Mô tả', '2026-06-11', '2026-07-11', 1, 10.00, 100);
            $browser->waitForLocation('/admin/campaigns');

            // 4. Biên hoa hồng: commission_rate = -0.01 (Dưới biên dưới -> Bị từ chối)
            $browser->visit(new CampaignPage)
                    ->type('@name', 'Campaign rate -0.01')
                    ->select('@status', 'active');
            $browser->script([
                "document.getElementById('start_date').value = '2026-06-11'",
                "document.getElementById('end_date').value = '2026-07-11'",
                "document.getElementById('start_date').dispatchEvent(new Event('change'))",
                "document.getElementById('end_date').dispatchEvent(new Event('change'))",
            ]);
            $browser->clear('@budget')
                    ->type('@budget', '100000')
                    ->clear('@commission_rate')
                    ->type('@commission_rate', '-0.01')
                    ->clear('@cost_per_click')
                    ->type('@cost_per_click', '100')
                    ->click('@submit')
                    ->waitForLocation('/admin/campaigns/create');

            // 5. Biên hoa hồng: commission_rate = 0.00 (Biên dưới -> Hợp lệ)
            $browser->visit(new CampaignPage)
                    ->createCampaign('Campaign rate 0.00', 'active', 'Mô tả', '2026-06-11', '2026-07-11', 100000, 0.00, 100);
            $browser->waitForLocation('/admin/campaigns');

            // 6. Biên hoa hồng: commission_rate = 100.00 (Biên trên -> Hợp lệ)
            $browser->visit(new CampaignPage)
                    ->createCampaign('Campaign rate 100.00', 'active', 'Mô tả', '2026-06-11', '2026-07-11', 100000, 100.00, 100);
            $browser->waitForLocation('/admin/campaigns');

            // 7. Biên hoa hồng: commission_rate = 100.01 (Vượt biên trên -> Bị từ chối)
            $browser->visit(new CampaignPage)
                    ->type('@name', 'Campaign rate 100.01')
                    ->select('@status', 'active');
            $browser->script([
                "document.getElementById('start_date').value = '2026-06-11'",
                "document.getElementById('end_date').value = '2026-07-11'",
                "document.getElementById('start_date').dispatchEvent(new Event('change'))",
                "document.getElementById('end_date').dispatchEvent(new Event('change'))",
            ]);
            $browser->clear('@budget')
                    ->type('@budget', '100000')
                    ->clear('@commission_rate')
                    ->type('@commission_rate', '100.01')
                    ->clear('@cost_per_click')
                    ->type('@cost_per_click', '100')
                    ->click('@submit')
                    ->waitForLocation('/admin/campaigns/create');
        });
    }

    /**
     * Test tìm kiếm và bộ lọc sản phẩm bởi Publisher.
     * Kiểm tra chi tiết các trường hợp của bộ lọc BVA theo đặc tả ở mục 2.
     */
    public function test_product_search_and_filter(): void
    {
        $publisher = User::factory()->create([
            'email' => 'pub_test@example.com',
            'role' => 'publisher',
        ]);

        $shop = User::factory()->create([
            'role' => 'shop',
        ]);

        $category1 = Category::create(['name' => 'Thời trang Nam']);
        $category2 = Category::create(['name' => 'Điện tử']);

        Product::create([
            'user_id' => $shop->id,
            'category_id' => $category1->id,
            'name' => 'Áo Thun Polo Nam',
            'price' => 250000,
            'status' => 'approved',
            'sku' => 'POLO-001',
        ]);

        $this->browse(function (Browser $browser) use ($publisher, $category1) {
            $browser->loginAs($publisher);
                    
            // 1. Tìm kiếm từ khóa tồn tại (Hiển thị sản phẩm khớp)
            $browser->visit('/publisher/products')
                    ->type('#search', 'Áo Thun')
                    ->press('button[type="submit"]')
                    ->pause(1000)
                    ->assertSee('Áo Thun Polo Nam');

            // 2. Tìm kiếm từ khóa không tồn tại (Hiển thị thông báo trống)
            $browser->visit('/publisher/products')
                    ->type('#search', 'Không có thực')
                    ->press('button[type="submit"]')
                    ->pause(1000)
                    ->assertSee('Không tìm thấy kết quả');

            // 3. Lọc danh mục tồn tại (Hiển thị sản phẩm thuộc danh mục đó)
            $browser->visit('/publisher/products')
                    ->select('#category', (string)$category1->id)
                    ->pause(1000)
                    ->assertSee('Áo Thun Polo Nam');

            // 4. Lọc danh mục không tồn tại / không có sản phẩm (Danh sách trống)
            $browser->visit('/publisher/products?category=999999')
                    ->pause(1000)
                    ->assertSee('Không tìm thấy kết quả');
        });
    }

    /**
     * Test truy cập chiến dịch không tồn tại (Trường hợp không tồn tại).
     */
    public function test_ui_campaign_not_found(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/admin/campaigns/999999/edit') // ID không tồn tại
                    ->assertDontSee('Thông tin Campaign'); 
        });
    }
}
