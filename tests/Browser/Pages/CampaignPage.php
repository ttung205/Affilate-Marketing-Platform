<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CampaignPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/admin/campaigns/create';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url());
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@name' => 'input[name="name"]',
            '@status' => 'select[name="status"]',
            '@description' => 'textarea[name="description"]',
            '@start_date' => 'input[name="start_date"]',
            '@end_date' => 'input[name="end_date"]',
            '@budget' => 'input[name="budget"]',
            '@commission_rate' => 'input[name="commission_rate"]',
            '@cost_per_click' => 'input[name="cost_per_click"]',
            '@target_conversions' => 'input[name="target_conversions"]',
            '@submit' => 'button[type="submit"]',
        ];
    }

    /**
     * Create a new campaign using POM.
     */
    public function createCampaign(Browser $browser, string $name, string $status, string $description, string $startDate, string $endDate, int $budget, float $commissionRate, int $costPerClick, int $targetConversions = 0): void
    {
        $browser->type('@name', $name)
                ->select('@status', $status)
                ->type('@description', $description);

        $browser->script([
            "document.getElementById('start_date').value = '{$startDate}'",
            "document.getElementById('end_date').value = '{$endDate}'",
            "document.getElementById('start_date').dispatchEvent(new Event('change'))",
            "document.getElementById('end_date').dispatchEvent(new Event('change'))",
        ]);

        $browser->clear('@budget')
                ->type('@budget', (string)$budget)
                ->clear('@commission_rate')
                ->type('@commission_rate', (string)$commissionRate)
                ->clear('@cost_per_click')
                ->type('@cost_per_click', (string)$costPerClick)
                ->clear('@target_conversions')
                ->type('@target_conversions', (string)$targetConversions)
                ->click('@submit');
    }
}
