<?php

namespace App\Http\Controllers\Vendor;

use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function index()
    {
        if (Auth::guard('vendor')->check()) {
            return redirect()->route('vendor.dashboard.index');
        }

        return redirect()->route('vendor.auth.login');
    }
}
