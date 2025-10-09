<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('users')->cascadeOnDelete(); // shop creator
            $table->foreignId('publisher_id')->nullable()->constrained('users')->nullOnDelete(); // publisher or null => all
            $table->string('code')->unique();
            $table->enum('type', ['percent','fixed','freeship']);
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order', 12, 2)->default(0);
            $table->integer('max_uses')->default(0);
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('expires_at')->nullable();
            $table->boolean('is_global')->default(true); // true = toàn shop, false = áp dụng cho các sản phẩm đính kèm
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
