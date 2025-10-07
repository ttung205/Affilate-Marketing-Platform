<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PublisherRankingService;

class UpdatePublisherRankings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'publisher:update-rankings {--publisher= : Update specific publisher by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update publisher rankings based on links and commission criteria';

    protected PublisherRankingService $rankingService;

    public function __construct(PublisherRankingService $rankingService)
    {
        parent::__construct();
        $this->rankingService = $rankingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating publisher rankings...');

        if ($publisherId = $this->option('publisher')) {
            $this->updateSpecificPublisher($publisherId);
        } else {
            $this->updateAllPublishers();
        }
    }

    private function updateSpecificPublisher($publisherId)
    {
        $publisher = \App\Models\User::find($publisherId);

        if (!$publisher) {
            $this->error("Publisher with ID {$publisherId} not found.");
            return;
        }

        if (!$publisher->isPublisher()) {
            $this->error("User {$publisherId} is not a publisher.");
            return;
        }

        $this->info("Updating ranking for publisher: {$publisher->name}");

        $oldRanking = $publisher->publisherRanking;
        $newRanking = $this->rankingService->updatePublisherRanking($publisher);

        if ($newRanking) {
            $oldRankingName = $oldRanking ? $oldRanking->name : 'None';
            $this->info("✅ Ranking updated: {$oldRankingName} → {$newRanking->name}");
        } else {
            $this->warn("⚠️  No ranking achieved yet.");
        }
    }

    private function updateAllPublishers()
    {
        $results = $this->rankingService->updateAllPublisherRankings();

        $this->info("📊 Ranking update completed:");
        $this->line("   • Total checked: {$results['total_checked']}");
        $this->line("   • Upgraded: {$results['upgraded']}");
        $this->line("   • Errors: {$results['errors']}");

        if ($results['upgraded'] > 0) {
            $this->info("🎉 {$results['upgraded']} publishers upgraded their ranking!");
        }
    }
}
