<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversions', function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('product_id')->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending')->after('shop_id');
            $table->foreignId('status_changed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('status_changed_at')->nullable()->after('status_changed_by');
            $table->text('status_note')->nullable()->after('status_changed_at');
            $table->boolean('is_commission_processed')->default(false)->after('status_note');
            $table->timestamp('commission_processed_at')->nullable()->after('is_commission_processed');

            $table->index(['status', 'status_changed_at'], 'conversions_status_status_changed_at_index');
            $table->index(['is_commission_processed'], 'conversions_is_commission_processed_index');
            $table->index(['shop_id', 'status'], 'conversions_shop_status_index');
        });

        // Populate shop_id for historical conversions based on product owner
        DB::statement(
            'UPDATE conversions c JOIN products p ON p.id = c.product_id ' .
            'SET c.shop_id = p.user_id WHERE c.shop_id IS NULL'
        );

        // Mark existing conversions as approved and processed to prevent double payouts
        DB::statement(
            "UPDATE conversions SET status = 'approved', is_commission_processed = 1, " .
            "commission_processed_at = converted_at, status_changed_at = converted_at " .
            "WHERE status_changed_at IS NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversions', function (Blueprint $table) {
            $table->dropIndex('conversions_status_status_changed_at_index');
            $table->dropIndex('conversions_is_commission_processed_index');
            $table->dropIndex('conversions_shop_status_index');

            $table->dropForeign(['status_changed_by']);
            $table->dropForeign(['shop_id']);
            $table->dropColumn([
                'commission_processed_at',
                'is_commission_processed',
                'status_note',
                'status_changed_at',
                'status_changed_by',
                'status',
                'shop_id'
            ]);
        });
    }
};
