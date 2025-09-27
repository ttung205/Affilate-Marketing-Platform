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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['commission_earned', 'withdrawal', 'refund', 'bonus', 'penalty', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('description');
            $table->string('reference_type')->nullable(); // conversion_id, withdrawal_id, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable(); // Dữ liệu bổ sung
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['publisher_id', 'type']);
            $table->index(['publisher_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
