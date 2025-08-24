<?php

namespace App\Traits;

use App\Models\AffiliateLink;
use Illuminate\Support\Str;

trait AffiliateLinkTrait
{
    /**
     * Generate unique tracking code
     */
    protected function generateTrackingCode($publisher, $product): string
    {
        // Use simple ASCII characters to avoid encoding issues
        $publisherCode = 'PUB' . str_pad($publisher->id, 3, '0', STR_PAD_LEFT);
        $productCode = 'PROD' . str_pad($product->id, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        $trackingCode = "{$publisherCode}_{$productCode}_{$timestamp}_{$random}";
        
        // Ensure uniqueness
        while (AffiliateLink::where('tracking_code', $trackingCode)->exists()) {
            $random = strtoupper(Str::random(4));
            $trackingCode = "{$publisherCode}_{$productCode}_{$timestamp}_{$random}";
        }
        
        return $trackingCode;
    }

    /**
     * Generate unique short code
     */
    protected function generateShortCode(): string
    {
        $shortCode = strtoupper(Str::random(6));
        
        // Ensure uniqueness
        while (AffiliateLink::where('short_code', $shortCode)->exists()) {
            $shortCode = strtoupper(Str::random(6));
        }
        
        return $shortCode;
    }

    /**
     * Check if publisher already has affiliate link for product
     */
    protected function checkExistingLink($publisherId, $productId, $excludeId = null): bool
    {
        try {
            $query = AffiliateLink::where('publisher_id', $publisherId)
                ->where('product_id', $productId);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            
            return $query->exists();
        } catch (\Exception $e) {
            \Log::error('Error checking existing affiliate link', [
                'publisher_id' => $publisherId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get affiliate link statistics
     */
    protected function getAffiliateLinkStats($publisherId = null): array
    {
        // Get status counts first
        $statusQuery = AffiliateLink::query();
        if ($publisherId) {
            $statusQuery->where('publisher_id', $publisherId);
        }
        
        $statusCounts = $statusQuery->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get total clicks
        $clicksQuery = AffiliateLink::query();
        if ($publisherId) {
            $clicksQuery->where('publisher_id', $publisherId);
        }
        
        $totalClicks = $clicksQuery->join('clicks', 'affiliate_links.id', '=', 'clicks.affiliate_link_id')
            ->count();
        
        // Get total conversions
        $conversionsQuery = AffiliateLink::query();
        if ($publisherId) {
            $conversionsQuery->where('publisher_id', $publisherId);
        }
        
        $totalConversions = $conversionsQuery->join('conversions', 'affiliate_links.id', '=', 'conversions.affiliate_link_id')
            ->count();

        // Calculate total commission
        $commissionQuery = AffiliateLink::query();
        if ($publisherId) {
            $commissionQuery->where('publisher_id', $publisherId);
        }
        
        $totalCommission = $commissionQuery->join('conversions', 'affiliate_links.id', '=', 'conversions.affiliate_link_id')
            ->sum('conversions.commission');

        return [
            'total' => array_sum($statusCounts),
            'active' => $statusCounts['active'] ?? 0,
            'inactive' => $statusCounts['inactive'] ?? 0,
            'pending' => $statusCounts['pending'] ?? 0,
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'total_commission' => $totalCommission,
        ];
    }
}
