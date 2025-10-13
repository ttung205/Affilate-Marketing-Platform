<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformFeeSetting;
use Illuminate\Http\Request;

class PlatformFeeController extends Controller
{
    /**
     * Hiển thị trang quản lý phí sàn
     */
    public function index()
    {
        $settings = PlatformFeeSetting::orderBy('created_at', 'desc')->paginate(10);
        $currentFee = PlatformFeeSetting::getCurrentFee();
        
        return view('admin.platform-fee.index', compact('settings', 'currentFee'));
    }

    /**
     * Lưu cài đặt phí sàn mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fee_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'effective_from' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        // Nếu set là active, deactivate các setting cũ
        if ($request->is_active) {
            PlatformFeeSetting::where('is_active', true)->update(['is_active' => false]);
        }

        PlatformFeeSetting::create($validated);

        return redirect()->route('admin.platform-fees.index')
            ->with('success', 'Đã thêm cài đặt phí sàn mới thành công!');
    }

    /**
     * Cập nhật cài đặt phí sàn
     */
    public function update(Request $request, PlatformFeeSetting $platformFee)
    {
        $validated = $request->validate([
            'fee_percentage' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'effective_from' => 'nullable|date',
            'is_active' => 'boolean'
        ]);

        // Nếu set là active, deactivate các setting khác
        if ($request->is_active) {
            PlatformFeeSetting::where('id', '!=', $platformFee->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $platformFee->update($validated);

        return redirect()->route('admin.platform-fee.index')
            ->with('success', 'Đã cập nhật cài đặt phí sàn thành công!');
    }

    /**
     * Xóa cài đặt phí sàn
     */
    public function destroy(PlatformFeeSetting $platformFee)
    {
        $platformFee->delete();

        return redirect()->route('admin.platform-fee.index')
            ->with('success', 'Đã xóa cài đặt phí sàn thành công!');
    }
}
