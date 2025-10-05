<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Illuminate\Support\Collection;
use App\Imports\ProductsImportPreview;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        // Only show products belonging to the current shop
        $query = Product::with('category')
            ->where('user_id', Auth::id());

        // Search by name or description
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status') === 'active' ? true : false;
            $query->where('is_active', $status);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('shop.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('shop.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'affiliate_link' => 'nullable|url',
            'commission_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $request->image ? $this->imageService->uploadProductImage($request->image) : null,
            'category_id' => $request->category_id,
            'stock' => $request->stock,
            'is_active' => $request->is_active ?? true,
            'affiliate_link' => $request->affiliate_link,
            'commission_rate' => $request->commission_rate,
            'user_id' => Auth::id() // Assign to current shop
        ]);

        return redirect()->route('shop.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    public function show(Product $product)
    {
        // Check if the product belongs to the current shop
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập sản phẩm này.');
        }

        return view('shop.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        // Check if the product belongs to the current shop
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền chỉnh sửa sản phẩm này.');
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('shop.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        // Check if the product belongs to the current shop
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền cập nhật sản phẩm này.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'affiliate_link' => 'nullable|url',
            'commission_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'stock' => $request->stock,
            'is_active' => $request->is_active ?? true,
            'affiliate_link' => $request->affiliate_link,
            'commission_rate' => $request->commission_rate
        ];

        // Handle image upload using ImageService
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image) {
                $this->imageService->deleteImage($product->image);
            }
            
            $data['image'] = $this->imageService->uploadProductImage($request->file('image'));
        }

        $product->update($data);

        return redirect()->route('shop.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }
     // Xuất Excel
public function exportExcel()
{
    // Tên file set theo tgian
    $fileName = 'products_' . now()->format('Ymd_His') . '.xlsx';

    return Excel::download(new ProductsExport, $fileName);
}

    // Nhập Excel
public function importExcel(Request $request)
{
    $filePath = $request->input('file_path');

    if (!$filePath || !Storage::exists($filePath)) {
        return redirect()->back()->with('error', 'File import không tồn tại!');
    }

    $file = Storage::path($filePath);

    // Import dữ liệu vào DB
    Excel::import(new ProductsImport, $file);

    // Xóa file tạm
    Storage::delete($filePath);

    return redirect()->route('shop.products.index')
        ->with('success', 'Nhập sản phẩm thành công!');
}


    public function destroy(Product $product)
    {
        // Check if the product belongs to the current shop
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xóa sản phẩm này.');
        }

        // Delete image using ImageService
        if ($product->image) {
            $this->imageService->deleteImage($product->image);
        }

        $product->delete();

        return redirect()->route('shop.products.index')
        ->with('success', '🗑️ Sản phẩm "' . $product->name . '" đã được xóa thành công!');
    }

    public function toggleStatus(Product $product)
    {
        // Check if the product belongs to the current shop
        if ($product->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thay đổi trạng thái sản phẩm này.');
        }

        $product->update([
            'is_active' => !$product->is_active
        ]);

        $status = $product->is_active ? '✅ kích hoạt' : '⏸️ vô hiệu hóa';
        return redirect()->route('shop.products.index')
            ->with('success', "Sản phẩm '" . $product->name . "' đã được {$status}!");
    }

    /**
     * Remove product image and set to default
     */
    public function removeImage(Product $product)
    {
        // Check if the product belongs to the current shop
        if ($product->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thay đổi ảnh sản phẩm này.'
            ], 403);
        }

        try {
            $product->image = $this->imageService->deleteImageAndSetDefault($product->image, 'product');
            $product->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Ảnh sản phẩm đã được xóa và trở về ảnh mặc định',
                'image_url' => get_image_url($product->image)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa ảnh: ' . $e->getMessage()
            ], 500);
        }
    }
public function previewImport(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv'
    ]);

    // 1. Lưu file tạm
    $file = $request->file('file');
    $filePath = $file->store('temp'); // lưu vào storage/app/temp

    // 2. Đọc nội dung file
    $import = new ProductsImportPreview();
    Excel::import($import, $file);
    $rows = $import->getRows();

    // 3. Chuyển sang view preview
    return view('shop.products.preview', [
        'rows' => $rows,
        'filePath' => $filePath
    ]);
}



}