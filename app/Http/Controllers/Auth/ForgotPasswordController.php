<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Mail\ResetPasswordMail;
class ForgotPasswordController extends Controller
{
    /**
     * Hiển thị form quên mật khẩu
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Gửi email reset password
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.exists' => 'Email không tồn tại trong hệ thống'
        ]);

        try {
            // Tạo token ngẫu nhiên
            $token = Str::random(64);
            
            // Lưu token vào database
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => $token,
                    'created_at' => Carbon::now()
                ]
            );

            // Gửi email 
             Mail::to($request->email)->send(new ResetPasswordMail($token));

            return back()->with('status', 'Chúng tôi đã gửi link đặt lại mật khẩu vào email của bạn!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Có lỗi xảy ra, vui lòng thử lại sau.']);
        }
    }
}
