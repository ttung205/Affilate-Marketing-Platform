<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Campaign::with(['affiliateLinks']);

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->filled('date_range')) {
            $dateRange = $request->get('date_range');
            $now = now();
            
            switch ($dateRange) {
                case 'this_week':
                    $query->whereBetween('start_date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereBetween('start_date', [$now->startOfMonth(), $now->endOfMonth()]);
                    break;
                case 'this_quarter':
                    $query->whereBetween('start_date', [$now->startOfQuarter(), $now->endOfQuarter()]);
                    break;
                case 'this_year':
                    $query->whereBetween('start_date', [$now->startOfYear(), $now->endOfYear()]);
                    break;
            }
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(15);

        // Calculate statistics
        $stats = $this->getStatistics();

        return view('admin.campaigns.index', compact('campaigns', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.campaigns.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,paused,completed,draft',
            'budget' => 'nullable|numeric|min:0',
            'target_conversions' => 'nullable|integer|min:0',
        ]);

        try {
            $campaign = Campaign::create($request->all());

            Log::info('Campaign created successfully', [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
            ]);

            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Campaign đã được tạo thành công.');
        } catch (Exception $e) {
            Log::error('Failed to create campaign', [
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể tạo campaign. Vui lòng thử lại.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        // Get products in this campaign with their affiliate links and statistics
        $products = Product::whereHas('affiliateLinks', function($query) use ($campaign) {
            $query->where('campaign_id', $campaign->id);
        })->with(['affiliateLinks' => function($query) use ($campaign) {
            $query->where('campaign_id', $campaign->id);
        }, 'affiliateLinks.publisher', 'affiliateLinks.clicks', 'affiliateLinks.conversions'])->get();
        
        // Calculate statistics for each product
        $products->each(function($product) {
            $product->total_affiliate_links = $product->affiliateLinks->count();
            $product->total_clicks = $product->affiliateLinks->sum(function($link) {
                return $link->clicks->count();
            });
            $product->total_conversions = $product->affiliateLinks->sum(function($link) {
                return $link->conversions->count();
            });
        });
        
        // Get campaign performance
        $stats = [
            'total_products' => $products->count(),
            'total_links' => $products->sum('total_affiliate_links'),
            'total_clicks' => $campaign->getTotalClicksAttribute(),
            'total_conversions' => $campaign->getTotalConversionsAttribute(),
            'total_commission' => $campaign->getTotalCommissionAttribute(),
            'conversion_rate' => $campaign->getTotalClicksAttribute() > 0 
                ? round(($campaign->getTotalConversionsAttribute() / $campaign->getTotalClicksAttribute()) * 100, 2)
                : 0,
        ];

        return view('admin.campaigns.show', compact('campaign', 'stats', 'products'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        return view('admin.campaigns.edit', compact('campaign'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,paused,completed,draft',
            'budget' => 'nullable|numeric|min:0',
            'target_conversions' => 'nullable|integer|min:0',
        ]);

        try {
            $campaign->update($request->all());

            Log::info('Campaign updated successfully', [
                'id' => $campaign->id,
                'changes' => $campaign->getChanges(),
            ]);

            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Campaign đã được cập nhật thành công.');
        } catch (Exception $e) {
            Log::error('Failed to update campaign', [
                'id' => $campaign->id,
                'data' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Không thể cập nhật campaign. Vui lòng thử lại.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        // Check if campaign has affiliate links
        if ($campaign->affiliateLinks()->exists()) {
            return redirect()->route('admin.campaigns.index')
                ->with('error', 'Không thể xóa campaign đã có affiliate links.');
        }

        $campaign->delete();

        return redirect()->route('admin.campaigns.index')
            ->with('success', 'Campaign đã được xóa thành công.');
    }

    /**
     * Toggle campaign status
     */
    public function toggleStatus(Request $request, Campaign $campaign)
    {
        $newStatus = $request->input('status');
        if (!in_array($newStatus, ['active', 'paused', 'completed', 'draft'])) {
            return redirect()->route('admin.campaigns.index')
                ->with('error', 'Trạng thái không hợp lệ.');
        }

        $campaign->update(['status' => $newStatus]);

        $statusText = match($newStatus) {
            'active' => 'kích hoạt',
            'paused' => 'tạm dừng',
            'completed' => 'hoàn thành',
            'draft' => 'chuyển về nháp',
            default => 'cập nhật'
        };

        return redirect()->route('admin.campaigns.index')
            ->with('success', "Campaign đã được {$statusText} thành công.");
    }

    /**
     * Get campaign statistics
     */
    private function getStatistics()
    {
        return [
            'total' => Campaign::count(),
            'active' => Campaign::where('status', 'active')->count(),
            'paused' => Campaign::where('status', 'paused')->count(),
            'completed' => Campaign::where('status', 'completed')->count(),
            'draft' => Campaign::where('status', 'draft')->count(),
            'total_budget' => Campaign::sum('budget'),
            'total_clicks' => Campaign::join('affiliate_links', 'campaigns.id', '=', 'affiliate_links.campaign_id')
                ->join('clicks', 'affiliate_links.id', '=', 'clicks.affiliate_link_id')
                ->count(),
            'total_conversions' => Campaign::join('affiliate_links', 'campaigns.id', '=', 'affiliate_links.campaign_id')
                ->join('conversions', 'affiliate_links.id', '=', 'conversions.affiliate_link_id')
                ->count(),
        ];
    }
}
