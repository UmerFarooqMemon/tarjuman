<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Orders\OrderNotificationDispatcher;
use App\Services\Orders\OrderPlacementService;
use App\Services\Orders\VendorEarningService;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request, OrderPlacementService $placement): JsonResponse
    {
        $maxFiles = max(1, (int) config('estimation.max_files', 10));
        $maxKb = max(1, (int) config('estimation.max_file_kb', 10240));
        $mimes = implode(',', config('estimation.allowed_mimes', ['pdf', 'docx', 'jpg', 'jpeg', 'png']));

        $data = $request->validate([
            'estimate_id' => ['nullable', 'integer', 'exists:estimates,id'],
            'estimate_uuid' => ['nullable', 'uuid'],
            'session_id' => ['nullable', 'uuid'],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:32'],
            'pay_with_plan' => ['sometimes', 'boolean'],
            'customer_note' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'documents' => ['required', 'array', 'min:1', 'max:'.$maxFiles],
            'documents.*' => ['required', 'file', 'mimes:'.$mimes, 'max:'.$maxKb],
        ]);

        $data['customer_note'] = $data['customer_note'] ?? $data['note'] ?? null;
        unset($data['note']);

        if (empty($data['estimate_id']) && empty($data['estimate_uuid'])) {
            return response()->json([
                'message' => __('general.estimate_not_found'),
                'errors' => ['estimate' => [__('general.estimate_not_found')]],
            ], 422);
        }

        /** @var list<UploadedFile> $documents */
        $documents = array_values(array_filter(
            $request->file('documents', []),
            fn ($file) => $file instanceof UploadedFile
        ));

        $authUser = auth('api')->user();
        $result = $placement->place($data, $authUser, $documents);

        return response()->json([
            'data' => [
                'order' => $this->orderPayload($result['order']),
                'payment' => $result['payment'],
            ],
        ], 201);
    }

    public function show(string $orderId): JsonResponse
    {
        $order = Order::query()->where('order_id', $orderId)->firstOrFail();

        $user = auth('api')->user();
        if ($user && (int) $order->customer_id !== (int) $user->id) {
            abort(403);
        }

        return response()->json(['data' => $this->orderPayload($order)]);
    }

    public function paymentCallback(string $driver, Request $request, PaymentGatewayManager $manager, VendorEarningService $earnings): JsonResponse
    {
        $gateway = $manager->driver($driver);
        $result = $gateway->verifyCallback($request);

        if (! ($result['success'] ?? false)) {
            return response()->json(['message' => __('general.payment_verification_failed')], 422);
        }

        $order = Order::query()
            ->where(function ($query) use ($result, $request): void {
                if (! empty($result['tran_ref'])) {
                    $query->where('payment_tran_ref', $result['tran_ref']);
                }

                $cartId = $request->input('cart_id') ?? $request->input('merchant_reference');
                if (filled($cartId)) {
                    $query->orWhere('uuid', $cartId)
                        ->orWhere('order_id', $cartId);
                }
            })
            ->first();

        if (! $order) {
            return response()->json(['message' => __('general.order_not_found')], 404);
        }

        DB::transaction(function () use ($order, $result, $driver, $earnings): void {
            if ($order->payment_status === Order::PAYMENT_PAID) {
                return;
            }

            $order->forceFill([
                'payment_status' => Order::PAYMENT_PAID,
                'payment_gateway_snapshot' => $driver,
                'payment_tran_ref' => $result['tran_ref'] ?? $order->payment_tran_ref,
                'paid_at' => now(),
                'status' => $order->status === Order::STATUS_PENDING_PAYMENT
                    || $order->status === Order::STATUS_AWAITING_CUSTOMER_PAYMENT
                    ? ($order->status === Order::STATUS_AWAITING_CUSTOMER_PAYMENT
                        ? Order::STATUS_IN_PROGRESS
                        : Order::STATUS_OPEN)
                    : $order->status,
            ])->save();

            $order->events()->create([
                'type' => 'paid',
                'actor_type' => 'gateway',
                'payload' => [
                    'driver' => $driver,
                    'tran_ref' => $result['tran_ref'] ?? null,
                ],
            ]);

            $earnings->accrueForOrder($order->fresh());
        });

        app(OrderNotificationDispatcher::class)->orderPaid($order->fresh());

        return response()->json(['data' => ['ok' => true, 'order_id' => $order->order_id]]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function orderPayload(Order $order): array
    {
        $order->loadMissing('documents');

        return [
            'order_id' => $order->order_id,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'payment_timing' => $order->payment_timing_snapshot,
            'assignment_mode' => normalizeAssignmentMode($order->assignment_mode_snapshot),
            'estimate_amount' => $order->estimate_amount,
            'confirmed_amount' => $order->confirmed_amount,
            'currency' => $order->currency,
            'payment_link_url' => $order->payment_link_url,
            'documents' => $order->documents->map(fn ($document) => [
                'uuid' => $document->uuid,
                'kind' => $document->kind,
                'original_name' => $document->original_name,
                'mime' => $document->mime,
                'size' => $document->size,
                'retained_until' => optional($document->retained_until)?->toIso8601String(),
                'purged' => $document->isPurged(),
            ])->values()->all(),
            'created_at' => optional($order->created_at)?->toIso8601String(),
        ];
    }
}
