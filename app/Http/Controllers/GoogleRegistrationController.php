<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class GoogleRegistrationController extends Controller
{
    /**
     * Hiển thị form chọn role và hoàn tất đăng ký
     */
    public function showRegistrationForm()
    {
        // Kiểm tra xem có thông tin Google user không
        if (!Session::has('google_user_data')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập bằng Google trước');
        }

        $googleUserData = Session::get('google_user_data');
        
        return view('auth.google-registration', compact('googleUserData'));
    }

    /**
     * Xử lý đăng ký hoàn tất
     */
    public function completeRegistration(Request $request)
    {
        // Validate request
        $request->validate([
            'role' => 'required|in:shop,publisher',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        // Kiểm tra xem có thông tin Google user không
        if (!Session::has('google_user_data')) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập bằng Google trước');
        }

        $googleUserData = Session::get('google_user_data');

        try {
            // Tạo user mới
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'google_id' => $googleUserData['google_id'],
                'avatar' => $googleUserData['avatar'],
                'password' => Hash::make($googleUserData['email']), // Sử dụng email làm password
                'role' => $request->role,
            ]);

            // Xóa session Google user data
            Session::forget('google_user_data');

            // Đăng nhập user
            Auth::login($user);

            Log::info('Google user registration completed', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);

            // Chuyển hướng dựa vào role
            return $this->redirectBasedOnRole($user);

        } catch (\Exception $e) {
            Log::error('Google registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Đăng ký thất bại: ' . $e->getMessage());
        }
    }

    /**
     * Chuyển hướng user dựa vào role
     */
    private function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'shop':
                return redirect()->route('shop.dashboard');
            case 'publisher':
                return redirect()->route('publisher.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }
}
