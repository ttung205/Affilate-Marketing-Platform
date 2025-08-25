<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    public function redirectByTrackingCode($trackingCode)
    {
        return $this->processTracking($trackingCode, 'tracking_code');
    }

    public function redirectByShortCode($shortCode)
    {
        return $this->processTracking($shortCode, 'short_code');
    }

    private function processTracking($code, $type)
    {
        try {
            // Find affiliate link by code
            $affiliateLink = AffiliateLink::where($type, $code)
                ->where('status', 'active')
                ->with(['product', 'publisher', 'campaign'])
                ->first();

            if (!$affiliateLink) {
                abort(404, 'Link không tồn tại hoặc đã bị vô hiệu hóa');
            }

            // Record click
            $this->recordClick($affiliateLink);

            // Redirect to original product URL with tracking parameters
            $redirectUrl = $affiliateLink->original_url;
            $separator = str_contains($redirectUrl, '?') ? '&' : '?';
            
            $trackingParams = [
                'ref' => $affiliateLink->publisher_id,
                'utm_source' => 'publisher',
                'utm_medium' => 'affiliate',
                'utm_campaign' => $affiliateLink->product_id ? 'product_' . $affiliateLink->product_id : 'campaign_' . $affiliateLink->campaign_id,
                'tracking_code' => $affiliateLink->tracking_code
            ];

            $redirectUrl .= $separator . http_build_query($trackingParams);

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            \Log::error('Affiliate redirect error: ' . $e->getMessage());
            abort(500, 'Có lỗi xảy ra khi xử lý link');
        }
    }

    private function recordClick($affiliateLink)
    {
        try {
            DB::beginTransaction();

            // Create click record
            $clickData = [
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'product_id' => $affiliateLink->product_id, // This can be null for self-created links
                'tracking_code' => $affiliateLink->tracking_code,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->header('referer'),
                'clicked_at' => now(),
            ];

            \Log::info('Creating click record', $clickData);
            
            Click::create($clickData);

            DB::commit();
            \Log::info('Click recorded successfully for link: ' . $affiliateLink->id);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error recording click: ' . $e->getMessage(), [
                'affiliate_link_id' => $affiliateLink->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
