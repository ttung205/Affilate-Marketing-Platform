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
        // Lấy danh sách categories cho bộ lọc
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        // Xây dựng query sản phẩm với các bộ lọc
        $query = Product::with(['category', 'shopOwner'])
            ->where('is_active', true);
        
        // Áp dụng bộ lọc tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Áp dụng bộ lọc danh mục
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Áp dụng bộ lọc giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Áp dụng bộ lọc tỷ lệ hoa hồng
        if ($request->filled('min_commission')) {
            $query->where('commission_rate', '>=', $request->min_commission);
        }
        
        // Lấy danh sách sản phẩm đã phân trang
        $products = $query->orderBy('created_at', 'desc')->paginate(24);
        
        return view('publisher.products.index', compact('categories', 'products'));
    }

    public function create()
    {
        return view('publisher.products.create');
    }

    public function store(Request $request)
    {
        // TODO: Thực hiện tạo sản phẩm
        return redirect()->route('publisher.products.index');
    }

    public function show($id)
    {
        // Lấy sản phẩm với các quan hệ
        $product = Product::with(['category', 'shopOwner'])
            ->where('is_active', true)
            ->findOrFail($id);
        
        // Kiểm tra xem người dùng đã có liên kết affiliate cho sản phẩm này chưa
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
        // TODO: Thực hiện cập nhật sản phẩm
        return redirect()->route('publisher.products.index');
    }

    public function destroy($id)
    {
        // TODO: Thực hiện xóa sản phẩm
        return redirect()->route('publisher.products.index');
    }

    /**
     * Tạo liên kết affiliate cho một sản phẩm
     */
    public function createAffiliateLink(Request $request, $id)
    {
        try {
            \Log::info('Creating affiliate link', ['product_id' => $id, 'user_id' => auth()->id()]);
            
            // Validate request - no need to validate id as it comes from route parameter
            // $request->validate([
            //     'id' => 'required|exists:products,id'
            // ]);

            // Lấy sản phẩm với các quan hệ
            $product = Product::with(['category', 'shopOwner'])
                ->where('is_active', true)
                ->findOrFail($id);
            
            \Log::info('Product found', ['product' => $product->name]);
            
            // Kiểm tra xem người dùng đã có liên kết affiliate cho sản phẩm này chưa
            if ($this->checkExistingLink(auth()->id(), $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã có link tiếp thị cho sản phẩm này rồi!'
                ], 400);
            }
            
            // Tạo tracking code duy nhất
            $trackingCode = $this->generateSimpleTrackingCode(auth()->user(), $product);
            $shortCode = $this->generateShortCode();
            
            \Log::info('Generated codes', ['tracking_code' => $trackingCode, 'short_code' => $shortCode]);
            
            // Tạo liên kết affiliate
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
            
            // Log thành công
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
     * Tạo tracking code đơn giản cho sản phẩm
     */
    private function generateSimpleTrackingCode($publisher, $product): string
    {
        $publisherCode = 'PUB' . str_pad($publisher->id, 3, '0', STR_PAD_LEFT);
        $productCode = 'PRD' . str_pad($product->id, 3, '0', STR_PAD_LEFT);
        $timestamp = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        
        $trackingCode = "{$publisherCode}_{$productCode}_{$timestamp}_{$random}";
        
        // Đảm bảo tính duy nhất
        while (\App\Models\AffiliateLink::where('tracking_code', $trackingCode)->exists()) {
            $random = strtoupper(Str::random(4));
            $trackingCode = "{$publisherCode}_{$productCode}_{$timestamp}_{$random}";
        }
        
        return $trackingCode;
    }

}