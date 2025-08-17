<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Lấy danh sách category IDs
        $categoryIds = Category::pluck('id')->toArray();
        
        if (empty($categoryIds)) {
            // Nếu chưa có categories, tạo mới
            $this->call(CategorySeeder::class);
            $categoryIds = Category::pluck('id')->toArray();
        }
        
        for ($i = 1; $i <= 30; $i++) {
            Product::create([
                'name' => 'Sản phẩm ' . $i,
                'description' => 'Mô tả chi tiết cho sản phẩm ' . $i,
                'price' => rand(100000, 5000000),
                'image' => 'product-' . $i . '.jpg',
                'category_id' => $categoryIds[array_rand($categoryIds)], // Thay đổi từ 'category'
                'stock' => rand(10, 100),
                'is_active' => true,
                'affiliate_link' => 'https://example.com/product-' . $i,
                'commission_rate' => rand(5, 25),
            ]);
        }
    }
}