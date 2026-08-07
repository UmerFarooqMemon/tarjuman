<?php

namespace App\Http\Controllers\Vendor;

use App\Services\Orders\VendorOpenOrdersDiscoverService;
use App\Services\Vendor\VendorDashboardStats;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:vendor');
    }

    /**
     * Vendor portal dashboard with KPIs and latest open marketplace jobs.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(VendorDashboardStats $stats, VendorOpenOrdersDiscoverService $discover)
    {
        $vendorUser = auth('vendor')->user();
        $vendorId = (int) $vendorUser->vendor_id;
        $kpis = $stats->forVendor($vendorId);

        $latestOpenCards = [];
        if ($kpis['assignment_mode'] === 'open') {
            $latestOpenCards = $discover
                ->query([
                    'q' => '',
                    'sort' => 'newest',
                    'delivery_speed_id' => null,
                    'add_on_id' => null,
                    'document_type_id' => null,
                ])
                ->limit(3)
                ->get()
                ->map(fn ($order) => $discover->card($order))
                ->all();
        }

        return view('vendor.dashboard.index', [
            'kpis' => $kpis,
            'latestOpenCards' => $latestOpenCards,
            'vendorName' => $vendorUser->vendor?->displayName() ?? $vendorUser->fullName(),
        ]);
    }
}
