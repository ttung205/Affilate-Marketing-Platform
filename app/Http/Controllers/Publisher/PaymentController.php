<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return view('publisher.payments.index');
    }

    public function create()
    {
        return view('publisher.payments.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement payment creation
        return redirect()->route('publisher.payments.index');
    }

    public function show($id)
    {
        return view('publisher.payments.show');
    }

    public function edit($id)
    {
        return view('publisher.payments.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement payment update
        return redirect()->route('publisher.payments.index');
    }

    public function destroy($id)
    {
        // TODO: Implement payment deletion
        return redirect()->route('publisher.payments.index');
    }
}
