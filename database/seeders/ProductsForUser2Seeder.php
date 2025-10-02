<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductsForUser2Seeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('vi_VN');
        
        // Lấy danh sách category IDs
        $categoryIds = Category::pluck('id')->toArray();
        
        if (empty($categoryIds)) {
            // Nếu chưa có categories, tạo mới
            $this->call(CategorySeeder::class);
            $categoryIds = Category::pluck('id')->toArray();
        }

        // Danh sách tên sản phẩm theo từng danh mục
        $productNames = [
            'Điện tử' => [
                'iPhone 15 Pro Max', 'Samsung Galaxy S24 Ultra', 'MacBook Pro M3', 'iPad Air', 'AirPods Pro',
                'Sony WH-1000XM5', 'Dell XPS 13', 'Apple Watch Series 9', 'Nintendo Switch OLED', 'PlayStation 5'
            ],
            'Thời trang' => [
                'Áo sơ mi nam cao cấp', 'Váy đầm nữ thanh lịch', 'Quần jean skinny', 'Giày sneaker thể thao',
                'Túi xách da thật', 'Đồng hồ thời trang', 'Kính mát UV400', 'Áo khoác bomber', 'Giày cao gót', 'Thắt lưng da'
            ],
            'Nhà cửa' => [
                'Bộ sofa góc L', 'Tủ lạnh Inverter 350L', 'Máy giặt 9kg', 'Điều hòa 2 chiều', 'Bàn ăn gỗ sồi',
                'Giường ngủ 1m8', 'Tủ quần áo 4 cánh', 'Máy lọc nước RO', 'Nồi cơm điện tử', 'Lò vi sóng'
            ],
            'Sức khỏe' => [
                'Vitamin C 1000mg', 'Omega 3 Fish Oil', 'Máy đo huyết áp', 'Máy massage cầm tay', 'Collagen dạng bột',
                'Máy tập thể dục', 'Thảm yoga cao cấp', 'Dầu gội thảo dược', 'Kem chống nắng SPF50', 'Thuốc bổ gan'
            ],
            'Sách' => [
                'Đắc Nhân Tâm', 'Nhà Giả Kim', 'Tư Duy Nhanh Và Chậm', 'Atomic Habits', 'Rich Dad Poor Dad',
                'The 7 Habits', 'Think And Grow Rich', 'Sapiens', 'Homo Deus', 'The Lean Startup'
            ]
        ];

        // Mô tả sản phẩm mẫu
        $descriptions = [
            'Sản phẩm chất lượng cao, được nhập khẩu chính hãng với đầy đủ giấy tờ bảo hành.',
            'Thiết kế hiện đại, tính năng vượt trội, phù hợp với mọi nhu cầu sử dụng.',
            'Chất liệu cao cấp, bền đẹp theo thời gian, được nhiều khách hàng tin tưởng.',
            'Sản phẩm hot trend 2024, được ưa chuộng bởi chất lượng và giá cả hợp lý.',
            'Công nghệ tiên tiến, tiết kiệm năng lượng, thân thiện với môi trường.',
            'Đáp ứng tiêu chuẩn quốc tế, an toàn cho sức khỏe người sử dụng.',
            'Thiết kế sang trọng, tính năng thông minh, dễ sử dụng cho mọi lứa tuổi.',
            'Sản phẩm bestseller, được đánh giá cao bởi chuyên gia và người tiêu dùng.'
        ];

        // Tạo 50 sản phẩm cho user_id = 2
        for ($i = 1; $i <= 50; $i++) {
            // Chọn ngẫu nhiên một category
            $categoryId = $categoryIds[array_rand($categoryIds)];
            $category = Category::find($categoryId);
            
            // Chọn tên sản phẩm phù hợp với danh mục
            $categoryProducts = $productNames[$category->name] ?? ['Sản phẩm đặc biệt'];
            $productName = $categoryProducts[array_rand($categoryProducts)] . ' - Phiên bản ' . $i;
            
            // Tạo giá theo danh mục
            $priceRange = [
                'Điện tử' => [5000000, 50000000],
                'Thời trang' => [200000, 2000000],
                'Nhà cửa' => [1000000, 20000000],
                'Sức khỏe' => [100000, 1500000],
                'Sách' => [50000, 500000]
            ];
            
            $range = $priceRange[$category->name] ?? [100000, 1000000];
            $price = $faker->numberBetween($range[0], $range[1]);
            
            // Tạo commission rate theo danh mục
            $commissionRates = [
                'Điện tử' => [3, 8],
                'Thời trang' => [10, 20],
                'Nhà cửa' => [5, 15],
                'Sức khỏe' => [15, 30],
                'Sách' => [20, 35]
            ];
            
            $commissionRange = $commissionRates[$category->name] ?? [5, 15];
            $commissionRate = $faker->numberBetween($commissionRange[0], $commissionRange[1]);

            Product::create([
                'name' => $productName,
                'description' => $descriptions[array_rand($descriptions)] . ' ' . $faker->sentence(10),
                'price' => $price,
                'image' => 'product-' . $i . '.jpg',
                'category_id' => $categoryId,
                'stock' => $faker->numberBetween(5, 200),
                'is_active' => $faker->boolean(85), // 85% sản phẩm active
                'affiliate_link' => 'https://shop.example.com/product-' . $i,
                'commission_rate' => $commissionRate,
                'user_id' => 2, // Gán cho user_id = 2
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Đã tạo thành công 50 sản phẩm cho user_id = 2');
    }
}
