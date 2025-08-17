<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Điện tử',
                'description' => 'Các sản phẩm điện tử công nghệ cao',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name' => 'Thời trang',
                'description' => 'Quần áo, giày dép, phụ kiện thời trang',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name' => 'Nhà cửa',
                'description' => 'Đồ dùng gia đình, nội thất',
                'sort_order' => 3,
                'is_active' => true
            ],
            [
                'name' => 'Sức khỏe',
                'description' => 'Thực phẩm chức năng, thiết bị y tế',
                'sort_order' => 4,
                'is_active' => true
            ],
            [
                'name' => 'Sách',
                'description' => 'Sách vở, tài liệu học tập',
                'sort_order' => 5,
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}