<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ResetPasswordController extends Controller
{
    /**
     * Hiển thị form đặt lại mật khẩu
     */
    public function showResetForm(Request $request, $token)
    {
        // Kiểm tra token có hợp lệ không
        $resetData = DB::table('password_reset_tokens')
            ->where('token', $token)
            ->first();

        if (!$resetData) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link đặt lại mật khẩu không hợp lệ.']);
        }

        // Kiểm tra token có hết hạn không (60 phút)
        if (Carbon::parse($resetData->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $resetData->email)->delete();
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Link đặt lại mật khẩu đã hết hạn.']);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Xử lý đặt lại mật khẩu
     */
    public function reset(Request $request)
    {
        // Validate input
        $request->validate([
            'token' => 'required',
            'reset_email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'reset_email.required' => 'Vui lòng nhập email',
            'reset_email.email' => 'Email không đúng định dạng',
            'password.required' => 'Vui lòng nhập mật khẩu mới',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp'
        ]);

        try {
            // Kiểm tra token
            $resetData = DB::table('password_reset_tokens')
                ->where('token', $request->token)
                ->where('email', $request->reset_email)
                ->first();

            if (!$resetData) {
                return back()->withErrors(['reset_email' => 'Token không hợp lệ.']);
            }

            // Update password cho user
            User::where('email', $request->reset_email)->update([
                'password' => Hash::make($request->password)
            ]);

            // Xóa token đã sử dụng
            DB::table('password_reset_tokens')
                ->where('email', $request->reset_email)
                ->delete();

            return redirect()->route('login')
                ->with('status', 'Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập.');

        } catch (\Exception $e) {
            return back()->withErrors(['reset_email' => 'Có lỗi xảy ra, vui lòng thử lại sau.']);
        }
    }
}
