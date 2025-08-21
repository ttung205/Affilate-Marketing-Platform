<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function performance()
    {
        return view('publisher.reports.performance');
    }

    public function commissions()
    {
        return view('publisher.reports.commissions');
    }

    public function clicks()
    {
        return view('publisher.reports.clicks');
    }
}
