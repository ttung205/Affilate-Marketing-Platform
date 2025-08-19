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
        Schema::create('conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_link_id')->constrained('affiliate_links')->onDelete('cascade');
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('tracking_code');
            $table->string('order_id');
            $table->decimal('amount', 15, 2);
            $table->decimal('commission', 15, 2);
            $table->timestamp('converted_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['affiliate_link_id', 'converted_at']);
            $table->index(['publisher_id', 'converted_at']);
            $table->index(['product_id', 'converted_at']);
            $table->index('tracking_code');
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversions');
    }
};
