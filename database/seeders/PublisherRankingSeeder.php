<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PublisherRanking;

class PublisherRankingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rankings = [
            [
                'name' => 'Đồng',
                'slug' => 'dong',
                'level' => 1,
                'color' => '#CD7F32',
                'min_links' => 5,
                'min_commission' => 500000,
                'bonus_percentage' => 0,
                'benefits' => 'Hạng cơ bản - Bắt đầu hành trình affiliate marketing',
                'description' => 'Hạng Đồng dành cho những publisher mới bắt đầu với ít nhất 5 link và 500,000 VND hoa hồng.',
            ],
            [
                'name' => 'Bạc',
                'slug' => 'bac',
                'level' => 2,
                'color' => '#C0C0C0',
                'min_links' => 15,
                'min_commission' => 2000000,
                'bonus_percentage' => 5,
                'benefits' => 'Hạng Bạc - Nhận thêm 5% bonus hoa hồng, ưu tiên hỗ trợ',
                'description' => 'Hạng Bạc dành cho publisher có kinh nghiệm với ít nhất 15 link và 2,000,000 VND hoa hồng.',
            ],
            [
                'name' => 'Vàng',
                'slug' => 'vang',
                'level' => 3,
                'color' => '#FFD700',
                'min_links' => 30,
                'min_commission' => 5000000,
                'bonus_percentage' => 10,
                'benefits' => 'Hạng Vàng - Nhận thêm 10% bonus hoa hồng, ưu tiên cao, hỗ trợ 24/7',
                'description' => 'Hạng Vàng dành cho publisher chuyên nghiệp với ít nhất 30 link và 5,000,000 VND hoa hồng.',
            ],
            [
                'name' => 'Kim Cương',
                'slug' => 'kim-cuong',
                'level' => 4,
                'color' => '#B9F2FF',
                'min_links' => 50,
                'min_commission' => 10000000,
                'bonus_percentage' => 15,
                'benefits' => 'Hạng Kim Cương - Nhận thêm 15% bonus hoa hồng, VIP support, ưu đãi đặc biệt',
                'description' => 'Hạng Kim Cương dành cho publisher xuất sắc với ít nhất 50 link và 10,000,000 VND hoa hồng.',
            ],
        ];

        foreach ($rankings as $ranking) {
            PublisherRanking::updateOrCreate(
                ['slug' => $ranking['slug']],
                $ranking
            );
        }
    }
}
