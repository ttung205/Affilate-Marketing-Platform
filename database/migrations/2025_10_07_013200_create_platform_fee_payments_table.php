<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_products_value', 15, 2)->default(0); // Tổng giá trị sản phẩm
            $table->decimal('fee_percentage', 5, 2); // % phí áp dụng
            $table->decimal('fee_amount', 15, 2); // Số tiền phí
            $table->enum('status', ['pending', 'paid', 'rejected'])->default('pending');
            $table->text('qr_code')->nullable(); // QR code thanh toán
            $table->timestamp('paid_at')->nullable(); // Thời gian thanh toán
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_fee_payments');
    }
};
