<?php

namespace App\Http\Controllers\Vendor;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:vendor');
    }

    /**
     * Vendor portal dashboard (scaffold).
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('vendor.dashboard.index');
    }
}
