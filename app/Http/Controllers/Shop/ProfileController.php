<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('shop.profile.edit');
    }

    public function update(Request $request)
    {
        // TODO: Implement profile update
        return redirect()->route('shop.profile.edit')->with('success', 'Hồ sơ đã được cập nhật thành công!');
    }
}
