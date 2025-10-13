<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\Click;
use App\Services\PublisherService;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent() ?? '';
            
            // Initialize Fraud Detection Service
            $fraudDetection = new FraudDetectionService();

            // Check if IP is blocked
            if ($fraudDetection->isIpBlocked($ipAddress)) {
                Log::warning('Blocked IP attempted to click', [
                    'ip_address' => $ipAddress,
                    'affiliate_link_id' => $affiliateLink->id,
                ]);
                
                // Still redirect but don't count click/commission
                return;
            }

            // Run fraud detection
            $fraudResult = $fraudDetection->detectFraud($affiliateLink, $ipAddress, $userAgent);

            // If fraud detected, don't record click or pay commission
            if ($fraudResult['is_fraud']) {
                Log::warning('Fraud click detected and blocked', [
                    'affiliate_link_id' => $affiliateLink->id,
                    'publisher_id' => $affiliateLink->publisher_id,
                    'ip_address' => $ipAddress,
                    'risk_score' => $fraudResult['risk_score'],
                    'reasons' => $fraudResult['reason'],
                ]);

                // Auto-block IP if risk score is very high
                if ($fraudResult['risk_score'] >= 100) {
                    $fraudDetection->blockIpAddress($ipAddress, $fraudResult['reason']);
                }

                // Don't process click/commission for fraud attempts
                return;
            }

            DB::beginTransaction();

            // Create click record
            $clickData = [
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'product_id' => $affiliateLink->product_id, // This can be null for self-created links
                'tracking_code' => $affiliateLink->tracking_code,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referrer' => request()->header('referer'),
                'clicked_at' => now(),
            ];

            Log::info('Creating click record', $clickData);
            
            $click = Click::create($clickData);

            // Increment click counter for rate limiting
            $fraudDetection->incrementClickCounter($ipAddress);

            // Xử lý hoa hồng từ click (chỉ khi không phải fraud)
            $publisherService = new PublisherService();
            $publisherService->processClickCommission($click);

            DB::commit();
            Log::info('Click recorded successfully for link: ' . $affiliateLink->id, [
                'risk_score' => $fraudResult['risk_score'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording click: ' . $e->getMessage(), [
                'affiliate_link_id' => $affiliateLink->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
