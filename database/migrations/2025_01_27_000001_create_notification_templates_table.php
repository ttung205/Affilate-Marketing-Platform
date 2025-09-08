<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // 'click', 'conversion', 'order', etc.
            $table->string('title');
            $table->text('message');
            $table->string('icon')->nullable();
            $table->string('color')->default('blue');
            $table->json('channels')->default('["database", "broadcast"]'); // database, broadcast, mail, sms
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
