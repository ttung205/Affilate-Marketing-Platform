<?php

namespace Tests\Browser\P3_UI;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CrossBrowserExecution extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test kiểm tra tính toàn vẹn của layout trên nhiều độ phân giải màn hình khác nhau (Giả lập đa trình duyệt).
     * Xác nhận layout dashboard không bị vỡ giao diện trên Desktop, Tablet và Mobile.
     */
    public function test_layout_integrity_on_multiple_browsers(): void
    {
        $publisher = User::factory()->create([
            'role' => 'publisher',
        ]);

        $this->browse(function (Browser $browser) use ($publisher) {
            // 1. Kiểm tra trên màn hình lớn Desktop (1920x1080)
            $browser->loginAs($publisher)
                    ->resize(1920, 1080)
                    ->visit('/dashboard')
                    ->assertPresent('.dashboard-container')
                    
                    // 2. Kiểm tra trên màn hình trung bình Tablet (768x1024)
                    ->resize(768, 1024)
                    ->visit('/dashboard')
                    ->assertPresent('.dashboard-container')
                    
                    // 3. Kiểm tra trên màn hình nhỏ di động Mobile (375x812)
                    ->resize(375, 812)
                    ->visit('/dashboard')
                    ->assertPresent('.dashboard-container');
        });
    }
}
