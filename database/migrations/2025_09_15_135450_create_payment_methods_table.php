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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['bank_transfer', 'momo', 'zalopay', 'vnpay', 'phone_card']);
            $table->string('account_name'); // Tên chủ tài khoản
            $table->string('account_number'); // Số tài khoản/số điện thoại
            $table->string('bank_name')->nullable(); // Tên ngân hàng (cho bank_transfer)
            $table->string('bank_code')->nullable(); // Mã ngân hàng
            $table->string('branch_name')->nullable(); // Chi nhánh
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_data')->nullable(); // Dữ liệu xác minh
            $table->timestamps();
            
            // Indexes
            $table->index(['publisher_id', 'type']);
            $table->index(['publisher_id', 'is_default']);
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
