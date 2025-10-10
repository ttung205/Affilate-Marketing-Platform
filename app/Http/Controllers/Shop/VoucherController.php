<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VoucherAssignedNotification;

class VoucherController extends Controller
{
    public function index()
    {
        $shopId = Auth::id();
        $vouchers = Voucher::where('shop_id', $shopId)
            ->latest()
            ->paginate(10);

        return view('shop.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        $publishers = User::where('role', 'publisher')->get();
        $products = Product::where('user_id', Auth::id())->get();
        return view('shop.vouchers.create', compact('publishers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'         => 'required|string|unique:vouchers,code',
            'type'         => 'required|in:percent,fixed,freeship',
            'value'        => 'nullable|numeric|min:0',
            'min_order'    => 'nullable|numeric|min:0',
            'max_uses'     => 'nullable|integer|min:0',
            'expires_at'   => 'nullable|date',
            'is_global'    => 'required|boolean',
            'publisher_id' => 'nullable|exists:users,id',
            'product_ids'  => 'nullable|array',
        ]);


                if ($request->type === 'percent') {
            $request->validate([
                'value' => 'required|numeric|min:1|max:100',
            ], [
                'value.max' => 'Giá trị phần trăm không được vượt quá 100%.',
                'value.min' => 'Giá trị phần trăm phải ít nhất là 1%.',
            ]);
        }

        // Kiểm tra publisher hợp lệ
        if ($request->publisher_id) {
            $publisher = User::find($request->publisher_id);
            if (!$publisher || $publisher->role !== 'publisher') {
                return back()->withErrors(['publisher_id' => 'Người nhận không hợp lệ (phải là publisher)']);
            }
        }

        // Tạo voucher
        $voucher = Voucher::create([
            'shop_id'      => Auth::id(),
            'publisher_id' => $request->publisher_id,
            'code'         => strtoupper($request->code),
            'type'         => $request->type,
            'value'        => $request->value ?? 0,
            'min_order'    => $request->min_order ?? 0,
            'max_uses'     => $request->max_uses ?? 0,
            'used_count'   => 0,
            'is_active'    => true,
            'expires_at'   => $request->expires_at,
            'is_global'    => (bool) $request->is_global,
        ]);

        // Gắn sản phẩm nếu không áp dụng toàn shop
        if (!$voucher->is_global && $request->filled('product_ids')) {
            $allowed = Product::where('user_id', Auth::id())
                ->whereIn('id', $request->product_ids)
                ->pluck('id')
                ->toArray();

            if ($allowed) {
                $voucher->products()->sync($allowed);
            }
        }

        // Gửi thông báo
        if ($request->publisher_id) {
            $publisher = User::find($request->publisher_id);
            $publisher->notify(new VoucherAssignedNotification($voucher));
        } else {
            $publishers = User::where('role', 'publisher')->get();
            if ($publishers->count()) {
                Notification::send($publishers, new VoucherAssignedNotification($voucher));
            }
        }

        return redirect()
            ->route('shop.vouchers.index')
            ->with('success', 'Tạo voucher thành công!');
    }

    public function show(Voucher $voucher)
    {
        if ($voucher->shop_id !== Auth::id()) {
            abort(403);
        }

        $voucher->load('publisher', 'products');
        return view('shop.vouchers.show', compact('voucher'));
    }
    public function destroy(Voucher $voucher)
{
    if ($voucher->shop_id !== Auth::id()) {
        abort(403);
    }

    $voucher->delete();

    return redirect()->route('shop.vouchers.index')->with('success', 'Đã xóa voucher thành công.');
}

}
