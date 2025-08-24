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
        Schema::table('affiliate_links', function (Blueprint $table) {
            // Add unique constraint to ensure one publisher can only have one affiliate link per product
            $table->unique(['publisher_id', 'product_id'], 'unique_publisher_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('unique_publisher_product');
        });
    }
};
