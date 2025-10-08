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
        Schema::create('platform_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('fee_percentage', 5, 2)->default(0); // Phí sàn theo %
            $table->text('description')->nullable(); // Mô tả
            $table->timestamp('effective_from')->nullable(); // Có hiệu lực từ ngày
            $table->boolean('is_active')->default(true); // Đang áp dụng
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_fee_settings');
    }
};
