<?php

namespace App\Console\Commands;

use App\Services\CommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessHoldPeriod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:process-hold-period {--days=30 : Số ngày hold period}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xử lý hold period - chuyển pending balance sang available balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $holdPeriodDays = $this->option('days');
        
        $this->info("Bắt đầu xử lý hold period ({$holdPeriodDays} ngày)...");
        
        try {
            $commissionService = new CommissionService();
            $commissionService->processHoldPeriod();
            
            $this->info('✅ Hold period đã được xử lý thành công!');
            
        } catch (\Exception $e) {
            $this->error('❌ Lỗi khi xử lý hold period: ' . $e->getMessage());
            Log::error('Hold period processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
        
        return 0;
    }
}
