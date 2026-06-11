<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class Notification extends BaseComponent
{
    /**
     * Get the root selector for the component.
     */
    public function selector(): string
    {
        return '.notification-dropdown';
    }

    /**
     * Assert that the browser page contains the component.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertVisible($this->selector());
    }

    /**
     * Get the element shortcuts for the component.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@btn' => '.notification-btn',
            '@badge' => '.notification-badge',
            '@menu' => '#notificationMenu',
            '@list' => '#notificationList',
        ];
    }

    /**
     * Toggle the notification dropdown.
     */
    public function toggle(Browser $browser): void
    {
        $browser->click('@btn')
                ->waitFor('@menu', 5);
    }
}
