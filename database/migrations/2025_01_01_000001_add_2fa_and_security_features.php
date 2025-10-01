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
        // Add 2FA column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->json('trusted_ips')->nullable()->after('last_login_ip');
        });

        // Update withdrawals table to add pending_otp status
        Schema::table('withdrawals', function (Blueprint $table) {
            // Modify the enum to include pending_otp
            $table->dropColumn('status');
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->enum('status', [
                'pending_otp', 
                'pending', 
                'approved', 
                'processing', 
                'completed', 
                'rejected', 
                'cancelled'
            ])->default('pending')->after('net_amount');
            
            // Add security fields
            $table->string('request_ip')->nullable()->after('transaction_reference');
            $table->string('user_agent')->nullable()->after('request_ip');
            $table->json('security_metadata')->nullable()->after('user_agent');
            $table->timestamp('otp_sent_at')->nullable()->after('security_metadata');
            $table->integer('otp_attempts')->default(0)->after('otp_sent_at');
        });

        // Create audit log table
        Schema::create('withdrawal_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('withdrawal_id')->constrained('withdrawals')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // created, approved, rejected, completed, etc.
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['withdrawal_id', 'created_at']);
            $table->index(['user_id', 'action']);
        });

        // Create admin IP whitelist table
        Schema::create('admin_ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->unique(['admin_id', 'ip_address']);
            $table->index(['ip_address', 'is_active']);
        });

        // Create fraud detection rules table
        Schema::create('fraud_detection_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // amount_threshold, frequency_limit, pattern_detection
            $table->json('conditions');
            $table->string('action'); // flag, block, require_approval
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fraud_detection_rules');
        Schema::dropIfExists('admin_ip_whitelist');
        Schema::dropIfExists('withdrawal_audit_logs');
        
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn([
                'request_ip',
                'user_agent', 
                'security_metadata',
                'otp_sent_at',
                'otp_attempts'
            ]);
            $table->dropColumn('status');
        });
        
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'processing', 'completed', 'rejected', 'cancelled'])
                  ->default('pending')->after('net_amount');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'two_factor_enabled',
                'last_login_at',
                'last_login_ip',
                'trusted_ips'
            ]);
        });
    }
};
