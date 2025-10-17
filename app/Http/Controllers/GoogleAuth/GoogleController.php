<?php

namespace App\Http\Controllers\GoogleAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GoogleController extends Controller
{
    public function redirectToGoogle(){
        return Socialite::driver('google')->redirect();
    }

    // handle google callback
    public function handleGoogleCallback(){
        try{
            // lấy thông tin user từ gg
            $googleUser = Socialite::driver('google')->user();
            
            // Log để debug
            Log::info('Google callback received', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName()
            ]);

            // kiểm tra xem user đã tồn tại trong db chưa
            $user = User::where('google_id', $googleUser->getId())->first();
            
            // Nếu user đã tồn tại, đăng nhập luôn
            if ($user) {
                Auth::login($user);
                Log::info('Existing user logged in', ['user_id' => $user->id]);
                return $this->redirectBasedOnRole($user);
            }

            // Kiểm tra email đã tồn tại chưa
            $existingUser = User::where('email', $googleUser->getEmail())->first();
            
            if ($existingUser) {
                // Nếu email đã tồn tại, cập nhật google_id và đăng nhập
                $existingUser->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
                Auth::login($existingUser);
                Log::info('Updated existing user with Google ID', ['user_id' => $existingUser->id]);
                return $this->redirectBasedOnRole($existingUser);
            }

            // Nếu là user mới, lưu thông tin vào session và chuyển đến trang chọn role
            Session::put('google_user_data', [
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            Log::info('New Google user, redirecting to role selection');
            return redirect()->route('google.registration');

        } catch (\Exception $e) {
            Log::error('Google login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')->with('error', 'Đăng nhập bằng Google thất bại: ' . $e->getMessage());
        }
    }

    //chuyển hướng user dựa vào role
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