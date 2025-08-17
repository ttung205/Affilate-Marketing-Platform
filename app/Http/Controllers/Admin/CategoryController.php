<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(){
        $categories = Category::withCount('products')->orderBy('sort_order')->orderBy('name')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }
    public function create(){
        return view('admin.categories.create');
    }
    public function store(Request $request){
        $request->validate([
           'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image ? $this->uploadImage($request->image) : null,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.categories.index')
        ->with('success', 'Danh mục "' . $category->name . '" đã được tạo thành công!');
    }
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image ? $this->uploadImage($request->image) : $category->image,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Danh mục đã được cập nhật thành công!');
    }

    public function destroy(Category $category)
    {
        // Kiểm tra xem có sản phẩm nào trong danh mục không
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Không thể xóa danh mục có sản phẩm!');
        }

        // Xóa hình ảnh
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Danh mục đã được xóa thành công!');
    }

    private function uploadImage($image)
    {
        $fileName = time() . '_' . $image->getClientOriginalName();
        $path = $image->storeAs('categories', $fileName, 'public');
        return $path;
    }
}
