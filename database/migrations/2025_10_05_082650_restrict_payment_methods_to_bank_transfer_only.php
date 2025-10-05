<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Xóa các payment methods không phải bank_transfer
        DB::table('payment_methods')
            ->whereNotIn('type', ['bank_transfer'])
            ->delete();

        // Cập nhật các withdrawals có payment_method_type không phải bank_transfer
        // Chỉ cập nhật các bản ghi đã hoàn thành hoặc bị hủy (không ảnh hưởng đến dữ liệu đang xử lý)
        DB::table('withdrawals')
            ->whereNotIn('payment_method_type', ['bank_transfer'])
            ->whereIn('status', ['completed', 'rejected', 'cancelled'])
            ->update(['payment_method_type' => 'bank_transfer']);

        // Thay đổi enum cho payment_methods table
        DB::statement("ALTER TABLE payment_methods MODIFY COLUMN type ENUM('bank_transfer') NOT NULL");

        // Thay đổi enum cho withdrawals table
        DB::statement("ALTER TABLE withdrawals MODIFY COLUMN payment_method_type ENUM('bank_transfer') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại enum ban đầu
        DB::statement("ALTER TABLE payment_methods MODIFY COLUMN type ENUM('bank_transfer', 'momo', 'zalopay', 'vnpay', 'phone_card') NOT NULL");
        
        DB::statement("ALTER TABLE withdrawals MODIFY COLUMN payment_method_type ENUM('bank_transfer', 'momo', 'zalopay', 'vnpay', 'phone_card') NOT NULL");
    }
};
