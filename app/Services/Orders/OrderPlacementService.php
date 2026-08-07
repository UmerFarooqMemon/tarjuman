<?php

namespace App\Services\Orders;

use App\Models\OrderDocument;
use App\Models\EnterpriseSubscription;
use App\Models\Estimate;
use App\Models\Order;
use App\Models\OrderAddOn;
use App\Models\OrderEvent;
use App\Models\SubscriptionUsageEvent;
use App\Models\User;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderPlacementService
{
    public function __construct(
        protected PaymentGatewayManager $gateways,
        protected SecureOrderFileStore $files,
        protected OrderNotificationDispatcher $notifier,
    ) {}

    /**
     * @param  array{
     *   estimate_id?: int,
     *   estimate_uuid?: string,
     *   session_id?: string,
     *   first_name: string,
     *   last_name: string,
     *   email: string,
     *   phone?: string,
     *   pay_with_plan?: bool,
     *   customer_note?: string,
     *   password?: string
     * }  $input
     * @param  list<UploadedFile>  $documents
     * @return array{order: Order, payment?: array<string, mixed>, user: User}
     */
    public function place(array $input, ?User $authUser = null, array $documents = []): array
    {
        if ($documents === []) {
            throw ValidationException::withMessages([
                'documents' => [__('general.order_documents_required')],
            ]);
        }

        $settings = siteSettings();
        $paymentMode = $settings?->order_payment_mode ?: 'later';
        $assignmentMode = normalizeAssignmentMode($settings?->order_assignment_mode);
        $sourceRetentionDays = max(1, (int) ($settings?->order_source_retention_days ?: 90));

        $estimate = $this->resolveEstimate($input);
        $user = $authUser ?? $this->ensureIndividualUser($input);

        if (! empty($input['pay_with_plan']) && ! $user->isEnterprise()) {
            throw ValidationException::withMessages([
                'pay_with_plan' => [__('general.order_plan_enterprise_only')],
            ]);
        }

        $result = DB::transaction(function () use ($input, $estimate, $user, $paymentMode, $assignmentMode, $documents, $sourceRetentionDays) {
            $payWithPlan = ! empty($input['pay_with_plan']);
            $subscription = $payWithPlan ? $this->assertPlanCoverage($user, $estimate) : null;

            $order = Order::query()->create([
                'customer_id' => $user->id,
                'estimate_id' => $estimate->id,
                'session_uuid' => $estimate->session_uuid,
                'status' => $this->initialStatus($paymentMode, $payWithPlan),
                'payment_status' => $payWithPlan ? Order::PAYMENT_COVERED_BY_PLAN : (
                    $paymentMode === 'quick' ? Order::PAYMENT_PENDING : Order::PAYMENT_UNPAID
                ),
                'payment_method' => $payWithPlan ? 'plan' : 'gateway',
                'payment_timing_snapshot' => $paymentMode,
                'assignment_mode_snapshot' => $assignmentMode,
                'source_language_id' => $estimate->source_language_id,
                'target_language_id' => $estimate->target_language_id,
                'document_type_id' => $estimate->document_type_id,
                'delivery_speed_id' => $estimate->delivery_speed_id,
                'word_count' => $estimate->word_count,
                'page_count' => $estimate->page_count,
                'estimate_amount' => $estimate->total_amount,
                'currency' => $estimate->currency ?: platformCurrency(),
                'customer_note' => filled($input['customer_note'] ?? null)
                    ? trim((string) $input['customer_note'])
                    : null,
            ]);

            $estimate->loadMissing('documents', 'addOns');

            $estimateDocsByName = $estimate->documents
                ->keyBy(fn ($doc) => mb_strtolower((string) $doc->filename));

            $pendingDocs = [];
            foreach ($documents as $file) {
                $stored = $this->files->store($file, $order, OrderDocument::KIND_SOURCE);
                $nameKey = mb_strtolower((string) $stored['original_name']);
                $estimateDoc = $estimateDocsByName->get($nameKey);
                $pendingDocs[] = [
                    'payload' => [
                        'uuid' => $stored['uuid'],
                        'order_id' => $order->id,
                        'kind' => OrderDocument::KIND_SOURCE,
                        'disk_path' => $stored['disk_path'],
                        'original_name' => $stored['original_name'],
                        'mime' => $stored['mime'],
                        'checksum_sha256' => $stored['checksum_sha256'],
                        'encryption' => $stored['encryption'],
                        'size' => $stored['size'],
                        'pages' => (int) ($estimateDoc?->pages ?? 0),
                        'words' => (int) ($estimateDoc?->words ?? 0),
                        'retained_until' => now()->addDays($sourceRetentionDays),
                    ],
                    'pages' => (int) ($estimateDoc?->pages ?? 0),
                    'words' => (int) ($estimateDoc?->words ?? 0),
                ];
            }

            $totalPages = array_sum(array_column($pendingDocs, 'pages'));
            $totalWords = array_sum(array_column($pendingDocs, 'words'));
            $orderAmount = (float) ($estimate->total_amount ?? 0);
            $useWords = $totalWords > 0;
            $weightTotal = $useWords ? $totalWords : $totalPages;
            $allocated = 0.0;
            $lastIndex = count($pendingDocs) - 1;

            foreach ($pendingDocs as $index => $row) {
                $weight = $useWords ? $row['words'] : $row['pages'];
                if ($index === $lastIndex) {
                    $amount = round(max(0, $orderAmount - $allocated), 2);
                } elseif ($weightTotal > 0 && $orderAmount > 0) {
                    $amount = round($orderAmount * ($weight / $weightTotal), 2);
                    $allocated += $amount;
                } else {
                    $amount = null;
                }

                OrderDocument::query()->create(array_merge($row['payload'], [
                    'amount' => $amount,
                ]));
            }

            foreach ($estimate->addOns as $addOn) {
                OrderAddOn::query()->create([
                    'order_id' => $order->id,
                    'add_on_id' => $addOn->add_on_id,
                    'name' => $addOn->name,
                    'amount' => $addOn->amount,
                    'meta' => [
                        'pricing_mode' => $addOn->pricing_mode,
                        'unit_amount' => $addOn->unit_amount,
                        'quantity' => $addOn->quantity,
                    ],
                ]);
            }

            OrderEvent::query()->create([
                'order_id' => $order->id,
                'type' => 'created',
                'actor_type' => 'user',
                'actor_id' => $user->id,
                'payload' => [
                    'payment_mode' => $paymentMode,
                    'assignment_mode' => $assignmentMode,
                    'pay_with_plan' => $payWithPlan,
                    'documents_count' => count($documents),
                ],
            ]);

            $estimate->markConverted($order->id);

            $payment = null;

            if ($payWithPlan && $subscription) {
                $this->deductPlanUsage($subscription, $order, $estimate);
                $order->forceFill([
                    'status' => Order::STATUS_OPEN,
                    'paid_at' => now(),
                ])->save();
            } elseif ($paymentMode === 'quick') {
                $payment = $this->startGatewayPayment($order);
            }

            return [
                'order' => $order->fresh(['addOns', 'documents']),
                'payment' => $payment,
                'user' => $user,
            ];
        });

        $this->notifier->orderCreated($result['order']);

        if (! empty($result['order']->paid_at)) {
            $this->notifier->orderPaid($result['order']);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function resolveEstimate(array $input): Estimate
    {
        $query = Estimate::query()->with('addOns');

        if (! empty($input['estimate_id'])) {
            $estimate = $query->whereKey($input['estimate_id'])->first();
        } elseif (! empty($input['estimate_uuid'])) {
            $estimate = $query->where('uuid', $input['estimate_uuid'])->first();
        } else {
            $estimate = null;
        }

        if (! $estimate) {
            throw ValidationException::withMessages([
                'estimate' => [__('general.estimate_not_found')],
            ]);
        }

        if ($estimate->status === Estimate::STATUS_CONVERTED) {
            throw ValidationException::withMessages([
                'estimate' => [__('general.estimate_already_converted')],
            ]);
        }

        return $estimate;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    protected function ensureIndividualUser(array $input): User
    {
        $existing = User::query()->where('email', $input['email'])->first();
        if ($existing) {
            return $existing;
        }

        $phone = isset($input['phone']) && is_string($input['phone']) && trim($input['phone']) !== ''
            ? trim($input['phone'])
            : null;

        if ($phone !== null && User::query()->where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => [__('general.auth_phone_taken')],
            ]);
        }

        $password = $input['password'] ?? Str::password(12);

        return User::query()->create([
            'type' => User::TYPE_INDIVIDUAL,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'name' => trim($input['first_name'].' '.$input['last_name']),
            'email' => $input['email'],
            'phone' => $phone,
            'password' => $password,
            'is_active' => true,
        ]);
    }

    protected function initialStatus(string $paymentMode, bool $payWithPlan): string
    {
        if ($payWithPlan) {
            return Order::STATUS_OPEN;
        }

        return $paymentMode === 'quick'
            ? Order::STATUS_PENDING_PAYMENT
            : Order::STATUS_OPEN;
    }

    protected function assertPlanCoverage(User $user, Estimate $estimate): EnterpriseSubscription
    {
        $subscription = EnterpriseSubscription::query()
            ->where('user_id', $user->id)
            ->where('status', EnterpriseSubscription::STATUS_ACTIVE)
            ->latest('id')
            ->first();

        if (! $subscription) {
            throw ValidationException::withMessages([
                'pay_with_plan' => [__('general.order_no_active_subscription')],
            ]);
        }

        $pages = (int) ($estimate->page_count ?? 0);
        $words = (int) ($estimate->word_count ?? 0);

        if (($pages <= 0 && $words <= 0) || ! $subscription->canCover($pages, $words)) {
            throw ValidationException::withMessages([
                'pay_with_plan' => [__('general.order_insufficient_plan_quota')],
            ]);
        }

        return $subscription;
    }

    protected function deductPlanUsage(EnterpriseSubscription $subscription, Order $order, Estimate $estimate): void
    {
        $pages = (int) ($estimate->page_count ?? 0);
        $words = (int) ($estimate->word_count ?? 0);

        $subscription->pages_used = (int) $subscription->pages_used + $pages;
        $subscription->words_used = (int) $subscription->words_used + $words;

        if ($subscription->isExhausted()) {
            $subscription->status = EnterpriseSubscription::STATUS_EXHAUSTED;
        }
        $subscription->save();

        if ($pages > 0) {
            SubscriptionUsageEvent::query()->create([
                'enterprise_subscription_id' => $subscription->id,
                'order_id' => $order->id,
                'amount' => $pages,
                'quota_unit' => 'page',
                'type' => 'deduct',
            ]);
        }

        if ($words > 0) {
            SubscriptionUsageEvent::query()->create([
                'enterprise_subscription_id' => $subscription->id,
                'order_id' => $order->id,
                'amount' => $words,
                'quota_unit' => 'word',
                'type' => 'deduct',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function startGatewayPayment(Order $order): array
    {
        $gateway = $this->gateways->default();
        $returnUrl = url('/api/orders/payments/'.$gateway->driverName().'/return');
        $callbackUrl = url('/api/orders/payments/'.$gateway->driverName().'/callback');

        $result = $gateway->createPayment($order->loadMissing('customer'), $returnUrl, $callbackUrl);

        $order->forceFill([
            'payment_gateway_snapshot' => $gateway->driverName(),
            'payment_tran_ref' => $result['tran_ref'] ?? null,
            'payment_checkout_id' => $result['checkout_id'] ?? null,
            'payment_link_url' => $result['payment_link'] ?? $result['redirect_url'] ?? null,
        ])->save();

        return $result;
    }
}
