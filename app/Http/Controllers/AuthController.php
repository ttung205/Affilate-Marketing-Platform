<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember', true); // Default to true for persistent session

        // Attempt to authenticate
        if (Auth::attempt($credentials, false)) { // Don't login yet if 2FA is enabled
            $user = Auth::user();
            Auth::logout(); // Logout immediately
            
            // Check if user has 2FA enabled
            if ($user->google2fa_enabled) {
                // Store user ID and remember preference in session for 2FA verification
                session([
                    '2fa:user:id' => $user->id,
                    '2fa:remember' => $remember
                ]);
                
                return redirect()->route('2fa.verify');
            }

            // If 2FA is not enabled, login normally
            Auth::login($user, $remember);
            $request->session()->regenerate();
            
            //Chuyển hướng theo từng role
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

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không đúng',
        ])->withInput($request->except('password'));
    }


    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'publisher',
        ]);

        // Auto login with remember me enabled
        Auth::login($user, true);
        $request->session()->regenerate();

        //Chuyển hướng theo từng role
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'publisher':
                return redirect()->route('publisher.dashboard');
            case 'shop':
                return redirect()->route('shop.dashboard');
            default:
                return redirect()->route('home');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('home');
    }
}
