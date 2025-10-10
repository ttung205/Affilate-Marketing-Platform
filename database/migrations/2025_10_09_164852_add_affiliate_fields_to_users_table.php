<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_publisher')->default(false)->after('remember_token');
            $table->string('affiliate_code')->nullable()->unique()->after('is_publisher');
            $table->decimal('commission_rate', 5, 2)->default(0)->after('affiliate_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_publisher','affiliate_code','commission_rate']);
        });
    }
};
