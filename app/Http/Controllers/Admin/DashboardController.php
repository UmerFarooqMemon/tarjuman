<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\EstimateDashboardStats;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Admin Dashboard — estimate funnel & quote insights.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(EstimateDashboardStats $stats)
    {
        return view('admin.dashboard.index', [
            'dashboard' => $stats->build(),
        ]);
    }
}
