<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        return view('publisher.campaigns.index');
    }

    public function create()
    {
        return view('publisher.campaigns.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement campaign creation
        return redirect()->route('publisher.campaigns.index');
    }

    public function show($id)
    {
        return view('publisher.campaigns.show');
    }

    public function edit($id)
    {
        return view('publisher.campaigns.edit');
    }

    public function update(Request $request, $id)
    {
        // TODO: Implement campaign update
        return redirect()->route('publisher.campaigns.index');
    }

    public function destroy($id)
    {
        // TODO: Implement campaign deletion
        return redirect()->route('publisher.campaigns.index');
    }
}
