<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campaign;

class UpdateCampaignsCostPerClickSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cập nhật tất cả campaigns hiện có với cost_per_click mặc định 100 VND
        Campaign::whereNull('cost_per_click')->update([
            'cost_per_click' => 100.00
        ]);

        $this->command->info('Đã cập nhật cost_per_click cho tất cả campaigns.');
    }
}
