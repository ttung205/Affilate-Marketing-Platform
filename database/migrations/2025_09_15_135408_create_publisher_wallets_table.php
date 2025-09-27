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
        Schema::create('publisher_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->decimal('balance', 15, 2)->default(0.00); // Số dư có thể rút
            $table->decimal('pending_balance', 15, 2)->default(0.00); // Số dư chờ xử lý
            $table->decimal('total_earned', 15, 2)->default(0.00); // Tổng đã kiếm được
            $table->decimal('total_withdrawn', 15, 2)->default(0.00); // Tổng đã rút
            $table->decimal('hold_period_days', 3, 0)->default(30); // Số ngày hold
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_withdrawal_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('publisher_id');
            $table->index('is_active');
            $table->unique('publisher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publisher_wallets');
    }
};
