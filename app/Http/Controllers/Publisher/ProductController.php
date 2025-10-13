<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\PublisherRankingService;
use App\Traits\AffiliateLinkTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    use AffiliateLinkTrait;

    protected $rankingService;

    public function __construct(PublisherRankingService $rankingService)
    {
        $this->rankingService = $rankingService;
    }
    public function index(Request $request)
    {
        // Get categories for filter dropdown
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        // Build product query with filters
        $query = Product::with(['category', 'shopOwner'])
            ->where('is_active', true);
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Apply price filters
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Apply commission filter
        if ($request->filled('min_commission')) {
            $query->where('commission_rate', '>=', $request->min_commission);
        }
        
        // Get paginated products
        $products = $query->orderBy('created_at', 'desc')->paginate(24);
        
        return view('publisher.products.index', compact('categories', 'products'));
    }

    public function create()
    {
        return view('publisher.products.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement product creation
        return redirect()->route('publisher.products.index');
    }

    public function show($id)
    {
        // Get product with relationships
        $product = Product::with(['category', 'shopOwner'])
            ->where('is_active', true)
            ->findOrFail($id);
        
        // Check if user already has an affiliate link for this product
        $existingLink = auth()->user()->affiliateLinks()
            ->where('product_id', $id)
            ->with(['clicks', 'conversions', 'campaign'])
            ->first();
        
        return view('publisher.products.show', compact('product', 'existingLink'));
    }

    public function edit($id)
    {
        return view('publisher.products.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement product update
        return redirect()->route('publisher.products.index');
    }

    public function destroy($id)
    {
        // TODO: Implement product deletion
        return redirect()->route('publisher.products.index');
    }

    /**
     * Create affiliate link for a product
     */
    public function createAffiliateLink(Request $request, $id)
    {
        try {
            \Log::info('Creating affiliate link', ['product_id' => $id, 'user_id' => auth()->id()]);
            
            // Validate request - no need to validate id as it comes from route parameter
            // $request->validate([
            //     'id' => 'required|exists:products,id'
            // ]);

            // Get product with relationships
            $product = Product::with(['category', 'shopOwner'])
                ->where('is_active', true)
                ->findOrFail($id);
            
            \Log::info('Product found', ['product' => $product->name]);
            
            // Check if user already has an affiliate link for this product
            if ($this->checkExistingLink(auth()->id(), $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã có link tiếp thị cho sản phẩm này rồi!'
                ], 400);
            }
            
            // Generate unique codes
            $trackingCode = $this->generateSimpleTrackingCode(auth()->user(), $product);
            $shortCode = $this->generateShortCode();
            
            \Log::info('Generated codes', ['tracking_code' => $trackingCode, 'short_code' => $shortCode]);
            
            // Create affiliate link
            $affiliateLink = auth()->user()->affiliateLinks()->create([
                'publisher_id' => auth()->id(),
                'product_id' => $id,
                'original_url' => $product->affiliate_link ?? route('publisher.products.show', $id),
                'tracking_code' => $trackingCode,
                'short_code' => $shortCode,
                'status' => 'active',
                'commission_rate' => $product->commission_rate ?? 15.00, // Use product commission rate
            ]);
            
            \Log::info('Affiliate link created', ['affiliate_link_id' => $affiliateLink->id]);
            
            // Tự động cập nhật xếp hạng sau khi tạo link mới
            $this->rankingService->updatePublisherRanking(auth()->user());
            
            // Log success
            \Log::info('Affiliate link created successfully', [
                'id' => $affiliateLink->id,
                'publisher_id' => auth()->id(),
                'product_id' => $id,
                'tracking_code' => $trackingCode
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo link tiếp thị thành công!',
                'affiliate_link' => url('/ref/' . $shortCode),
                'short_code' => $shortCode,
                'tracking_code' => $trackingCode,
                'commission_rate' => $affiliateLink->commission_rate
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::warning('Product not found for affiliate link creation', [
                'product_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã bị vô hiệu hóa.'
            ], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error creating affiliate link', [
                'product_id' => $id,
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
     * Generate simple tracking code for product
     */
    private function generateSimpleTrackingCode($publisher, $product): string
    {
        $publisherCode = 'PUB' . str_pad($publisher->id, 3, '0', STR_PAD_LEFT);
        $productCode = 'PRD' . str_pad($product->id, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        $trackingCode = "{$publisherCode}_{$productCode}_{$timestamp}_{$random}";
        
        // Ensure uniqueness
        while (\App\Models\AffiliateLink::where('tracking_code', $trackingCode)->exists()) {
            $random = strtoupper(Str::random(4));
            $trackingCode = "{$publisherCode}_{$productCode}_{$timestamp}_{$random}";
        }
        
        return $trackingCode;
    }

}