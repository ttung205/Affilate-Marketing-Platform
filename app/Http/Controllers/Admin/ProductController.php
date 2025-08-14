<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:100',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'affiliate_link' => 'nullable|url',
            'commission_rate' => 'required|numeric|min:0|max:100'
        ]);

        // Xử lý giá tiền - loại bỏ dấu phẩy, chấm và chuyển thành integer
        $price = (int) str_replace([',', '.', ' '], '', $request->price);
        
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $price,
            'image' => $request->image ? $this->uploadImage($request->image) : null,
            'category' => $request->category,
            'stock' => $request->stock,
            'is_active' => $request->is_active ?? true,
            'affiliate_link' => $request->affiliate_link,
            'commission_rate' => $request->commission_rate
        ]);

        return redirect()->route('admin.products.index')
        ->with('success', '🎉 Sản phẩm "' . $product->name . '" đã được tạo thành công!');
    }

    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:100',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'affiliate_link' => 'nullable|url',
            'commission_rate' => 'required|numeric|min:0|max:100'
        ]);

        // Xử lý giá tiền - loại bỏ dấu phẩy, chấm và chuyển thành integer
        $price = (int) str_replace([',', '.', ' '], '', $request->price);

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $price,
            'image' => $request->image ? $this->uploadImage($request->image) : $product->image,
            'category' => $request->category,
            'stock' => $request->stock,
            'is_active' => $request->is_active ?? true,
            'affiliate_link' => $request->affiliate_link,
            'commission_rate' => $request->commission_rate
        ]);

        return redirect()->route('admin.products.index')
        ->with('success', '✅ Sản phẩm "' . $product->name . '" đã được cập nhật thành công!');
    }

    public function destroy(Product $product)
    {
        // Xóa hình ảnh cũ nếu có
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
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

    public function search(Request $request)
    {
        $query = $request->get('query');
        
        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('category', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.products.index', compact('products', 'query'));
    }

    private function uploadImage($image)
    {
        $fileName = time() . '_' . $image->getClientOriginalName();
        $path = $image->storeAs('products', $fileName, 'public');
        return $path;
    }

    private function deleteImage($imagePath)
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}