<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\AffiliateLink;
use App\Services\PublisherRankingService;
use App\Traits\AffiliateLinkTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    use AffiliateLinkTrait;

    protected $rankingService;

    public function __construct(PublisherRankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }

    /**
     * Hiển thị danh sách campaign hoạt động
     */
    public function index(Request $request)
    {
        // Xây dựng query campaign với các bộ lọc
        $query = Campaign::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
        
        // Áp dụng bộ lọc tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Áp dụng bộ lọc tỷ lệ hoa hồng
        if ($request->filled('min_commission')) {
            $query->where('commission_rate', '>=', $request->min_commission);
        }
        
        // Áp dụng bộ lọc ngân sách
        if ($request->filled('min_budget')) {
            $query->where('budget', '>=', $request->min_budget);
        }
        
        // Lấy danh sách campaign đã phân trang
        $campaigns = $query->orderBy('created_at', 'desc')->paginate(24);
        
        return view('publisher.campaigns.index', compact('campaigns'));
    }

    /**
     * Hiển thị chiến dịch được chỉ định
     */
    public function show($id)
    {
        $campaign = Campaign::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->findOrFail($id);
        
        // Kiểm tra xem người dùng đã có liên kết affiliate cho campaign này chưa
        $existingLink = auth()->user()->affiliateLinks()
            ->where('campaign_id', $id)
            ->with(['clicks', 'conversions'])
            ->first();
        
        // Lấy thống kê campaign
        $stats = [
            'total_clicks' => $campaign->total_clicks ?? 0,
            'total_conversions' => $campaign->total_conversions ?? 0,
            'conversion_rate' => $campaign->conversion_rate ?? 0,
            'total_commission' => $campaign->total_commission ?? 0,
        ];
        
        return view('publisher.campaigns.show', compact('campaign', 'existingLink', 'stats'));
    }

    /**
     * Tạo liên kết affiliate cho một campaign
     */
    public function createAffiliateLink(Request $request, $id)
    {
        try {
            // Kiểm tra request
            $validated = $request->validate([
                'destination_url' => 'required|url',
            ], [
                'destination_url.required' => 'Vui lòng nhập link gốc (Destination URL)',
                'destination_url.url' => 'Link gốc phải là một URL hợp lệ',
            ]);
            
            \Log::info('Creating affiliate link for campaign', [
                'campaign_id' => $id, 
                'user_id' => auth()->id(),
                'destination_url' => $validated['destination_url']
            ]);
            
            // Lấy campaign
            $campaign = Campaign::where('status', 'active')
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->findOrFail($id);
            
            \Log::info('Campaign found', ['campaign' => $campaign->name]);
            
            // Tạo tracking code duy nhất
            $trackingCode = $this->generateCampaignTrackingCode(auth()->user(), $campaign);
            $shortCode = $this->generateShortCode();
            
            \Log::info('Generated codes', ['tracking_code' => $trackingCode, 'short_code' => $shortCode]);
            
            // Chuẩn bị tên liên kết (tự động)
            $linkName = "{$campaign->name} - " . now()->format('d/m/Y H:i');
            
            // Tạo liên kết affiliate
            $affiliateLink = auth()->user()->affiliateLinks()->create([
                'publisher_id' => auth()->id(),
                'campaign_id' => $id,
                'product_id' => null, // Campaign links don't have specific product
                'name' => $linkName,
                'original_url' => $validated['destination_url'],
                'tracking_code' => $trackingCode,
                'short_code' => $shortCode,
                'status' => 'active',
                'commission_rate' => $campaign->commission_rate ?? 15.00,
            ]);
            
            \Log::info('Affiliate link created', ['affiliate_link_id' => $affiliateLink->id]);
            
            // Tự động cập nhật xếp hạng sau khi tạo liên kết mới
            $this->rankingService->updatePublisherRanking(auth()->user());
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo link rút gọn thành công!',
                'affiliate_link' => url('/ref/' . $shortCode),
                'short_code' => $shortCode,
                'tracking_code' => $affiliateLink->tracking_code,
                'commission_rate' => $affiliateLink->commission_rate,
                'cost_per_click' => $campaign->cost_per_click ?? 100
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first()
            ], 422);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Campaign not found for affiliate link creation', [
                'campaign_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Chiến dịch không tồn tại hoặc đã kết thúc.'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error creating campaign affiliate link', [
                'campaign_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo link tiếp thị. Vui lòng thử lại.'
            ], 500);
        }
    }
    
    /**
     * Tạo tracking code cho campaign
     */
    private function generateCampaignTrackingCode($publisher, $campaign): string
    {
        $publisherCode = 'PUB' . str_pad($publisher->id, 3, '0', STR_PAD_LEFT);
        $campaignCode = 'CMP' . str_pad($campaign->id, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        $trackingCode = "{$publisherCode}_{$campaignCode}_{$timestamp}_{$random}";
        
        // Đảm bảo tính duy nhất
        while (AffiliateLink::where('tracking_code', $trackingCode)->exists()) {
            $random = strtoupper(Str::random(4));
            $trackingCode = "{$publisherCode}_{$campaignCode}_{$timestamp}_{$random}";
        }
        
        return $trackingCode;
    }
}

