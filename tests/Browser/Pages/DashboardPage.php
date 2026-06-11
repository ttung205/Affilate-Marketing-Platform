<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class DashboardPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/dashboard';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $path = $browser->driver->getCurrentURL();
        $matched = false;
        foreach (['dashboard', 'publisher', 'shop', 'admin'] as $segment) {
            if (str_contains($path, $segment)) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            $browser->assertPathContains('dashboard');
        }
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@notification-btn' => '.notification-btn',
            '@notification-badge' => '.notification-badge',
            '@notification-menu' => '#notificationMenu',
            '@chatbot-toggle' => '#chatbot-toggle',
            '@chatbot-window' => '#chatbot-window',
            '@chatbot-input' => '#chatbot-input',
            '@chatbot-send' => '#chatbot-send',
            '@chatbot-messages' => '#chatbot-messages',
        ];
    }
}
