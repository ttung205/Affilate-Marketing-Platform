<?php

namespace Tests\Browser\Components;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Component as BaseComponent;

class Chat extends BaseComponent
{
    /**
     * Get the root selector for the component.
     */
    public function selector(): string
    {
        return '#chatbot-widget';
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
            '@toggle' => '#chatbot-toggle',
            '@window' => '#chatbot-window',
            '@input' => '#chatbot-input',
            '@send' => '#chatbot-send',
            '@messages' => '#chatbot-messages',
            '@close' => '#chatbot-close',
            '@quick-actions-toggle' => '#chatbot-quick-actions-toggle',
            '@quick-actions-content' => '#chatbot-quick-actions-content',
            '@quick-action' => '.chatbot-quick-action',
        ];
    }

    /**
     * Open the chatbot widget.
     */
    public function open(Browser $browser): void
    {
        $browser->click('@toggle')
                ->waitFor('@window', 5);
    }

    /**
     * Send a message to the chatbot.
     */
    public function sendMessage(Browser $browser, string $message): void
    {
        $browser->type('@input', $message)
                ->click('@send');
    }

    /**
     * Wait for chatbot response.
     */
    public function waitForReply(Browser $browser): void
    {
        $browser->waitFor('.chatbot-message-bot', 10);
    }

    /**
     * Toggle the quick actions menu.
     */
    public function toggleQuickActions(Browser $browser): void
    {
        $browser->click('@quick-actions-toggle')
                ->pause(500);
    }

    /**
     * Click a specific quick action by its data-action attribute.
     */
    public function clickQuickAction(Browser $browser, string $action): void
    {
        $browser->click(".chatbot-quick-action[data-action=\"{$action}\"]")
                ->pause(1000);
    }
}

