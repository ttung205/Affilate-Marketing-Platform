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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('publisher_ranking_id')->nullable()->constrained('publisher_rankings')->onDelete('set null');
            $table->timestamp('ranking_achieved_at')->nullable(); // Thời gian đạt được hạng hiện tại
            $table->timestamp('last_ranking_check_at')->nullable(); // Lần cuối kiểm tra hạng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['publisher_ranking_id']);
            $table->dropColumn(['publisher_ranking_id', 'ranking_achieved_at', 'last_ranking_check_at']);
        });
    }
};
