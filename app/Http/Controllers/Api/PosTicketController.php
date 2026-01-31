<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TableSession;
use App\Mail\PosReceiptMail;
use App\Support\Orders\PosReceiptBuilder;
use App\Support\Orders\TableOrderService;
use App\Support\Payments\ProcessorPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PosTicketController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        $sessions = TableSession::with(['openOrder.batches.items.extras', 'openOrder.batches.items.prepLabels.area', 'openOrder.payments'])
            ->when(! $isManager, fn ($query) => $query->where('server_id', $user->id))
            ->whereIn('status', ['active'])
            ->whereIn('service_channel', ['walkin', 'phone'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'tickets' => $sessions->map(fn (TableSession $session) => $this->formatTicket($session, true)),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:walkin,phone'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'party_size' => ['nullable', 'integer', 'min:1', 'max:99'],
        ], [
            'type.required' => 'Selecciona el tipo de ticket.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos enviados.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $type = $data['type'];
        $defaultName = $type === 'phone' ? 'Telefono' : 'Walk-in';
        $guestName = $data['guest_name'] ?? $defaultName;
        $guestPhone = $data['guest_phone'] ?? 'N/A';
        $guestEmail = $data['guest_email'] ?? strtolower($defaultName) . '+pos@local';

        $session = TableSession::create([
            'server_id' => $user->id,
            'open_order_id' => null,
            'service_channel' => $type,
            'table_label' => strtoupper($type),
            'party_size' => $data['party_size'] ?? 1,
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => $guestPhone,
            'order_mode' => 'traditional',
            'status' => 'active',
            'expires_at' => null,
        ]);

        $order = Order::create([
            'table_session_id' => $session->id,
            'server_id' => $user->id,
            'status' => 'pending',
        ]);

        $session->update(['open_order_id' => $order->id]);

        return response()->json([
            'message' => 'Ticket creado.',
            'ticket' => $this->formatTicket($session->fresh(['openOrder.batches.items.extras', 'openOrder.batches.items.prepLabels.area', 'openOrder.payments']), true),
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, TableSession $tableSession)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless(in_array($tableSession->service_channel, ['walkin', 'phone'], true), Response::HTTP_FORBIDDEN);

        $tableSession->load(['openOrder.batches.items.extras', 'openOrder.batches.items.prepLabels.area', 'openOrder.payments']);

        return response()->json([
            'ticket' => $this->formatTicket($tableSession, true),
        ]);
    }

    public function addItems(Request $request, TableSession $tableSession)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless(in_array($tableSession->service_channel, ['walkin', 'phone'], true), Response::HTTP_FORBIDDEN);

        if ($tableSession->status === 'closed') {
            return response()->json([
                'message' => 'El ticket ya fue cerrado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request->merge([
            'items' => $this->normalizeOrderItems($request->input('items', [])),
        ]);

        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string', 'in:dish,cocktail,wine'],
            'items.*.id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'items.*.extras' => ['nullable', 'array'],
            'items.*.extras.*.id' => ['required', 'integer'],
            'items.*.extras.*.quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ], [
            'items.required' => 'Debes agregar al menos un item.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa la orden enviada.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $batch = app(TableOrderService::class)->createBatch(
                $tableSession,
                $validator->validated()['items'],
                'pos',
            );
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'extras_required') {
                return response()->json([
                    'message' => 'Selecciona las opciones requeridas antes de enviar.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($e->getMessage() === 'extras_max') {
                return response()->json([
                    'message' => 'Superaste el máximo permitido en una opción.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($e->getMessage() === 'item_unavailable') {
                return response()->json([
                    'message' => 'Uno de los productos ya no está disponible.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Items agregados.',
            'order_id' => $batch->order_id,
            'batch_id' => $batch->id,
        ], Response::HTTP_CREATED);
    }

    private function normalizeOrderItems(array $items): array
    {
        return array_map(function (array $item): array {
            $extras = $item['extras'] ?? [];
            if (!is_array($extras)) {
                $item['extras'] = [];
                return $item;
            }

            $normalized = [];
            foreach ($extras as $extra) {
                if (is_array($extra)) {
                    if (isset($extra['id'])) {
                        $normalized[] = [
                            'id' => (int) $extra['id'],
                            'quantity' => $extra['quantity'] ?? null,
                        ];
                        continue;
                    }
                    if (isset($extra['extra_id'])) {
                        $normalized[] = [
                            'id' => (int) $extra['extra_id'],
                            'quantity' => $extra['quantity'] ?? null,
                        ];
                    }
                    continue;
                }

                if (is_numeric($extra)) {
                    $normalized[] = ['id' => (int) $extra];
                }
            }

            $item['extras'] = $normalized;

            return $item;
        }, $items);
    }

    public function pay(Request $request, TableSession $tableSession)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless(in_array($tableSession->service_channel, ['walkin', 'phone'], true), Response::HTTP_FORBIDDEN);

        $validator = Validator::make($request->all(), [
            'method' => ['required', 'string', 'in:cash,card,ath,split,tap_to_pay'],
            'tip' => ['nullable', 'numeric', 'min:0'],
            'provider' => ['nullable', 'string', 'max:60'],
            'processor_status' => ['nullable', 'string'],
            'processor_meta' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Selecciona el método de pago.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = $tableSession->openOrder;
        if (!$order) {
            return response()->json([
                'message' => 'No hay ticket abierto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->loadMissing(['items.extras', 'batches', 'tableSession', 'server', 'payments']);
        $totals = PosReceiptBuilder::calculateTotals($order);
        $subtotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $data = $validator->validated();
        $tipTotal = $data['tip'] ?? null;
        $tipTotal = $tipTotal !== null ? round((float) $tipTotal, 2) : null;

        $processorStatus = $data['processor_status'] ?? null;
        if ($processorStatus && !in_array($processorStatus, ['approved', 'succeeded', 'paid'], true)) {
            return response()->json([
                'message' => 'El pago no fue aprobado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $provider = $data['provider'] ?? 'manual';
        $paymentMeta = ['split_mode' => 'full'];
        if (!empty($data['processor_meta'])) {
            $paymentMeta['processor'] = $data['processor_meta'];
        }

        $payment = $this->createManualPayment(
            $order,
            $request->user(),
            $data['method'],
            $subtotal,
            $taxTotal,
            $tipTotal,
            $paymentMeta,
            $provider,
        );

        $summary = $this->syncOrderPayments($order);
        $receiptUrl = null;

        if ($summary['is_paid']) {
            OrderBatch::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'paid_at' => now(),
            ]);

            $tableSession->update([
                'status' => 'closed',
                'closed_at' => now(),
                'open_order_id' => null,
            ]);

            $receipt = PosReceiptBuilder::build(
                $order->fresh(['items.extras', 'tableSession', 'server']),
                'paid',
            );
            $receiptUrl = URL::temporarySignedRoute(
                'receipts.pos.download',
                now()->addDays(2),
                ['order' => $order->id, 'stage' => 'paid'],
            );

            if ($this->shouldSendReceipt($tableSession)) {
                Mail::to($tableSession->guest_email)->send(
                    new PosReceiptMail($order, $receipt, $receiptUrl, 'paid'),
                );
            }
        }

        return response()->json([
            'message' => $summary['is_paid'] ? 'Ticket cobrado.' : 'Pago registrado.',
            'payment_id' => $payment->id,
            'summary' => $summary,
            'receipt_url' => $receiptUrl,
        ]);
    }

    public function confirmExternalPayment(Request $request, TableSession $tableSession)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless(in_array($tableSession->service_channel, ['walkin', 'phone'], true), Response::HTTP_FORBIDDEN);

        $validator = Validator::make($request->all(), [
            'method' => ['required', 'string', 'in:cash,card,ath,tap_to_pay'],
            'provider' => ['required', 'string', 'max:60'],
            'payload' => ['required', 'array'],
            'tip' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa el pago.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = $tableSession->openOrder;
        if (!$order) {
            return response()->json([
                'message' => 'No hay ticket abierto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $normalized = ProcessorPayload::normalize($data['provider'], $data['payload']);
        if ($normalized['status'] !== 'approved') {
            return response()->json([
                'message' => 'El pago no fue aprobado.',
                'status' => $normalized['status'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->loadMissing(['items.extras', 'batches', 'tableSession', 'server', 'payments']);
        $totals = PosReceiptBuilder::calculateTotals($order);
        $subtotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $tipTotal = $data['tip'] ?? null;
        $tipTotal = $tipTotal !== null ? round((float) $tipTotal, 2) : null;

        $payment = $this->createManualPayment(
            $order,
            $request->user(),
            $data['method'],
            $subtotal,
            $taxTotal,
            $tipTotal,
            [
                'split_mode' => 'full',
                'processor' => $normalized,
                'processor_amount' => $data['amount'] ?? null,
            ],
            $data['provider'],
        );

        $summary = $this->syncOrderPayments($order);
        $receiptUrl = null;

        if ($summary['is_paid']) {
            OrderBatch::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'paid_at' => now(),
            ]);

            $tableSession->update([
                'status' => 'closed',
                'closed_at' => now(),
                'open_order_id' => null,
            ]);

            $receiptUrl = $this->sendReceiptMail($tableSession, $order, 'paid');
        }

        return response()->json([
            'message' => $summary['is_paid'] ? 'Ticket cobrado.' : 'Pago registrado.',
            'payment_id' => $payment->id,
            'summary' => $summary,
            'receipt_url' => $receiptUrl,
        ]);
    }

    public function addPayment(Request $request, TableSession $tableSession)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless(in_array($tableSession->service_channel, ['walkin', 'phone'], true), Response::HTTP_FORBIDDEN);

        $validator = Validator::make($request->all(), [
            'method' => ['required', 'string', 'in:cash,card,ath,split'],
            'split_mode' => ['required', 'string', 'in:items,amount'],
            'items' => ['nullable', 'array'],
            'items.*' => ['integer'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'tip' => ['nullable', 'numeric', 'min:0'],
            'tip_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'provider' => ['nullable', 'string', 'max:60'],
            'processor_status' => ['nullable', 'string'],
            'processor_meta' => ['nullable', 'array'],
        ], [
            'split_mode.required' => 'Selecciona el tipo de split.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa el pago.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = $tableSession->openOrder;
        if (!$order) {
            return response()->json([
                'message' => 'No hay ticket abierto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->loadMissing(['items.extras', 'batches', 'tableSession', 'server', 'payments']);
        $totals = PosReceiptBuilder::calculateTotals($order);
        $subtotalTotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $totalWithTax = $totals['total'];
        $paidSummary = $this->summarizePayments($order);
        $remainingTotal = max($totalWithTax - $paidSummary['paid_total'], 0);

        if ($remainingTotal <= 0) {
            return response()->json([
                'message' => 'La cuenta ya está cubierta.',
                'summary' => $this->syncOrderPayments($order),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $splitMode = $data['split_mode'];
        $tipPercent = $data['tip_percent'] ?? null;
        $tipValue = $data['tip'] ?? null;

        $itemsPayload = [];
        $splitSubtotal = 0.0;
        $splitTaxTotal = 0.0;
        PosReceiptBuilder::primeTaxRelations($order);

        if ($splitMode === 'items') {
            $selectedIds = collect($data['items'] ?? [])->unique()->values();
            if ($selectedIds->isEmpty()) {
                return response()->json([
                    'message' => 'Selecciona al menos un item.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $paidItemIds = $this->paidItemIds($order);
            $items = $order->items->filter(fn (OrderItem $item) => ! $item->voided_at);
            $selectedItems = $items->whereIn('id', $selectedIds);

            if ($selectedItems->count() !== $selectedIds->count()) {
                return response()->json([
                    'message' => 'Algunos items ya no están disponibles.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            foreach ($selectedItems as $item) {
                if ($paidItemIds->contains($item->id)) {
                    return response()->json([
                        'message' => 'Uno de los items ya fue pagado.',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                $lineTotals = PosReceiptBuilder::calculateLineTotals($item);
                $splitSubtotal += $lineTotals['subtotal'];
                $splitTaxTotal += $lineTotals['tax_total'];
                $lineTotal = $lineTotals['subtotal'] + $lineTotals['tax_total'];
                $itemsPayload[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => (int) $item->quantity,
                    'line_total' => round($lineTotal, 2),
                ];
            }
        } else {
            $amount = $data['amount'] ?? null;
            if (!$amount) {
                return response()->json([
                    'message' => 'Ingresa un monto válido.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $splitTotal = round((float) $amount, 2);
            if ($totalWithTax > 0) {
                $ratio = min($splitTotal / $totalWithTax, 1);
                $splitSubtotal = round($subtotalTotal * $ratio, 2);
                $splitTaxTotal = round($taxTotal * $ratio, 2);
            } else {
                $splitSubtotal = $splitTotal;
            }
        }

        $splitTotal = round($splitSubtotal + $splitTaxTotal, 2);

        if ($splitTotal <= 0) {
            return response()->json([
                'message' => 'El monto a cobrar debe ser mayor a cero.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($splitTotal > $remainingTotal) {
            return response()->json([
                'message' => 'El monto excede lo pendiente.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($tipValue === null && $tipPercent !== null) {
            $tipValue = round($splitSubtotal * ((float) $tipPercent / 100), 2);
        }
        $tipValue = $tipValue !== null ? round((float) $tipValue, 2) : 0.0;

        $processorStatus = $data['processor_status'] ?? null;
        if ($processorStatus && !in_array($processorStatus, ['approved', 'succeeded', 'paid'], true)) {
            return response()->json([
                'message' => 'El pago no fue aprobado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $provider = $data['provider'] ?? 'manual';
        $paymentMeta = [
            'split_mode' => $splitMode,
            'items' => $itemsPayload,
            'tip_percent' => $tipPercent,
        ];
        if (!empty($data['processor_meta'])) {
            $paymentMeta['processor'] = $data['processor_meta'];
        }

        $payment = $this->createManualPayment(
            $order,
            $request->user(),
            $data['method'],
            $splitSubtotal,
            $splitTaxTotal,
            $tipValue,
            $paymentMeta,
            $provider,
        );

        $summary = $this->syncOrderPayments($order);

        if ($summary['is_paid']) {
            OrderBatch::where('order_id', $order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                ]);

            $order->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'paid_at' => now(),
            ]);

            $tableSession->update([
                'status' => 'closed',
                'closed_at' => now(),
                'open_order_id' => null,
            ]);

            $receiptUrl = $this->sendReceiptMail($tableSession, $order, 'paid');
        }

        return response()->json([
            'message' => 'Pago registrado.',
            'payment_id' => $payment->id,
            'summary' => $summary,
            'receipt_url' => $receiptUrl ?? null,
        ]);
    }

    public function sendReceipt(Request $request, TableSession $tableSession)
    {
        $user = $request->user();
        $isManager = $user?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $user->id, Response::HTTP_FORBIDDEN);
        abort_unless(in_array($tableSession->service_channel, ['walkin', 'phone'], true), Response::HTTP_FORBIDDEN);

        $order = $tableSession->openOrder;
        if (!$order) {
            return response()->json([
                'message' => 'No hay ticket abierto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$this->shouldSendReceipt($tableSession)) {
            return response()->json([
                'message' => 'Sin email valido para enviar la cuenta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $receiptUrl = $this->sendReceiptMail($tableSession, $order, 'pre');

        return response()->json([
            'message' => 'Cuenta enviada.',
            'receipt_url' => $receiptUrl,
        ]);
    }

    private function formatTicket(TableSession $session, bool $withOrders = false): array
    {
        $payload = [
            'id' => $session->id,
            'ticket_id' => $session->open_order_id,
            'service_channel' => $session->service_channel,
            'status' => $session->status,
            'guest_name' => $session->guest_name,
            'guest_phone' => $session->guest_phone,
            'guest_email' => $session->guest_email,
            'party_size' => $session->party_size,
            'created_at' => optional($session->created_at)->toIso8601String(),
        ];

        if ($withOrders && $session->openOrder) {
            $summary = $this->summarizePayments($session->openOrder);
            $payload['payment_summary'] = [
                'subtotal' => $summary['subtotal'],
                'tax_total' => $summary['tax_total'],
                'total' => $summary['total'],
                'paid_subtotal' => $summary['paid_subtotal'],
                'paid_total' => $summary['paid_total'],
                'tip_total' => $summary['tip_total'],
                'balance' => $summary['balance'],
                'is_paid' => $summary['is_paid'],
            ];
            $payload['orders'] = $session->openOrder->batches->map(fn (OrderBatch $batch) => $this->formatBatch($batch));
        }

        return $payload;
    }

    private function formatBatch(OrderBatch $batch): array
    {
        $items = $batch->items->filter(fn ($item) => !$item->voided_at);

        return [
            'id' => $batch->id,
            'order_id' => $batch->order_id,
            'status' => $batch->status,
            'created_at' => optional($batch->created_at)->toIso8601String(),
            'confirmed_at' => optional($batch->confirmed_at)->toIso8601String(),
            'cancelled_at' => optional($batch->cancelled_at)->toIso8601String(),
            'items' => $items->map(function (OrderItem $item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'notes' => $item->notes,
                    'category_scope' => $item->category_scope,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category_name,
                    'category_order' => $item->category_order,
                    'extras' => $item->extras->map(fn ($extra) => [
                        'id' => $extra->id,
                        'name' => $extra->name,
                        'group_name' => $extra->group_name,
                        'kind' => $extra->kind,
                        'price' => $extra->price,
                        'quantity' => $extra->quantity,
                    ]),
                    'labels' => $item->prepLabels->map(function ($label) {
                        return [
                            'id' => $label->id,
                            'name' => $label->name,
                            'area_id' => $label->prep_area_id,
                            'area_name' => $label->area?->name,
                            'status' => $label->pivot?->status ?? 'pending',
                            'prepared_at' => optional($label->pivot?->prepared_at)->toIso8601String(),
                            'ready_at' => optional($label->pivot?->ready_at)->toIso8601String(),
                            'delivered_at' => optional($label->pivot?->delivered_at)->toIso8601String(),
                        ];
                    }),
                ];
            }),
        ];
    }

    private function shouldSendReceipt(TableSession $session): bool
    {
        $email = trim((string) $session->guest_email);
        if ($email === '') {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return !str_ends_with($email, '+pos@local');
    }

    private function sendReceiptMail(TableSession $session, Order $order, string $stage): ?string
    {
        if (!$this->shouldSendReceipt($session)) {
            return null;
        }

        $order->loadMissing(['items.extras', 'tableSession', 'server']);
        $receipt = PosReceiptBuilder::build($order, $stage);
        $receiptUrl = URL::temporarySignedRoute(
            'receipts.pos.download',
            now()->addDays(2),
            ['order' => $order->id, 'stage' => $stage],
        );

        Mail::to($session->guest_email)->send(
            new PosReceiptMail($order, $receipt, $receiptUrl, $stage),
        );

        return $receiptUrl;
    }

    private function createManualPayment(Order $order, ?\App\Models\User $user, string $method, float $subtotal, float $taxTotal, ?float $tip, array $meta = [], string $provider = 'manual'): Payment
    {
        $amount = round($subtotal + $taxTotal + ($tip ?? 0), 2);

        $paymentMethod = $order->payment_method;
        if (!$paymentMethod) {
            $paymentMethod = $method;
        } elseif ($paymentMethod !== $method) {
            $paymentMethod = 'split';
        }

        $order->update([
            'payment_method' => $paymentMethod,
            'payment_status' => $order->payment_status ?? 'partial',
        ]);

        return Payment::create([
            'order_id' => $order->id,
            'created_by' => $user?->id,
            'provider' => $provider,
            'method' => $method,
            'amount' => $amount,
            'status' => 'succeeded',
            'meta' => array_merge([
                'subtotal' => round($subtotal, 2),
                'tax_total' => round($taxTotal, 2),
                'tip' => round((float) ($tip ?? 0), 2),
            ], $meta),
        ]);
    }

    private function summarizePayments(Order $order): array
    {
        $order->loadMissing('payments');
        $totals = PosReceiptBuilder::calculateTotals($order);
        $subtotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $total = $totals['total'];
        $paidSubtotal = 0.0;
        $paidTaxTotal = 0.0;
        $tipTotal = 0.0;

        foreach ($order->payments as $payment) {
            if (!in_array($payment->status, ['succeeded', 'paid'], true)) {
                continue;
            }
            $metaSubtotal = data_get($payment->meta, 'subtotal');
            $metaTax = data_get($payment->meta, 'tax_total', 0);
            $metaTip = data_get($payment->meta, 'tip', 0);

            if ($metaSubtotal === null) {
                $metaSubtotal = (float) $payment->amount;
            }

            $paidSubtotal += (float) $metaSubtotal;
            $paidTaxTotal += (float) ($metaTax ?? 0);
            $tipTotal += (float) ($metaTip ?? 0);
        }

        $paidSubtotal = round($paidSubtotal, 2);
        $paidTaxTotal = round($paidTaxTotal, 2);
        $tipTotal = round($tipTotal, 2);
        $paidTotal = round($paidSubtotal + $paidTaxTotal, 2);
        $balance = round(max($total - $paidTotal, 0), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'tax_total' => round($taxTotal, 2),
            'total' => round($total, 2),
            'paid_subtotal' => $paidSubtotal,
            'paid_total' => $paidTotal,
            'tip_total' => $tipTotal,
            'balance' => $balance,
            'is_paid' => $paidTotal >= round($total, 2),
        ];
    }

    private function syncOrderPayments(Order $order): array
    {
        $summary = $this->summarizePayments($order);

        $order->update([
            'paid_total' => $summary['paid_total'],
            'tip_total' => $summary['tip_total'],
            'payment_status' => $summary['is_paid'] ? 'paid' : 'partial',
        ]);

        return $summary;
    }

    private function paidItemIds(Order $order)
    {
        $paid = collect();
        foreach ($order->payments as $payment) {
            if (!in_array($payment->status, ['succeeded', 'paid'], true)) {
                continue;
            }
            $items = data_get($payment->meta, 'items', []);
            foreach ($items as $item) {
                $itemId = is_array($item) ? ($item['id'] ?? null) : $item;
                if ($itemId) {
                    $paid->push($itemId);
                }
            }
        }

        return $paid->unique()->values();
    }
}
