<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AffiliateLink;
use Illuminate\Support\Str;

class UpdateAffiliateLinksShortCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $affiliateLinks = AffiliateLink::whereNull('short_code')->get();
        
        foreach ($affiliateLinks as $link) {
            $shortCode = $this->generateUniqueShortCode();
            $link->update(['short_code' => $shortCode]);
            
            $this->command->info("Updated affiliate link ID {$link->id} with short_code: {$shortCode}");
        }
        
        $this->command->info("Updated {$affiliateLinks->count()} affiliate links with short codes.");
    }
    
    /**
     * Generate unique short code
     */
    private function generateUniqueShortCode(): string
    {
        do {
            $shortCode = strtoupper(Str::random(6));
        } while (AffiliateLink::where('short_code', $shortCode)->exists());
        
        return $shortCode;
    }
}
