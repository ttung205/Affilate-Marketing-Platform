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
        Schema::create('click_fraud_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_link_id')->nullable()->constrained('affiliate_links')->onDelete('set null');
            $table->foreignId('publisher_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->onDelete('set null');
            $table->string('ip_address', 45); // Support IPv6
            $table->text('user_agent')->nullable();
            $table->json('reasons')->nullable(); // Array of fraud reasons
            $table->integer('risk_score')->default(0);
            $table->timestamp('detected_at');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('ip_address');
            $table->index('publisher_id');
            $table->index('detected_at');
            $table->index(['ip_address', 'detected_at']);
            $table->index('risk_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('click_fraud_logs');
    }
};
