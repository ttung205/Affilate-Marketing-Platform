<?php

namespace Tests\Unit\P1_Fraud;

use Tests\TestCase;
use App\Http\Controllers\Tracking\TrackingController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrackingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * [TC-05] Kiểm tra logic Tracking Controller
     * Implementation thực tế không dùng Cookie mà dùng URL Query Params.
     */
    public function test_cookie_assignment_logic(): void
    {
        $trackingCode = 'TRACK_' . Str::random(8);

        DB::table('users')->insert([
            'id' => 99,
            'name' => 'Pub',
            'email' => 'pub@test.com',
            'password' => bcrypt('123456'),
            'role' => 'publisher',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('campaigns')->insert([
            'id' => 1,
            'name' => 'Test Campaign',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'budget' => 1000000,
            'status' => 'active',
            'commission_rate' => 10,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('affiliate_links')->insert([
            'id' => 1,
            'publisher_id' => 99,
            'product_id' => null,
            'campaign_id' => 1,
            'tracking_code' => $trackingCode,
            'short_code' => Str::random(6),
            'original_url' => 'https://example.com/product',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $request = \Illuminate\Http\Request::create('/track/' . $trackingCode, 'GET');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');
        app()->instance('request', $request);

        $controller = new TrackingController();
        $response = $controller->redirectByTrackingCode($trackingCode);

        $this->assertTrue($response->isRedirect());
        $redirectUrl = $response->getTargetUrl();

        $this->assertStringContainsString('https://example.com/product', $redirectUrl);
        $this->assertStringContainsString('ref=99', $redirectUrl);
        $this->assertStringContainsString('tracking_code=' . $trackingCode, $redirectUrl);
    }
}
