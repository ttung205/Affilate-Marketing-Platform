<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AffiliateLinkController extends Controller
{
    public function index()
    {
        return view('publisher.affiliate-links.index');
    }

    public function create()
    {
        return view('publisher.affiliate-links.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement affiliate link creation
        return redirect()->route('publisher.affiliate-links.index');
    }

    public function show($id)
    {
        return view('publisher.affiliate-links.show');
    }

    public function edit($id)
    {
        return view('publisher.affiliate-links.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement affiliate link update
        return redirect()->route('publisher.affiliate-links.index');
    }

    public function destroy($id)
    {
        // TODO: Implement affiliate link deletion
        return redirect()->route('publisher.affiliate-links.index');
    }
}
