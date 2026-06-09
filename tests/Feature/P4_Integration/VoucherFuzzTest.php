<?php

namespace Tests\Feature\P4_Integration;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class VoucherFuzzTest extends TestCase
{
    use RefreshDatabase;

    private User $shopUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopUser = User::create([
            'name' => 'Shop Test Fuzz',
            'email' => 'shop_fuzz@example.com',
            'password' => bcrypt('password'),
            'role' => 'shop',
        ]);
    }

    /**
     * Advanced Test: Fuzz Testing cho chức năng tạo Voucher
     * Đẩy 200+ bộ dữ liệu ngẫu nhiên, dị dạng, độc hại (SQL Injection, XSS, Overflow)
     * Đảm bảo hệ thống KHÔNG crash (không trả về lỗi 500) mà xử lý từ chối hợp lệ (302/422).
     */
    public function test_voucher_creation_fuzzing()
    {
        // Danh sách các dữ liệu fuzzed độc hại/dị dạng cho code
        $fuzzedCodes = [
            '', // Trống
            Str::random(1000), // Siêu dài (Buffer Overflow test)
            "' OR '1'='1", // SQL Injection
            "'; DROP TABLE vouchers; --", // SQL Injection phá hoại
            "<script>alert('XSS')</script>", // Cross-Site Scripting (XSS)
            "code với dấu cách",
            "MÃ_CÓ_DẤU_🔥", // Ký tự đặc biệt Emoji
            "123", // Số thuần
        ];

        // Danh sách các dữ liệu fuzzed độc hại/dị dạng cho value
        $fuzzedValues = [
            -10, // Số âm
            0, // Bằng 0
            1000000000000000, // Số cực lớn
            "không phải số", // Chuỗi chữ
            12.34567, // Số thập phân dài
            null,
        ];

        // Danh sách các dữ liệu fuzzed độc hại/dị dạng cho min_order
        $fuzzedMinOrders = [
            -1000,
            "abc",
            1.5,
            null,
        ];

        // Kết hợp và chạy ngẫu nhiên 200 lần
        for ($i = 0; $i < 200; $i++) {
            $code = $fuzzedCodes[array_rand($fuzzedCodes)];
            $value = $fuzzedValues[array_rand($fuzzedValues)];
            $minOrder = $fuzzedMinOrders[array_rand($fuzzedMinOrders)];
            $type = ['percent', 'fixed', 'freeship', 'invalid_type'][rand(0, 3)];
            $isGlobal = [true, false, 'not_boolean'][rand(0, 2)];

            // Tránh lỗi trùng lặp khóa (Unique constraint) khi loop sinh ngẫu nhiên trùng code
            if ($code !== '') {
                $code = $code . '_' . $i;
            }

            $response = $this->actingAs($this->shopUser)
                ->post(route('shop.vouchers.store'), [
                    'code' => $code,
                    'type' => $type,
                    'value' => $value,
                    'min_order' => $minOrder,
                    'is_global' => $isGlobal,
                ]);

            // KIỂM TRA QUAN TRỌNG: 
            // Hệ thống có thể báo lỗi Validation (redirect 302 back) hoặc lưu thành công (302 redirect to index)
            // Nhưng TUYỆT ĐỐI không được trả về lỗi hệ thống 500 (Internal Server Error)
            $status = $response->status();
            $this->assertNotEquals(500, $status, "Hệ thống bị crash (lỗi 500) khi nhận dữ liệu fuzzed: code='$code', type='$type', value='$value', min_order='$minOrder', is_global='$isGlobal'");
        }
    }
}
