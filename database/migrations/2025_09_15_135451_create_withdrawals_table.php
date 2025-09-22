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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2); // Số tiền thực nhận
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled'])->default('pending');
            $table->enum('payment_method_type', ['bank_transfer', 'momo', 'zalopay', 'vnpay', 'phone_card']);
            $table->json('payment_details'); // Thông tin thanh toán (số tài khoản, tên ngân hàng, etc.)
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('transaction_reference')->nullable(); // Mã giao dịch từ ngân hàng
            $table->timestamps();
            
            // Indexes
            $table->index(['publisher_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('processed_by');
            $table->index('transaction_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
