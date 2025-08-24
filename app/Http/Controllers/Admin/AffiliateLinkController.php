<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\User;
use App\Traits\AffiliateLinkTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class AffiliateLinkController extends Controller
{
    use AffiliateLinkTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AffiliateLink::with(['publisher', 'product', 'campaign', 'clicks', 'conversions']);

        // Filter by publisher
        if ($request->filled('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by campaign
        if ($request->filled('campaign')) {
            $query->where('campaign_id', $request->campaign);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by tracking code
        if ($request->filled('search')) {
            $query->where('tracking_code', 'like', "%{$request->search}%");
        }

        $affiliateLinks = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics - Optimized with single query
        $stats = $this->getAffiliateLinkStats();
        
        // Add revenue calculation for admin
        $totalClicks = $stats['total_clicks'];
        $totalRevenue = 0;
        
        // Revenue from clicks (1 click = 100 VND)
        $totalRevenue += $totalClicks * 100;
        
        // Revenue from conversions (commission)
        $conversionRevenue = AffiliateLink::join('conversions', 'affiliate_links.id', '=', 'conversions.affiliate_link_id')
            ->join('products', 'affiliate_links.product_id', '=', 'products.id')
            ->selectRaw('SUM(products.price * affiliate_links.commission_rate / 100) as total_commission')
            ->value('total_commission') ?? 0;
        
        $totalRevenue += $conversionRevenue;
        $stats['total_revenue'] = $totalRevenue;

        // Get form data for filters
        $formData = $this->getFormData();

        return view('admin.affiliate-links.index', array_merge(compact('affiliateLinks', 'stats'), $formData));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $formData = $this->getFormData();
        
        // Pre-fill data from query parameters
        $prefillData = [];
        if ($request->has('product_id')) {
            $prefillData['selected_product_id'] = $request->get('product_id');
        }
        if ($request->has('campaign_id')) {
            $prefillData['selected_campaign_id'] = $request->get('campaign_id');
        }
        
        return view('admin.affiliate-links.create', array_merge($formData, $prefillData));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'publisher_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'original_url' => 'required|url',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,pending',
        ]);

        // Check if publisher already has an affiliate link for this product
        if ($this->checkExistingLink($request->publisher_id, $request->product_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Publisher này đã có link tiếp thị cho sản phẩm này rồi!');
        }

        // Generate unique tracking code
        $trackingCode = $this->generateTrackingCode(
            User::find($request->publisher_id),
            Product::find($request->product_id)
        );

        // Generate unique short code
        $shortCode = $this->generateShortCode();

        try {
            $affiliateLink = AffiliateLink::create([
                'publisher_id' => $request->publisher_id,
                'product_id' => $request->product_id,
                'campaign_id' => $request->campaign_id,
                'original_url' => $request->original_url,
                'tracking_code' => $trackingCode,
                'short_code' => $shortCode,
                'commission_rate' => $request->commission_rate,
                'status' => $request->status,
            ]);

            Log::info('Affiliate link created successfully', [
                'id' => $affiliateLink->id,
                'tracking_code' => $trackingCode,
                'publisher_id' => $request->publisher_id,
                'product_id' => $request->product_id,
            ]);

            return redirect()->route('admin.affiliate-links.index')
                ->with('success', 'Affiliate link đã được tạo thành công.');
        } catch (Exception $e) {
            Log::error('Failed to create affiliate link', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể tạo affiliate link. Vui lòng thử lại.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AffiliateLink $affiliateLink)
    {
        $affiliateLink->load(['publisher', 'product', 'campaign', 'clicks', 'conversions']);
        
        // Get performance statistics
        $stats = [
            'total_clicks' => $affiliateLink->clicks()->count(),
            'unique_clicks' => $affiliateLink->clicks()->distinct('ip_address')->count(),
            'total_conversions' => $affiliateLink->conversions()->count(),
            'conversion_rate' => $affiliateLink->getConversionRateAttribute(),
            'total_revenue' => $affiliateLink->conversions()->sum('amount'),
            'total_commission' => $affiliateLink->conversions()->sum('commission'),
        ];

        return view('admin.affiliate-links.show', compact('affiliateLink', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AffiliateLink $affiliateLink)
    {
        $formData = $this->getFormData();
        return view('admin.affiliate-links.edit', array_merge($formData, compact('affiliateLink')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AffiliateLink $affiliateLink)
    {
        $request->validate([
            'publisher_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'original_url' => 'required|url',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive,pending',
        ]);

        // Check if publisher already has an affiliate link for this product (excluding current link)
        if ($this->checkExistingLink($request->publisher_id, $request->product_id, $affiliateLink->id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Publisher này đã có link tiếp thị cho sản phẩm này rồi!');
        }

        try {
            $affiliateLink->update([
                'publisher_id' => $request->publisher_id,
                'product_id' => $request->product_id,
                'campaign_id' => $request->campaign_id,
                'original_url' => $request->original_url,
                'commission_rate' => $request->commission_rate,
                'status' => $request->status,
            ]);

            Log::info('Affiliate link updated successfully', [
                'id' => $affiliateLink->id,
                'changes' => $affiliateLink->getChanges(),
            ]);

            return redirect()->route('admin.affiliate-links.index')
                ->with('success', 'Affiliate link đã được cập nhật thành công.');
        } catch (Exception $e) {
            Log::error('Failed to update affiliate link', [
                'id' => $affiliateLink->id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể cập nhật affiliate link. Vui lòng thử lại.');
        }
    }

    /**
     * Toggle affiliate link status
     */
    public function toggleStatus(Request $request, AffiliateLink $affiliateLink)
    {
        $newStatus = $request->input('status');
        
        if (!in_array($newStatus, ['active', 'inactive', 'pending'])) {
            return redirect()->route('admin.affiliate-links.index')
                ->with('error', 'Trạng thái không hợp lệ.');
        }

        $affiliateLink->update(['status' => $newStatus]);

        $statusText = match($newStatus) {
            'active' => 'kích hoạt',
            'inactive' => 'vô hiệu hóa',
            'pending' => 'chờ duyệt',
            default => 'cập nhật'
        };

        return redirect()->route('admin.affiliate-links.index')
            ->with('success', "Affiliate link đã được {$statusText} thành công.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AffiliateLink $affiliateLink)
    {
        // Check if affiliate link has clicks or conversions
        if ($affiliateLink->clicks()->exists() || $affiliateLink->conversions()->exists()) {
            return redirect()->route('admin.affiliate-links.index')
                ->with('error', 'Không thể xóa affiliate link đã có clicks hoặc conversions.');
        }

        $affiliateLink->delete();

        return redirect()->route('admin.affiliate-links.index')
            ->with('success', 'Affiliate link đã được xóa thành công.');
    }



    /**
     * Get form data for create and edit forms
     */
    private function getFormData(): array
    {
        return [
            'publishers' => User::whereIn('role', ['shop', 'publisher'])->get(),
            'products' => Product::where('is_active', true)->get(),
            'campaigns' => Campaign::all(),
        ];
    }


}
