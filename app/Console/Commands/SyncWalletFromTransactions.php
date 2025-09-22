<?php

namespace App\Console\Commands;

use App\Services\CommissionService;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncWalletFromTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:sync-from-transactions {--publisher-id= : Sync specific publisher}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync wallet balances from transactions for all publishers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $publisherId = $this->option('publisher-id');
        
        if ($publisherId) {
            $this->info("Syncing wallet for publisher ID: {$publisherId}...");
            $this->syncPublisher($publisherId);
        } else {
            $this->info("Syncing wallets for all publishers...");
            
            $publishers = User::whereIn('role', ['publisher', 'shop'])->get();
            $this->info("Found {$publishers->count()} publishers to sync");
            
            $bar = $this->output->createProgressBar($publishers->count());
            $bar->start();
            
            foreach ($publishers as $publisher) {
                $this->syncPublisher($publisher->id);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
        }
        
        $this->info('✅ Wallet sync completed successfully!');
        
        return 0;
    }
    
    private function syncPublisher($publisherId)
    {
        try {
            $publisher = User::find($publisherId);
            if (!$publisher) {
                $this->error("Publisher with ID {$publisherId} not found");
                return;
            }
            
            $commissionService = new CommissionService();
            $commissionService->syncWalletFromTransactions($publisher);
            
            $this->line("Synced publisher: {$publisher->name} (ID: {$publisherId})");
            
        } catch (\Exception $e) {
            $this->error("Error syncing publisher {$publisherId}: " . $e->getMessage());
            Log::error("Wallet sync error for publisher {$publisherId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
