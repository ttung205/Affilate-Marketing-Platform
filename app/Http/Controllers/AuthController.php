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

        $user = User::where('email', $request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            //Chuyển hướng theo từng role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'publisher':
                    return redirect()->route('publisher.dashboard');
                case 'shop':
                    return redirect()->route('shop.dashboard');
                default:
            }
        }
        return back()->withErrors([
            'email' => 'Email hoac mat khau khong dung',
        ]);
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

        Auth::login($user);

        //Chuyển hướng theo từng role
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'publisher':
                return redirect()->route('publisher.dashboard');
            case 'shop':
                return redirect()->route('shop.dashboard');
            default:
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
