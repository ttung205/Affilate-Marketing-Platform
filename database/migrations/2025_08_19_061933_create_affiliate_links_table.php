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
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');
            $table->string('original_url');
            $table->string('tracking_code')->unique();
            $table->decimal('commission_rate', 5, 2)->default(15.00); // 15.00%
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->timestamps();
            
            // Indexes để tối ưu truy vấn
            $table->index(['publisher_id', 'status']);
            $table->index(['product_id', 'status']);
            $table->index('tracking_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_links');
    }
};
