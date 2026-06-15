<?php

namespace Tests\Browser\P3_UI;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Components\Chat;

class ChatbotUiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test chatbot interaction for guests (public).
     */
    public function test_guest_chatbot_interaction(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->within(new Chat, function (Browser $chat) {
                        // Assert toggle button is visible
                        $chat->assertVisible('@toggle')
                             // Open the chatbot window
                             ->open()
                             ->assertVisible('@window')
                             ->pause(1500)
                             // Check subtitle for Guest
                             ->assertSeeIn('#chatbot-role-subtitle', 'Khách')
                             // Check welcome message
                             ->waitForTextIn('@messages', 'affiliate marketing', 10)
                             // Toggle quick actions
                             ->assertVisible('@quick-actions-toggle')
                             ->toggleQuickActions()
                             ->assertVisible('@quick-actions-content')
                             // Click quick action "guest_info"
                             ->clickQuickAction('guest_info')
                             ->waitForTextIn('@messages', 'affiliate marketing', 10);
                    });
        });
    }

    /**
     * Test chatbot interaction for Publishers.
     */
    public function test_publisher_chatbot_quick_actions(): void
    {
        $publisher = User::factory()->create([
            'name' => 'John Publisher',
            'role' => 'publisher',
        ]);

        $this->browse(function (Browser $browser) use ($publisher) {
            $browser->loginAs($publisher)
                    ->visit('/dashboard')
                    ->within(new Chat, function (Browser $chat) {
                        $chat->open()
                             ->assertVisible('@window')
                             ->pause(1500)
                             // Check role subtitle
                             ->assertSeeIn('#chatbot-role-subtitle', 'John Publisher')
                             // Check welcome message
                             ->waitForTextIn('@messages', 'affiliate marketing', 10)
                             // Toggle quick actions
                             ->toggleQuickActions()
                             // Click quick action "publisher_links"
                             ->clickQuickAction('publisher_links')
                             ->waitForTextIn('@messages', 'affiliate links', 10);
                    });
        });
    }

    /**
     * Test chatbot interaction for Shops.
     */
    public function test_shop_chatbot_quick_actions(): void
    {
        $shop = User::factory()->create([
            'name' => 'Alice Shop',
            'role' => 'shop',
        ]);

        $this->browse(function (Browser $browser) use ($shop) {
            $browser->loginAs($shop)
                    ->visit('/dashboard')
                    ->within(new Chat, function (Browser $chat) {
                        $chat->open()
                             ->assertVisible('@window')
                             ->pause(1500)
                             // Check role subtitle
                             ->assertSeeIn('#chatbot-role-subtitle', 'Alice Shop')
                             // Check welcome message
                             ->waitForTextIn('@messages', 'Alice Shop', 10)
                             // Toggle quick actions
                             ->toggleQuickActions()
                             // Click quick action "shop_products"
                             ->clickQuickAction('shop_products')
                             ->waitForTextIn('@messages', 'affiliate marketing', 10);
                    });
        });
    }

    /**
     * Test chatbot interaction for Admins.
     */
    public function test_admin_chatbot_quick_actions(): void
    {
        $admin = User::factory()->create([
            'name' => 'Bob Admin',
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/dashboard')
                    ->within(new Chat, function (Browser $chat) {
                        $chat->open()
                             ->assertVisible('@window')
                             ->pause(1500)
                             // Check role subtitle
                             ->assertSeeIn('#chatbot-role-subtitle', 'Bob Admin')
                             // Check welcome message
                             ->waitForTextIn('@messages', 'Bob Admin', 10)
                             // Toggle quick actions
                             ->toggleQuickActions()
                             // Click quick action "admin_dashboard"
                             ->clickQuickAction('admin_dashboard')
                             ->waitForTextIn('@messages', 'affiliate marketing', 10);
                    });
        });
    }
}
