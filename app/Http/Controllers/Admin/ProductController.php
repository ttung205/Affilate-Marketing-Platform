<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Services\ImageService;

class ProductController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

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

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:categories,id', // Thêm validation cho category_id
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
            'category_id' => $request->category_id, // Thêm category_id
            'stock' => $request->stock,
            'is_active' => $request->is_active ?? true,
            'affiliate_link' => $request->affiliate_link,
            'commission_rate' => $request->commission_rate
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:categories,id', // Thêm validation cho category_id
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

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    public function destroy(Product $product)
    {
        // Delete image using ImageService
        if ($product->image) {
            $this->imageService->deleteImage($product->image);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
        ->with('success', '🗑️ Sản phẩm "' . $product->name . '" đã được xóa thành công!');
    }

    public function toggleStatus(Product $product)
    {
        $product->update([
            'is_active' => !$product->is_active
        ]);

        $status = $product->is_active ? '✅ kích hoạt' : '⏸️ vô hiệu hóa';
    return redirect()->route('admin.products.index')
        ->with('success', "Sản phẩm '" . $product->name . "' đã được {$status}!");
    }
}