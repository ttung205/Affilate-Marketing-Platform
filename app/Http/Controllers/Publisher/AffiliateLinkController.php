<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\Campaign;
use App\Traits\AffiliateLinkTrait;
use App\Services\PublisherRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AffiliateLinkController extends Controller
{
    use AffiliateLinkTrait;

    protected $rankingService;

    public function __construct(PublisherRankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }
    /**
     * Hiển thị danh sách tài nguyên.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->affiliateLinks()
            ->with(['product', 'campaign', 'clicks', 'conversions']);

        // Lọc theo sản phẩm
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Lọc theo campaign
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo tracking code
        if ($request->filled('search')) {
            $query->where('tracking_code', 'like', "%{$request->search}%");
        }

        $affiliateLinks = $query->orderBy('created_at', 'desc')->paginate(15);

        // Lấy thống kê
        $stats = $this->getAffiliateLinkStats(auth()->id());

        // Lấy dữ liệu cho các bộ lọc
        $formData = $this->getFormData();

        return view('publisher.affiliate-links.index', array_merge(compact('affiliateLinks', 'stats'), $formData));
    }

    /**
     * Hiển thị form tạo tài nguyên.
     */
    public function create(Request $request)
    {
        $formData = $this->getFormData();
        
        // Điền dữ liệu từ các tham số truyền vào
        $prefillData = [];
        if ($request->has('campaign_id')) {
            $prefillData['selected_campaign_id'] = $request->get('campaign_id');
        }
        
        return view('publisher.affiliate-links.create', array_merge($formData, $prefillData));
    }

    /**
     * Lưu tài nguyên mới.
     */
    public function store(Request $request)
    {
        $request->validate([
            'original_url' => 'required|url',
            'campaign_id' => 'required|exists:campaigns,id',
        ]);

        // Lấy campaign để lấy tỷ lệ hoa hồng
        $campaign = Campaign::findOrFail($request->campaign_id);

        // Tạo tracking code duy nhất cho người dùng và campaign
        $trackingCode = $this->generateTrackingCode(auth()->user(), $campaign);

        // Tạo short code duy nhất
        $shortCode = $this->generateShortCode();

        try {
            // Tạo tài nguyên affiliate
            $affiliateLink = auth()->user()->affiliateLinks()->create([
                'publisher_id' => auth()->id(),
                'product_id' => null, // No specific product required
                'campaign_id' => $request->campaign_id,
                'original_url' => $request->original_url,
                'tracking_code' => $trackingCode,
                'short_code' => $shortCode,
                'commission_rate' => $campaign->commission_rate ?? 15.00, // Lấy từ campaign hoặc mặc định
                'status' => 'active', // Liên kết của publisher được kích hoạt theo mặc định
            ]);

            Log::info('Publisher affiliate link created successfully', [
                'id' => $affiliateLink->id,
                'tracking_code' => $trackingCode,
                'short_code' => $shortCode,
                'publisher_id' => auth()->id(),
                'campaign_id' => $request->campaign_id,
                'commission_rate' => $campaign->commission_rate,
            ]);

            // Tự động cập nhật xếp hạng sau khi tạo tài nguyên mới
            $this->rankingService->updatePublisherRanking(auth()->user());

            return redirect()->route('publisher.affiliate-links.show', $affiliateLink)
                ->with('success', 'Link tiếp thị đã được tạo thành công. Bạn có thể copy link để sử dụng.');
        } catch (Exception $e) {
            Log::error('Failed to create publisher affiliate link', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể tạo link tiếp thị. Vui lòng thử lại.');
        }
    }

    /**
     * Hiển thị tài nguyên cụ thể.
     */
    public function show(AffiliateLink $affiliateLink)
    {
        // Đảm bảo người dùng chỉ có thể xem liên kết liên kết của riêng họ
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền xem link này.');
        }

        $affiliateLink->load(['publisher', 'product', 'campaign', 'clicks', 'conversions']);
        
        // Lấy thống kê hiệu suất
        $stats = [
            'total_clicks' => $affiliateLink->clicks()->count(),
            'unique_clicks' => $affiliateLink->clicks()->distinct('ip_address')->count(),
            'total_conversions' => $affiliateLink->conversions()->count(),
            'conversion_rate' => $affiliateLink->getConversionRateAttribute(),
            'total_revenue' => $affiliateLink->conversions()->sum('amount'),
            'total_commission' => $affiliateLink->conversions()->sum('commission'),
            'click_commission' => $affiliateLink->click_commission,
            'combined_commission' => $affiliateLink->combined_commission,
        ];

        return view('publisher.affiliate-links.show', compact('affiliateLink', 'stats'));
    }

    /**
     * Hiển thị form chỉnh sửa tài nguyên.
     */
    public function edit(AffiliateLink $affiliateLink)
    {
        // Đảm bảo người dùng chỉ có thể chỉnh sửa liên kết liên kết của riêng họ
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa link này.');
        }

        $formData = $this->getFormData();
        return view('publisher.affiliate-links.edit', array_merge($formData, compact('affiliateLink')));
    }

    /**
     * Cập nhật tài nguyên cụ thể.
     */
    public function update(Request $request, AffiliateLink $affiliateLink)
    {
        // Kiểm tra quyền sở hữu
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền cập nhật link này.');
        }

        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'original_url' => 'required|url',
        ]);

        // Lấy campaign để lấy tỷ lệ hoa hồng
        $campaign = Campaign::findOrFail($request->campaign_id);

        try {
            $affiliateLink->update([
                'campaign_id' => $request->campaign_id,
                'original_url' => $request->original_url,
            ]);

            Log::info('Publisher affiliate link updated successfully', [
                'id' => $affiliateLink->id,
                'changes' => $affiliateLink->getChanges(),
                'new_commission_rate' => $campaign->commission_rate,
            ]);

            return redirect()->route('publisher.affiliate-links.show', $affiliateLink)
                ->with('success', 'Link tiếp thị đã được cập nhật thành công.');
        } catch (Exception $e) {
            Log::error('Failed to update publisher affiliate link', [
                'id' => $affiliateLink->id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể cập nhật link tiếp thị. Vui lòng thử lại.');
        }
    }

    /**
     * Xóa tài nguyên cụ thể.
     */
    public function destroy(AffiliateLink $affiliateLink)
    {
        // Kiểm tra quyền sở hữu
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền xóa link này.');
        }

        // Kiểm tra xem tài nguyên có clicks hoặc conversions không
        if ($affiliateLink->clicks()->exists() || $affiliateLink->conversions()->exists()) {
            return redirect()->route('publisher.affiliate-links.index')
                ->with('error', 'Không thể xóa link tiếp thị đã có clicks hoặc conversions.');
        }

        $affiliateLink->delete();

        return redirect()->route('publisher.affiliate-links.index')
            ->with('success', 'Link tiếp thị đã được xóa thành công.');
    }



    /**
     * Lấy dữ liệu cho form tạo và chỉnh sửa
     */
    private function getFormData(): array
    {
        return [
            'products' => Product::where('is_active', true)->get(),
            'campaigns' => Campaign::whereNotNull('commission_rate')
                ->where('commission_rate', '>', 0)
                ->where('status', 'active')
                ->get(),
        ];
    }


}
