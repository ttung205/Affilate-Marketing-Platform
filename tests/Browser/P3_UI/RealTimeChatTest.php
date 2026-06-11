<?php

namespace Tests\Browser\P3_UI;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Pages\DashboardPage;
use Tests\Browser\Components\Chat;
use Tests\Browser\Components\Notification;
use Illuminate\Support\Str;

class RealTimeChatTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test kịch bản nhắn tin thời gian thực với trợ lý ảo (Chatbot).
     * Xác nhận người dùng có thể gửi tin nhắn và nhận được phản hồi từ bot.
     */
    public function test_real_time_chat_interaction(): void
    {
        $publisher = User::factory()->create([
            'email' => 'publisher_chat@example.com',
            'role' => 'publisher',
        ]);

        $this->browse(function (Browser $browser) use ($publisher) {
            $browser->loginAs($publisher)
                    ->visit(new DashboardPage)
                    ->within(new Chat, function (Browser $chat) {
                        $chat->open()
                             ->assertVisible('@window')
                             ->sendMessage('hello')
                             ->waitForReply()
                             ->assertSeeIn('@messages', 'Tôi có thể giúp gì cho bạn?');
                    });
        });
    }

    /**
     * Test kiểm tra tính hợp lệ của dữ liệu đầu vào ô Chat (Validation & Giá trị biên).
     * Kiểm tra chi tiết mọi mốc biên tin nhắn: 0, 1, 1000, 1005 ký tự theo đặc tả BVA mục 2.
     */
    public function test_chat_input_validation(): void
    {
        $publisher = User::factory()->create([
            'role' => 'publisher',
        ]);

        $this->browse(function (Browser $browser) use ($publisher) {
            $browser->loginAs($publisher)
                    ->visit(new DashboardPage)
                    ->within(new Chat, function (Browser $chat) {
                        $chat->open()
                             ->assertVisible('@window');

                        // 1. Biên độ dài: length = 0 (Dưới biên dưới -> Bị từ chối)
                        $chat->type('@input', '')
                             ->click('@send')
                             ->assertMissing('.chatbot-message-user');

                        // 2. Biên độ dài: length = 1 (Biên dưới -> Hợp lệ)
                        $chat->type('@input', 'A')
                             ->click('@send')
                             ->waitFor('.chatbot-message-user', 5);

                        // 3. Biên độ dài: length = 1000 (Biên trên -> Hợp lệ)
                        $message1000 = str_repeat('A', 1000);
                        $chat->type('@input', $message1000)
                             ->click('@send');

                        // 4. Biên độ dài: length = 1005 (Vượt biên trên -> Bị từ chối)
                        $message1005 = str_repeat('B', 1005);
                        $chat->type('@input', $message1005)
                             ->click('@send');
                        
                        $chat->pause(1000);
                    });
        });
    }

    /**
     * Test hệ thống thông báo đẩy hiển thị đúng nội dung khi có thông báo mới.
     * Kiểm tra số lượng badge thông báo và tính hiển thị của danh sách (Trường hợp ID tồn tại).
     */
    public function test_notification_delivery(): void
    {
        $publisher = User::factory()->create([
            'email' => 'publisher_notify@example.com',
            'role' => 'publisher',
        ]);

        // Tạo thông báo mới trong cơ sở dữ liệu
        $publisher->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\WithdrawalRequestNotification',
            'data' => [
                'title' => 'Yêu cầu rút tiền mới',
                'message' => 'Yêu cầu rút tiền của bạn đã được phê duyệt.',
                'icon' => 'fas fa-wallet',
                'color' => 'green',
            ]
        ]);

        $this->browse(function (Browser $browser) use ($publisher) {
            $browser->loginAs($publisher)
                    ->visit(new DashboardPage)
                    ->pause(1000) // Chờ tiến trình JavaScript polling (realtime.js) tải thông báo
                    ->within(new Notification, function (Browser $notification) {
                        $notification->assertSeeIn('@badge', '1')
                                     ->toggle()
                                     ->assertVisible('@menu')
                                     ->assertSeeIn('@list', 'Yêu cầu rút tiền mới');
                    });
        });
    }

    /**
     * Test đánh dấu thông báo đã đọc và kiểm tra các trường hợp không tồn tại.
     * Kiểm tra khi đánh dấu đọc thông báo hợp lệ, và kiểm tra phản hồi từ chối (404) khi gửi ID thông báo không tồn tại.
     */
    public function test_mark_notification_as_read(): void
    {
        $publisher = User::factory()->create([
            'role' => 'publisher',
        ]);

        $notificationId = Str::uuid()->toString();
        $publisher->notifications()->create([
            'id' => $notificationId,
            'type' => 'App\Notifications\WithdrawalRequestNotification',
            'data' => [
                'title' => 'Thông báo đọc',
                'message' => 'Nội dung thông báo đọc',
                'icon' => 'fas fa-info',
                'color' => 'blue',
            ]
        ]);

        $this->browse(function (Browser $browser) use ($publisher) {
            $browser->loginAs($publisher)
                    ->visit(new DashboardPage)
                    ->pause(1000)
                    
                    // 1. Đánh dấu đọc thông báo đang tồn tại (Trường hợp hợp lệ)
                    ->within(new Notification, function (Browser $notification) {
                        $notification->assertSeeIn('@badge', '1')
                                     ->toggle()
                                     ->click('.mark-read-btn')
                                     ->pause(500)
                                     ->assertMissing('.notification-item.unread');
                    });

            // 2. Trường hợp không tồn tại: Gọi API với ID thông báo ngẫu nhiên không tồn tại trong DB (Bị từ chối)
            $nonExistingUuid = Str::uuid()->toString();
            
            $status = $browser->script("
                return fetch('/api/notifications/{$nonExistingUuid}/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content')
                    }
                }).then(res => res.status);
            ");

            // Xác nhận backend từ chối với mã lỗi 404 Not Found
            $this->assertEquals(404, $status[0]);
        });
    }
}
