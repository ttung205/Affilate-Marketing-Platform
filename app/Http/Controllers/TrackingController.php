<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackingController extends Controller
{
    /**
     * Track affiliate link by tracking code
     */
    public function trackByCode($trackingCode)
    {
        return $this->processTracking($trackingCode, 'tracking_code');
    }

    /**
     * Track affiliate link by short code
     */
    public function trackByShortCode($shortCode)
    {
        return $this->processTracking($shortCode, 'short_code');
    }

    /**
     * Process tracking for both tracking code and short code
     */
    private function processTracking($code, $type)
    {
        try {
            // Find affiliate link
            $affiliateLink = AffiliateLink::where($type, $code)->first();
            
            if (!$affiliateLink) {
                Log::warning("Affiliate link not found", [
                    'code' => $code,
                    'type' => $type,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
                
                abort(404, 'Affiliate link not found');
            }

            // Check if affiliate link is active
            if ($affiliateLink->status !== 'active') {
                Log::warning("Inactive affiliate link accessed", [
                    'affiliate_link_id' => $affiliateLink->id,
                    'status' => $affiliateLink->status,
                    'ip' => request()->ip()
                ]);
                
                abort(404, 'Affiliate link is not active');
            }

            // Record click
            $click = Click::create([
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'product_id' => $affiliateLink->product_id,
                'tracking_code' => $affiliateLink->tracking_code,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
                'clicked_at' => now(),
            ]);

            Log::info("Affiliate link clicked", [
                'click_id' => $click->id,
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'product_id' => $affiliateLink->product_id,
                'ip' => request()->ip(),
                'type' => $type,
                'code' => $code
            ]);

            // Redirect to original URL
            return redirect($affiliateLink->original_url);

        } catch (\Exception $e) {
            Log::error("Error processing affiliate link tracking", [
                'code' => $code,
                'type' => $type,
                'error' => $e->getMessage(),
                'ip' => request()->ip()
            ]);
            
            abort(500, 'Internal server error');
        }
    }
}
