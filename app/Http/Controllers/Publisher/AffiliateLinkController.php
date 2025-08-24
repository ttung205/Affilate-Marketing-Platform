<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\AffiliateLink;
use App\Models\Product;
use App\Models\Campaign;
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
        $query = auth()->user()->affiliateLinks()
            ->with(['product', 'campaign', 'clicks', 'conversions']);

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by campaign
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
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

        // Get statistics
        $stats = $this->getAffiliateLinkStats(auth()->id());

        // Get form data for filters
        $formData = $this->getFormData();

        return view('publisher.affiliate-links.index', array_merge(compact('affiliateLinks', 'stats'), $formData));
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
        
        return view('publisher.affiliate-links.create', array_merge($formData, $prefillData));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'original_url' => 'required|url',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Check if user already has an affiliate link for this product
        if ($this->checkExistingLink(auth()->id(), $request->product_id)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Bạn đã có link tiếp thị cho sản phẩm này rồi!');
        }

        // Get product for default values
        $product = Product::findOrFail($request->product_id);

        // Generate unique tracking code
        $trackingCode = $this->generateTrackingCode(auth()->user(), $product);

        // Generate unique short code
        $shortCode = $this->generateShortCode();

        try {
            $affiliateLink = auth()->user()->affiliateLinks()->create([
                'publisher_id' => auth()->id(),
                'product_id' => $request->product_id,
                'campaign_id' => $request->campaign_id,
                'original_url' => $request->original_url,
                'tracking_code' => $trackingCode,
                'short_code' => $shortCode,
                'commission_rate' => $request->commission_rate,
                'status' => 'active', // Publisher links are active by default
            ]);

            Log::info('Publisher affiliate link created successfully', [
                'id' => $affiliateLink->id,
                'tracking_code' => $trackingCode,
                'publisher_id' => auth()->id(),
                'product_id' => $request->product_id,
            ]);

            return redirect()->route('publisher.affiliate-links.index')
                ->with('success', 'Link tiếp thị đã được tạo thành công.');
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
     * Display the specified resource.
     */
    public function show(AffiliateLink $affiliateLink)
    {
        // Ensure user can only view their own affiliate links
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền xem link này.');
        }

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

        return view('publisher.affiliate-links.show', compact('affiliateLink', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AffiliateLink $affiliateLink)
    {
        // Ensure user can only edit their own affiliate links
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa link này.');
        }

        $formData = $this->getFormData();
        return view('publisher.affiliate-links.edit', array_merge($formData, compact('affiliateLink')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AffiliateLink $affiliateLink)
    {
        // Ensure user can only update their own affiliate links
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền cập nhật link này.');
        }

        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'original_url' => 'required|url',
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $affiliateLink->update([
                'campaign_id' => $request->campaign_id,
                'original_url' => $request->original_url,
                'commission_rate' => $request->commission_rate,
            ]);

            Log::info('Publisher affiliate link updated successfully', [
                'id' => $affiliateLink->id,
                'changes' => $affiliateLink->getChanges(),
            ]);

            return redirect()->route('publisher.affiliate-links.index')
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
     * Remove the specified resource from storage.
     */
    public function destroy(AffiliateLink $affiliateLink)
    {
        // Ensure user can only delete their own affiliate links
        if ($affiliateLink->publisher_id !== auth()->id()) {
            abort(403, 'Bạn không có quyền xóa link này.');
        }

        // Check if affiliate link has clicks or conversions
        if ($affiliateLink->clicks()->exists() || $affiliateLink->conversions()->exists()) {
            return redirect()->route('publisher.affiliate-links.index')
                ->with('error', 'Không thể xóa link tiếp thị đã có clicks hoặc conversions.');
        }

        $affiliateLink->delete();

        return redirect()->route('publisher.affiliate-links.index')
            ->with('success', 'Link tiếp thị đã được xóa thành công.');
    }



    /**
     * Get form data for create and edit forms
     */
    private function getFormData(): array
    {
        return [
            'products' => Product::where('is_active', true)->get(),
            'campaigns' => Campaign::all(),
        ];
    }


}
