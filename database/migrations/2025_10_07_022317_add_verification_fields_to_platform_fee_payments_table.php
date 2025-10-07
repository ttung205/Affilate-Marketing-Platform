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
        Schema::table('platform_fee_payments', function (Blueprint $table) {
            $table->text('admin_note')->nullable()->after('note');
            $table->unsignedBigInteger('verified_by')->nullable()->after('admin_note');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('platform_fee_payments')) {
            Schema::table('platform_fee_payments', function (Blueprint $table) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn(['admin_note', 'verified_by', 'verified_at']);
            });
        }
    }
};
