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
        Schema::create('withdrawal_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_id')->constrained('withdrawals')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['approve', 'reject', 'request_info']);
            $table->text('notes')->nullable();
            $table->json('verification_data')->nullable(); // Dữ liệu xác minh
            $table->timestamps();
            
            // Indexes
            $table->index(['withdrawal_id', 'action']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_approvals');
    }
};
