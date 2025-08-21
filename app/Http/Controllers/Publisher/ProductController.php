<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return view('publisher.products.index');
    }

    public function create()
    {
        return view('publisher.products.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement product creation
        return redirect()->route('publisher.products.index');
    }

    public function show($id)
    {
        return view('publisher.products.show');
    }

    public function edit($id)
    {
        return view('publisher.products.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement product update
        return redirect()->route('publisher.products.index');
    }

    public function destroy($id)
    {
        // TODO: Implement product deletion
        return redirect()->route('publisher.products.index');
    }
}
