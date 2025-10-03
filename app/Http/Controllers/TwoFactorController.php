<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Illuminate\Support\Facades\Auth;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;

class TwoFactorController extends Controller
{
    /**
     * Show 2FA setup page
     */
    public function showSetup()
    {
        $user = Auth::user();
        
        // Generate secret key if not exists
        if (!$user->google2fa_secret) {
            $google2fa = app('pragmarx.google2fa');
            $user->google2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Generate QR Code
        $google2fa = app('pragmarx.google2fa');
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        // Generate QR Code SVG
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.2fa-setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $user->google2fa_secret,
            'user' => $user
        ]);
    }

    /**
     * Enable 2FA after verification
     */
    public function enableTwoFactor(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|numeric|digits:6',
        ]);

        $user = Auth::user();
        $google2fa = app('pragmarx.google2fa');

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            $user->google2fa_enabled = true;
            $user->google2fa_enabled_at = now();
            $user->save();

            return redirect()->back()->with('success', 'Google 2FA đã được kích hoạt thành công!');
        }

        return back()->withErrors(['one_time_password' => 'Mã xác thực không đúng. Vui lòng thử lại.']);
    }

    /**
     * Disable 2FA
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (!\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Mật khẩu không đúng.']);
        }

        $user->google2fa_enabled = false;
        $user->google2fa_secret = null;
        $user->google2fa_enabled_at = null;
        $user->save();

        return redirect()->back()->with('success', 'Google 2FA đã được tắt.');
    }

    /**
     * Show 2FA verification page during login
     */
    public function showVerify()
    {
        if (!session('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-verify');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|numeric|digits:6',
        ]);

        $userId = session('2fa:user:id');
        $remember = session('2fa:remember', false);

        if (!$userId) {
            return redirect()->route('login')->withErrors(['error' => 'Phiên đăng nhập đã hết hạn.']);
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Người dùng không tồn tại.']);
        }

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            // Clear 2FA session
            session()->forget(['2fa:user:id', '2fa:remember']);

            // Login user
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    return redirect()->intended(route('admin.dashboard'));
                case 'publisher':
                    return redirect()->intended(route('publisher.dashboard'));
                case 'shop':
                    return redirect()->intended(route('shop.dashboard'));
                default:
                    return redirect()->intended(route('home'));
            }
        }

        return back()->withErrors(['one_time_password' => 'Mã xác thực không đúng. Vui lòng thử lại.']);
    }
}

