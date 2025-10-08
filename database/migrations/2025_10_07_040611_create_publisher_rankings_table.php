<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('publisher_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên hạng: Đồng, Bạc, Vàng, Kim Cương
            $table->string('slug'); // slug: dong, bac, vang, kim-cuong
            $table->integer('level'); // Cấp độ: 1, 2, 3, 4
            $table->string('color', 7); // Màu hex: #CD7F32, #C0C0C0, #FFD700, #B9F2FF
            $table->integer('min_links'); // Số link tối thiểu
            $table->decimal('min_commission', 15, 2); // Tổng hoa hồng tối thiểu
            $table->decimal('bonus_percentage', 5, 2)->default(0); // Phần trăm bonus
            $table->text('benefits')->nullable(); // Mô tả ưu đãi
            $table->text('description')->nullable(); // Mô tả hạng
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publisher_rankings');
    }
};
