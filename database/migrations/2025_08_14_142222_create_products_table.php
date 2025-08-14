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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('image')->nullable();
            $table->string('category')->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('affiliate_link')->nullable(); // Link affiliate
            $table->decimal('commission_rate', 5, 2)->default(0.00); // Tỷ lệ hoa hồng (%)
            $table->string('affiliate_id')->nullable(); // ID affiliate
            $table->string('affiliate_name')->nullable(); // Tên affiliate
            $table->string('affiliate_email')->nullable(); // Email affiliate
            $table->string('affiliate_phone')->nullable(); // Số điện thoại affiliate
            $table->string('affiliate_address')->nullable(); // Địa chỉ affiliate
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
