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
        Schema::table('payment_methods', function (Blueprint $table) {
            // Xóa index trước
            $table->dropIndex(['is_verified']);
            
            // Xóa các cột xác minh
            $table->dropColumn(['is_verified', 'verified_at', 'verification_data']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Thêm lại các cột xác minh
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('verification_data')->nullable();
            
            // Thêm lại index
            $table->index('is_verified');
        });
    }
};
