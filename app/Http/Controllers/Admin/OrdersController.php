<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\Vendor;
use App\Services\Orders\OrderAcceptService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:orders.view')->only(['index', 'show']);
        $this->middleware('permission:orders.edit')->only(['assign']);
    }

    public function index()
    {
        $orders = Order::query()->with(['customer', 'vendor'])->latest()->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'vendor', 'events', 'addOns', 'documents']);
        $vendors = Vendor::query()->where('is_active', true)->where('is_approved', true)->orderBy('id')->get();

        return view('admin.orders.show', compact('order', 'vendors'));
    }

    public function assign(Request $request, Order $order, OrderAcceptService $acceptService)
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
        ]);

        $vendor = Vendor::query()->findOrFail($data['vendor_id']);

        try {
            $acceptService->assignManually($order, $vendor, auth('admin')->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', __('general.order_assigned_successfully'));
    }
}
