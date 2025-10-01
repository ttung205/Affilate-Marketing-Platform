<?php

namespace App\Services;

use App\Models\User;
use App\Models\Withdrawal;
use App\Mail\WithdrawalOTPMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwoFactorAuthService
{
    
    /**
     * Generate admin approval token
     */
    public function generateAdminApprovalToken(User $admin, Withdrawal $withdrawal): string
    {
        $token = Str::random(32);
        $key = "admin_approval_token_{$admin->id}_{$withdrawal->id}";
        
        Cache::put($key, [
            'token' => $token,
            'admin_id' => $admin->id,
            'withdrawal_id' => $withdrawal->id,
            'created_at' => now()
        ], now()->addMinutes(30));
        
        Log::info('Admin approval token generated', [
            'admin_id' => $admin->id,
            'withdrawal_id' => $withdrawal->id
        ]);
        
        return $token;
    }
    
    /**
     * Verify admin approval token
     */
    public function verifyAdminApprovalToken(User $admin, Withdrawal $withdrawal, string $token): bool
    {
        $key = "admin_approval_token_{$admin->id}_{$withdrawal->id}";
        $tokenData = Cache::get($key);
        
        if (!$tokenData || $tokenData['token'] !== $token) {
            Log::warning('Admin approval token verification failed', [
                'admin_id' => $admin->id,
                'withdrawal_id' => $withdrawal->id
            ]);
            return false;
        }
        
        Cache::forget($key);
        
        Log::info('Admin approval token verified', [
            'admin_id' => $admin->id,
            'withdrawal_id' => $withdrawal->id
        ]);
        
        return true;
    }
    
    /**
     * Send OTP via email
     */
    private function sendOTPEmail(User $user, string $otp, Withdrawal $withdrawal): void
    {
        try {
            Mail::send('emails.withdrawal-otp', [
                'user' => $user,
                'otp' => $otp,
                'withdrawal' => $withdrawal,
                'expires_at' => now()->addMinutes(10)->format('H:i d/m/Y')
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Mã xác thực rút tiền - ' . config('app.name'));
            });
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check if user has 2FA enabled (always true for withdrawals - mandatory)
     */
    public function is2FAEnabled(User $user): bool
    {
        return true; // 2FA is now mandatory for all withdrawal requests
    }
    
    /**
     * Get 2FA status info for user
     */
    public function get2FAInfo(User $user): array
    {
        return [
            'is_mandatory' => true,
            'enabled_for_withdrawals' => true,
            'message' => 'Xác thực 2 lớp là bắt buộc cho tất cả yêu cầu rút tiền để đảm bảo bảo mật tài khoản của bạn.',
        ];
    }

    /**
     * Generate OTP for session-based withdrawal
     */
    public function generateWithdrawalOTPForSession($user, $sessionKey)
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $key = "withdrawal_otp_session_{$user->id}_{$sessionKey}";
        
        Cache::put($key, [
            'otp' => $otp,
            'session_key' => $sessionKey,
            'attempts' => 0,
            'created_at' => now()
        ], now()->addMinutes(10));
        
        // Send OTP via email
        Mail::to($user->email)->send(new \App\Mail\WithdrawalOTPMail($user, $otp));
        
        Log::info('Withdrawal OTP generated for session', [
            'user_id' => $user->id,
            'session_key' => $sessionKey
        ]);
    }

    /**
     * Verify OTP for session-based withdrawal
     */
    public function verifyWithdrawalOTPForSession($user, $sessionKey, $inputOtp)
    {
        $key = "withdrawal_otp_session_{$user->id}_{$sessionKey}";
        $otpData = Cache::get($key);
        
        if (!$otpData) {
            Log::warning('OTP verification failed - OTP expired or not found', [
                'user_id' => $user->id,
                'session_key' => $sessionKey
            ]);
            return false;
        }
        
        // Check attempts
        if ($otpData['attempts'] >= 3) {
            Cache::forget($key);
            Log::warning('OTP verification failed - too many attempts', [
                'user_id' => $user->id,
                'session_key' => $sessionKey,
                'attempts' => $otpData['attempts']
            ]);
            return false;
        }
        
        // Verify OTP
        if ($otpData['otp'] !== $inputOtp) {
            $otpData['attempts']++;
            Cache::put($key, $otpData, now()->addMinutes(10));
            
            Log::warning('OTP verification failed - incorrect OTP', [
                'user_id' => $user->id,
                'session_key' => $sessionKey,
                'attempts' => $otpData['attempts']
            ]);
            return false;
        }
        
        // OTP verified successfully
        Cache::forget($key);
        
        Log::info('OTP verified successfully for session', [
            'user_id' => $user->id,
            'session_key' => $sessionKey
        ]);
        
        return true;
    }
}
