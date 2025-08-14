<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categories = ['Điện tử', 'Thời trang', 'Nhà cửa', 'Sức khỏe', 'Sách'];
        
        for ($i = 1; $i <= 30; $i++) {
            Product::create([
                'name' => 'Sản phẩm ' . $i,
                'description' => 'Mô tả chi tiết cho sản phẩm ' . $i,
                'price' => rand(100000, 5000000),
                'image' => 'product-' . $i . '.jpg',
                'category' => $categories[array_rand($categories)],
                'stock' => rand(10, 100),
                'is_active' => true,
                'affiliate_link' => 'https://example.com/product-' . $i,
                'commission_rate' => rand(5, 25),
            ]);
        }
    }
}