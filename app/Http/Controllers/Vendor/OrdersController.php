<?php

namespace App\Http\Controllers\Vendor;

use App\Models\AddOn;
use App\Models\DeliverySpeed;
use App\Models\DocumentType;
use App\Models\Order;
use App\Services\Orders\OrderAcceptService;
use App\Services\Orders\OrderCompleteService;
use App\Services\Orders\OrderDocumentAccess;
use App\Services\Orders\OrderNotificationDispatcher;
use App\Services\Orders\VendorMyOrdersQuery;
use App\Services\Orders\VendorOpenOrdersDiscoverService;
use App\Services\Orders\VendorOrderDetailsPresenter;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:vendor');
    }

    public function index(Request $request, VendorMyOrdersQuery $ordersQuery)
    {
        $filters = $ordersQuery->filtersFromRequest($request);

        return view('vendor.orders.index', [
            'filters' => $filters,
            'statusOptions' => $ordersQuery->statusOptions(),
            'paymentStatusOptions' => $ordersQuery->paymentStatusOptions(),
            'deliverySpeeds' => DeliverySpeed::cachedActive(),
            'addOns' => AddOn::cachedActive(),
            'documentTypes' => DocumentType::cachedActive(),
            'dataUrl' => route('vendor.orders.data'),
        ]);
    }

    public function indexData(Request $request, VendorMyOrdersQuery $ordersQuery)
    {
        $vendorId = (int) auth('vendor')->user()?->vendor_id;
        $filters = $ordersQuery->filtersFromRequest($request);

        $orders = $vendorId > 0
            ? $ordersQuery->get($vendorId, $filters)
            : collect();

        $rows = $orders->map(function (Order $order) {
            $amount = $order->confirmed_amount ?? $order->estimate_amount;
            $showUrl = route('vendor.orders.show', $order);
            $isConfirm = $order->status === Order::STATUS_PENDING_VENDOR_CONFIRM;

            return [
                'order_id' => $order->order_id,
                'document_type' => $order->documentType?->displayName() ?: '—',
                'status' => orderStatusBadge($order->status),
                'payment_status' => orderPaymentStatusBadge($order->payment_status),
                'amount_html' => formatMoney($amount, $order->currency),
                'created_at' => optional($order->created_at)
                    ?->timezone(config('app.timezone'))
                    ?->locale('en')
                    ?->format('d M Y, g:i A'),
                'action_html' => $isConfirm
                    ? '<a href="'.e($showUrl).'" class="btn btn-sm btn-primary">'.e(__('general.review_and_confirm')).'</a>'
                    : '<a href="'.e($showUrl).'" class="btn btn-sm btn-outline-primary">'.e(__('general.view_order')).'</a>',
            ];
        })->values();

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function discover(Request $request, VendorOpenOrdersDiscoverService $discover)
    {
        $mode = normalizeAssignmentMode(siteSettings()?->order_assignment_mode);
        $filters = $discover->filtersFromRequest($request);
        $deliverySpeeds = DeliverySpeed::cachedActive();
        $addOns = AddOn::cachedActive();
        $documentTypes = DocumentType::cachedActive();

        if ($mode !== 'open') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'data' => [
                        'orders' => [],
                        'meta' => [
                            'current_page' => 1,
                            'last_page' => 1,
                            'has_more' => false,
                            'next_page' => null,
                            'total' => 0,
                        ],
                    ],
                ]);
            }

            return view('vendor.orders.discover', [
                'mode' => $mode,
                'filters' => $filters,
                'orders' => null,
                'cards' => [],
                'hasMore' => false,
                'nextPage' => null,
                'total' => 0,
                'deliverySpeeds' => $deliverySpeeds,
                'addOns' => $addOns,
                'documentTypes' => $documentTypes,
            ]);
        }

        $paginator = $discover->paginate($filters);
        $cards = $paginator->getCollection()->map(fn (Order $order) => $discover->card($order))->values()->all();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'data' => [
                    'orders' => $cards,
                    'meta' => [
                        'current_page' => $paginator->currentPage(),
                        'last_page' => $paginator->lastPage(),
                        'has_more' => $paginator->hasMorePages(),
                        'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
                        'total' => $paginator->total(),
                    ],
                ],
            ]);
        }

        return view('vendor.orders.discover', [
            'mode' => $mode,
            'filters' => $filters,
            'orders' => $paginator,
            'cards' => $cards,
            'hasMore' => $paginator->hasMorePages(),
            'nextPage' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            'total' => $paginator->total(),
            'deliverySpeeds' => $deliverySpeeds,
            'addOns' => $addOns,
            'documentTypes' => $documentTypes,
        ]);
    }

    public function show(Order $order, OrderDocumentAccess $access)
    {
        $vendorUser = auth('vendor')->user();
        if ((int) $order->vendor_id !== (int) $vendorUser->vendor_id) {
            abort(403);
        }

        $order->load(['documents' => fn ($q) => $q->whereNull('purged_at')->latest()]);
        $canAccessDocuments = $access->vendorCanAccess($order, $vendorUser);
        $canConfirm = $order->status === Order::STATUS_PENDING_VENDOR_CONFIRM;
        $canSendPaymentLink = $this->canSendPaymentLink($order);
        $completeService = app(OrderCompleteService::class);
        $canManageDelivery = $completeService->vendorCanManageDelivery($order, (int) $vendorUser->vendor_id);
        $canComplete = $completeService->vendorCanComplete($order, (int) $vendorUser->vendor_id);
        $downloadAllowed = vendorDocumentDownloadAllowed();
        $details = app(VendorOrderDetailsPresenter::class)
            ->present($order, includeDocuments: $canAccessDocuments, viewer: $vendorUser);

        return view('vendor.orders.show', compact(
            'order',
            'canAccessDocuments',
            'canConfirm',
            'canSendPaymentLink',
            'canManageDelivery',
            'canComplete',
            'downloadAllowed',
            'details'
        ));
    }

    public function details(Order $order, VendorOrderDetailsPresenter $presenter, OrderDocumentAccess $access)
    {
        $vendorUser = auth('vendor')->user();
        $assignedToVendor = (int) $order->vendor_id === (int) $vendorUser->vendor_id;
        $includeDocuments = $assignedToVendor && $access->vendorCanAccess($order, $vendorUser);

        // Open / mine / taken-by-another all open in the modal; sensitive fields are gated in the presenter.
        return response()->json($presenter->present(
            $order,
            includeDocuments: $includeDocuments,
            viewer: $vendorUser
        ));
    }

    /** @deprecated Use index(); kept as alias for existing links/notifications. */
    public function mine(Request $request)
    {
        return $this->index($request);
    }

    public function accept(Order $order, OrderAcceptService $acceptService)
    {
        $vendorUser = auth('vendor')->user();
        $vendor = $vendorUser->vendor;

        try {
            $acceptService->accept($order, $vendor, (int) $vendorUser->id);
        } catch (ConflictHttpException $e) {
            return back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $order->refresh();

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('general.order_accepted_successfully'));
    }

    public function confirm(Request $request, Order $order, PaymentGatewayManager $gateways)
    {
        $vendorId = auth('vendor')->user()?->vendor_id;
        if ((int) $order->vendor_id !== (int) $vendorId) {
            abort(403);
        }

        if ($order->status !== Order::STATUS_PENDING_VENDOR_CONFIRM) {
            return back()->with('error', __('general.order_not_confirmable'));
        }

        $data = $request->validate([
            'confirmed_amount' => ['required', 'numeric', 'min:0'],
            'vendor_note' => ['nullable', 'string', 'max:2000'],
            'confirm_action' => ['required', 'in:with_payment_link,amount_only'],
        ]);

        $sendPaymentLink = $data['confirm_action'] === 'with_payment_link';

        $order->forceFill([
            'confirmed_amount' => $data['confirmed_amount'],
            'vendor_note' => $data['vendor_note'] ?? $order->vendor_note,
            'confirmed_at' => now(),
        ])->save();

        $order->events()->create([
            'type' => 'vendor_confirmed',
            'actor_type' => 'vendor_user',
            'actor_id' => auth('vendor')->id(),
            'payload' => [
                'confirmed_amount' => $order->confirmed_amount,
                'send_payment_link' => $sendPaymentLink,
            ],
        ]);

        if (! $sendPaymentLink) {
            $order->forceFill([
                'status' => Order::STATUS_ASSIGNED,
            ])->save();

            return redirect()
                ->route('vendor.orders.show', $order)
                ->with('success', __('general.order_amount_confirmed'));
        }

        $this->createAndAttachPaymentLink($order, $gateways);

        app(OrderNotificationDispatcher::class)->paymentLinkReady($order->fresh(['customer']));

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('general.order_payment_link_sent'));
    }

    public function sendPaymentLink(Order $order, PaymentGatewayManager $gateways)
    {
        $vendorId = auth('vendor')->user()?->vendor_id;
        if ((int) $order->vendor_id !== (int) $vendorId) {
            abort(403);
        }

        if (! $this->canSendPaymentLink($order)) {
            return back()->with('error', __('general.order_payment_link_unavailable'));
        }

        $this->createAndAttachPaymentLink($order, $gateways);

        $order->events()->create([
            'type' => 'payment_link_sent',
            'actor_type' => 'vendor_user',
            'actor_id' => auth('vendor')->id(),
            'payload' => [
                'confirmed_amount' => $order->confirmed_amount,
            ],
        ]);

        app(OrderNotificationDispatcher::class)->paymentLinkReady($order->fresh(['customer']));

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('general.order_payment_link_sent'));
    }

    public function complete(Request $request, Order $order, OrderCompleteService $completeService)
    {
        $vendorUser = auth('vendor')->user();
        $vendorId = (int) ($vendorUser?->vendor_id ?? 0);

        if (! $completeService->vendorCanComplete($order, $vendorId)) {
            return back()->with('error', __('general.order_not_completable'));
        }

        $data = $request->validate([
            'completed_add_ons' => ['nullable', 'array'],
            'completed_add_ons.*' => ['integer'],
            'confirm_delivery_ready' => ['accepted'],
        ]);

        try {
            $completeService->complete(
                $order,
                array_map('intval', $data['completed_add_ons'] ?? []),
                $request->boolean('confirm_delivery_ready'),
                (int) $vendorUser->id
            );
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first())->withInput();
        }

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('general.order_completed_successfully'));
    }

    protected function canSendPaymentLink(Order $order): bool
    {
        if ($order->confirmed_amount === null) {
            return false;
        }

        if (in_array($order->payment_status, [Order::PAYMENT_PAID, Order::PAYMENT_COVERED_BY_PLAN], true)) {
            return false;
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return false;
        }

        if ($order->status === Order::STATUS_PENDING_VENDOR_CONFIRM) {
            return false;
        }

        return true;
    }

    protected function createAndAttachPaymentLink(Order $order, PaymentGatewayManager $gateways): void
    {
        $gateway = $gateways->default();
        $returnUrl = url('/api/orders/payments/'.$gateway->driverName().'/return');
        $callbackUrl = url('/api/orders/payments/'.$gateway->driverName().'/callback');
        $payment = $gateway->createPayment($order->fresh()->loadMissing('customer'), $returnUrl, $callbackUrl);

        $payload = [
            'payment_status' => Order::PAYMENT_PENDING,
            'payment_gateway_snapshot' => $gateway->driverName(),
            'payment_tran_ref' => $payment['tran_ref'] ?? null,
            'payment_checkout_id' => $payment['checkout_id'] ?? null,
            'payment_link_url' => $payment['payment_link'] ?? $payment['redirect_url'] ?? null,
        ];

        // Keep work/completion status when the link is sent later.
        if (! in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_IN_PROGRESS], true)) {
            $payload['status'] = Order::STATUS_AWAITING_CUSTOMER_PAYMENT;
        }

        $order->forceFill($payload)->save();
    }
}
