<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        return view('shop.orders.index');
    }

    public function create()
    {
        return view('shop.orders.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement order creation
        return redirect()->route('shop.orders.index')->with('success', 'Đơn hàng đã được tạo thành công!');
    }

    public function show($id)
    {
        return view('shop.orders.show', compact('id'));
    }

    public function edit($id)
    {
        return view('shop.orders.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement order update
        return redirect()->route('shop.orders.index')->with('success', 'Đơn hàng đã được cập nhật thành công!');
    }

    public function destroy($id)
    {
        // TODO: Implement order deletion
        return redirect()->route('shop.orders.index')->with('success', 'Đơn hàng đã được xóa thành công!');
    }
}
