<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('publisher.settings.index');
    }

    public function create()
    {
        return view('publisher.settings.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement setting creation
        return redirect()->route('publisher.settings.index');
    }

    public function show($id)
    {
        return view('publisher.settings.show');
    }

    public function edit($id)
    {
        return view('publisher.settings.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement setting update
        return redirect()->route('publisher.settings.index');
    }

    public function destroy($id)
    {
        // TODO: Implement setting deletion
        return redirect()->route('publisher.settings.index');
    }
}
