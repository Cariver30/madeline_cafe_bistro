<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\HostDashboardUpdated;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\TableSession;
use App\Models\User;
use App\Mail\PosReceiptMail;
use App\Support\CloverBillingClient;
use App\Support\CloverClient;
use App\Support\Loyalty\LoyaltyRewardService;
use App\Support\Orders\PosReceiptBuilder;
use App\Support\Orders\TableOrderService;
use App\Support\TableTurnTimeEstimator;
use App\Support\Payments\ProcessorPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ServerTableSessionController extends Controller
{
    private array $cloverStateCache = [];
    public function availableServers()
    {
        $servers = User::where('role', 'server')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active' => (bool) $user->active,
            ]);

        return response()->json([
            'servers' => $servers,
        ]);
    }

    public function store(Request $request)
    {
        $server = $request->user();

        $validator = Validator::make($request->all(), [
            'dining_table_id' => ['nullable', 'integer', Rule::exists('dining_tables', 'id')],
            'table_label' => ['required_without:dining_table_id', 'string', 'max:255'],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:255'],
            'order_mode' => ['nullable', 'string', 'in:traditional,table'],
        ], [
            'table_label.required' => 'La mesa es obligatoria.',
            'party_size.required' => 'La cantidad de personas es obligatoria.',
            'guest_name.required' => 'El nombre es obligatorio.',
            'guest_email.required' => 'El correo es obligatorio.',
            'guest_email.email' => 'El formato del correo no es válido.',
            'guest_phone.required' => 'El teléfono es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos enviados.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $validator->validated();

        $table = null;
        if (! empty($validated['dining_table_id'])) {
            $table = DiningTable::query()
                ->with('activeAssignment.waitingListEntry')
                ->find($validated['dining_table_id']);

            if ($table) {
                $hasActiveSession = TableSession::where('dining_table_id', $table->id)
                    ->where('status', 'active')
                    ->exists();

                if ($hasActiveSession) {
                    return response()->json([
                        'message' => 'La mesa ya tiene una sesión activa.',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if (! in_array($table->status, ['available', 'reserved', 'occupied'], true)) {
                    return response()->json([
                        'message' => 'La mesa no está disponible.',
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }

        $waitingEntry = $table?->activeAssignment?->waitingListEntry;

        $session = TableSession::create([
            'server_id' => $server->id,
            'dining_table_id' => $table?->id,
            'waiting_list_entry_id' => $waitingEntry?->id,
            'table_label' => $table?->label ?? $validated['table_label'],
            'party_size' => $validated['party_size'],
            'guest_name' => $validated['guest_name'],
            'guest_email' => strtolower(trim($validated['guest_email'])),
            'guest_phone' => $validated['guest_phone'],
            'order_mode' => $validated['order_mode'] ?? 'table',
            'expires_at' => now()->addHour(),
            'seated_at' => now(),
        ]);

        if ($table) {
            $table->update(['status' => 'occupied']);
            event(new HostDashboardUpdated('tables', $table->id));
        }

        if ($waitingEntry && $waitingEntry->status !== 'seated') {
            $waitingEntry->update([
                'status' => 'seated',
                'seated_at' => $waitingEntry->seated_at ?? now(),
            ]);
        }

        return response()->json([
            'message' => 'Mesa creada correctamente.',
            'qr_url' => route('table.order.show', $session->qr_token),
            'session' => $this->formatSession($session->fresh()),
        ], Response::HTTP_CREATED);
    }

    public function active(Request $request)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        $this->expireSessions($isManager ? null : $server->id);

        $sessions = TableSession::with(['server', 'orders.batches.items.extras', 'orders.batches.items.prepLabels.area', 'openOrder.payments'])
            ->when(!$isManager, fn ($query) => $query->where('server_id', $server->id))
            ->whereIn('status', ['active', 'expired'])
            ->orderByDesc('created_at')
            ->get();

        $this->queueCloverSync($request, $sessions->pluck('orders')->flatten()->pluck('batches')->flatten());

        $now = now();
        foreach ($sessions as $session) {
            $hasActiveBatch = $session->orders
                ->flatMap(fn (Order $order) => $order->batches)
                ->contains(function (OrderBatch $batch) {
                    if (! in_array($batch->status, ['pending', 'confirmed'], true)) {
                        return false;
                    }
                    if ($batch->cancelled_at || $batch->metered_closed_at) {
                        return false;
                    }
                    if ($batch->source === 'server' && ! $batch->clover_order_id) {
                        return false;
                    }
                    return true;
                });

            $isExpired = $session->expires_at && $session->expires_at->lte($now);

            if (! $hasActiveBatch && $isExpired && $session->status !== 'closed') {
                $session->update([
                    'status' => 'closed',
                    'closed_at' => $session->closed_at ?? $now,
                ]);
                if ($session->diningTable) {
                    $session->diningTable->update(['status' => 'dirty']);
                    event(new HostDashboardUpdated('tables', $session->diningTable->id));
                }
            }
        }

        return response()->json([
            'sessions' => $sessions
                ->filter(fn (TableSession $session) => $session->status === 'active')
                ->map(fn (TableSession $session) => $this->formatSession($session, true))
                ->values(),
        ]);
    }

    public function show(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        $this->expireSessions($isManager ? null : $server->id);

        $tableSession->load(['server', 'orders.batches.items.extras', 'orders.batches.items.prepLabels.area', 'openOrder.payments']);

        $this->queueCloverSync($request, $tableSession->orders->pluck('batches')->flatten());

        return response()->json([
            'session' => $this->formatSession($tableSession, true),
        ]);
    }

    public function orders(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        $orders = $tableSession->orders()
            ->with(['batches.items.extras', 'batches.items.prepLabels.area'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'orders' => $this->formatBatches($orders),
        ]);
    }

    public function storeOrder(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        if ($tableSession->status === 'closed') {
            return response()->json([
                'message' => 'La mesa ya fue cerrada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($tableSession->expires_at && $tableSession->expires_at->isPast()) {
            $tableSession->update(['status' => 'expired']);
            return response()->json([
                'message' => 'El QR expiró. Renueva la mesa para continuar.',
            ], Response::HTTP_GONE);
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
            'items.required' => 'Debes agregar al menos un plato.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa la orden enviada.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $validator->validated();
        Log::info('server_order_payload', [
            'user_id' => $server?->id,
            'session_id' => $tableSession->id,
            'items_count' => count($payload['items'] ?? []),
            'items' => array_map(static function (array $item): array {
                return [
                    'type' => $item['type'] ?? null,
                    'id' => $item['id'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'extras' => array_map(static fn ($extra) => [
                        'id' => $extra['id'] ?? null,
                        'quantity' => $extra['quantity'] ?? null,
                    ], $item['extras'] ?? []),
                ];
            }, $payload['items'] ?? []),
        ]);

        try {
            $batch = app(TableOrderService::class)->createBatch(
                $tableSession,
                $payload['items'],
                'server',
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
            'message' => 'Orden creada correctamente.',
            'order_id' => $batch->order_id,
            'batch_id' => $batch->id,
        ], Response::HTTP_CREATED);
    }

    public function renew(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        if ($tableSession->status === 'closed') {
            return response()->json([
                'message' => 'La mesa ya fue cerrada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tableSession->update([
            'status' => 'active',
            'expires_at' => now()->addHour(),
            'closed_at' => null,
        ]);

        return response()->json([
            'message' => 'Mesa renovada.',
            'session' => $this->formatSession($tableSession->fresh()),
        ]);
    }

    public function close(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        $validator = Validator::make($request->all(), [
            'tip' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa el monto de la propina.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tipTotal = $validator->validated()['tip'] ?? null;
        $tipTotal = $tipTotal !== null ? round((float) $tipTotal, 2) : null;

        if ($tableSession->status !== 'closed') {
            $tableSession->update([
                'status' => 'closed',
                'closed_at' => now(),
                'paid_at' => $tableSession->paid_at ?? now(),
            ]);
        }

        if ($tableSession->open_order_id) {
            $openOrder = $tableSession->openOrder;
            if ($openOrder && $openOrder->status === 'pending') {
                $openOrder->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'tip_total' => $tipTotal,
                ]);
            } elseif ($openOrder && $tipTotal !== null) {
                $openOrder->update(['tip_total' => $tipTotal]);
            }
            $tableSession->update(['open_order_id' => null]);
        }

        if (!$tableSession->loyalty_visit_id) {
            $visit = app(LoyaltyRewardService::class)->awardFromTableSession($tableSession);
            if ($visit) {
                $tableSession->update(['loyalty_visit_id' => $visit->id]);
            }
        }

        if ($tableSession->diningTable) {
            $tableSession->diningTable->update(['status' => 'dirty']);
            event(new HostDashboardUpdated('tables', $tableSession->diningTable->id));
            $this->clearWaitingListEntryForTable($tableSession->diningTable);
        }

        return response()->json([
            'message' => 'Mesa cerrada.',
            'session' => $this->formatSession($tableSession->fresh()),
        ]);
    }

    public function pay(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

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
                'message' => 'No hay una orden abierta para cobrar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->loadMissing(['items.extras', 'batches', 'payments']);
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

        $this->createManualPayment(
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

            $receiptUrl = $this->sendReceiptMail($tableSession, $order, 'paid');
        }

        if (!$tableSession->loyalty_visit_id) {
            $visit = app(LoyaltyRewardService::class)->awardFromTableSession($tableSession);
            if ($visit) {
                $tableSession->update(['loyalty_visit_id' => $visit->id]);
            }
        }

        if ($tableSession->diningTable) {
            $tableSession->diningTable->update(['status' => 'dirty']);
        }

        return response()->json([
            'message' => $summary['is_paid'] ? 'Mesa cobrada.' : 'Pago registrado.',
            'session' => $this->formatSession($tableSession->fresh(['openOrder.payments'])),
            'summary' => $summary,
            'receipt_url' => $receiptUrl,
        ]);
    }

    public function confirmExternalPayment(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

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
                'message' => 'No hay una orden abierta para cobrar.',
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

        $order->loadMissing(['items.extras', 'batches', 'payments']);
        $totals = PosReceiptBuilder::calculateTotals($order);
        $subtotal = $totals['subtotal'];
        $taxTotal = $totals['tax_total'];
        $tipTotal = $data['tip'] ?? null;
        $tipTotal = $tipTotal !== null ? round((float) $tipTotal, 2) : null;

        $this->createManualPayment(
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

        if (!$tableSession->loyalty_visit_id) {
            $visit = app(LoyaltyRewardService::class)->awardFromTableSession($tableSession);
            if ($visit) {
                $tableSession->update(['loyalty_visit_id' => $visit->id]);
            }
        }

        if ($tableSession->diningTable) {
            $tableSession->diningTable->update(['status' => 'dirty']);
        }

        return response()->json([
            'message' => $summary['is_paid'] ? 'Mesa cobrada.' : 'Pago registrado.',
            'session' => $this->formatSession($tableSession->fresh(['openOrder.payments'])),
            'summary' => $summary,
            'receipt_url' => $receiptUrl,
        ]);
    }

    public function addPayment(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

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
                'message' => 'No hay una orden abierta para cobrar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order->loadMissing(['items.extras', 'batches', 'payments']);
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

        $this->createManualPayment(
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
            'message' => 'Pago registrado.',
            'summary' => $summary,
            'receipt_url' => $receiptUrl,
        ]);
    }

    public function sendReceipt(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        $order = $tableSession->openOrder;
        if (!$order) {
            return response()->json([
                'message' => 'No hay una orden abierta para cobrar.',
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

    public function transfer(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;
        $requiresOverride = ! $isManager;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        if ($tableSession->status === 'closed') {
            return response()->json([
                'message' => 'La mesa ya esta cerrada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validator = Validator::make($request->all(), [
            'dining_table_id' => ['nullable', 'integer', Rule::exists('dining_tables', 'id')],
            'table_label' => ['required_without:dining_table_id', 'string', 'max:255'],
            'server_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'manager_email' => [$requiresOverride ? 'required' : 'nullable', 'email', 'max:255'],
            'manager_password' => [$requiresOverride ? 'required' : 'nullable', 'string'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Revisa los datos.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $validator->validated();
        $authorizedManager = null;
        if ($requiresOverride) {
            $authorizedManager = User::where('email', strtolower(trim($data['manager_email'])))
                ->where('role', 'manager')
                ->where('active', true)
                ->first();

            if (! $authorizedManager || ! Hash::check($data['manager_password'], $authorizedManager->password)) {
                return response()->json([
                    'message' => 'Autorización de gerente inválida.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $targetServer = User::where('id', $data['server_id'])
            ->where('role', 'server')
            ->where('active', true)
            ->first();

        if (!$targetServer) {
            return response()->json([
                'message' => 'Selecciona un mesero activo.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($targetServer->id === $tableSession->server_id) {
            return response()->json([
                'message' => 'Selecciona otro mesero para transferir.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!empty($data['server_id']) && ! $isManager && ! $authorizedManager) {
            return response()->json([
                'message' => 'Solo un gerente puede transferir a otro mesero.',
            ], Response::HTTP_FORBIDDEN);
        }

        $targetTable = null;
        if (! empty($data['dining_table_id'])) {
            $targetTable = DiningTable::find($data['dining_table_id']);
            if ($targetTable && ! in_array($targetTable->status, ['available', 'reserved'], true)) {
                return response()->json([
                    'message' => 'La mesa seleccionada no está disponible.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $previousTable = $tableSession->diningTable;

        $tableSession->update([
            'dining_table_id' => $targetTable?->id,
            'table_label' => $targetTable?->label ?? trim($data['table_label']),
            'server_id' => $data['server_id'],
        ]);

        if ($previousTable && $previousTable->id !== $targetTable?->id) {
            $previousTable->update(['status' => 'dirty']);
        }
        if ($targetTable) {
            $targetTable->update(['status' => 'occupied']);
        }

        return response()->json([
            'message' => 'Mesa transferida.',
            'session' => $this->formatSession($tableSession->fresh(['server', 'openOrder.payments'])),
        ]);
    }

    private function expireSessions(?int $serverId = null): void
    {
        $query = TableSession::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now());

        if ($serverId) {
            $query->where('server_id', $serverId);
        }

        $query->update(['status' => 'expired']);
    }

    private function queueCloverSync(Request $request, $batches): void
    {
        if (! $request->boolean('clover_live')) {
            return;
        }

        if (! config('services.clover.live_metrics', false)) {
            return;
        }

        $batchIds = collect($batches)
            ->filter(fn (OrderBatch $batch) => $batch->id && $batch->clover_order_id && ! $batch->metered_closed_at)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();

        if ($batchIds === []) {
            return;
        }

        $lockKey = 'clover_sync_lock';
        $lockTtl = (int) env('CLOVER_SYNC_LOCK_SECONDS', 8);

        if (! Cache::add($lockKey, true, now()->addSeconds($lockTtl))) {
            return;
        }

        app()->terminating(function () use ($batchIds, $lockKey) {
            try {
                $batches = OrderBatch::whereIn('id', $batchIds)->get();
                $this->syncCloverClosedBatches($batches);
            } finally {
                Cache::forget($lockKey);
            }
        });
    }

    private function syncCloverClosedBatches($batches): void
    {
        if (! request()->boolean('clover_live')) {
            return;
        }

        if (! config('services.clover.live_metrics', false)) {
            return;
        }

        $batches = collect($batches)
            ->filter(fn (OrderBatch $batch) => $batch->clover_order_id && ! $batch->metered_closed_at)
            ->values();

        if ($batches->isEmpty()) {
            return;
        }

        $settings = Setting::first();
        $client = CloverClient::fromSettings($settings);
        if (! $client) {
            return;
        }

        $billingClient = CloverBillingClient::fromSettings($settings);
        $eventId = config('services.clover.metered_event_id');
        $merchantId = $settings?->clover_merchant_id;

        foreach ($batches as $batch) {
            try {
                $cloverOrder = $client->getOrder($batch->clover_order_id, '');
            } catch (Throwable $exception) {
                if ($this->isCloverNotFound($exception)) {
                    $batch->update([
                        'status' => 'cancelled',
                        'cancelled_at' => $batch->cancelled_at ?? now(),
                        'clover_order_id' => null,
                        'clover_print_event_id' => null,
                    ]);
                    $this->closeSessionIfCompleted($batch);
                    continue;
                }
                report($exception);
                continue;
            }

            $state = data_get($cloverOrder, 'state');
            $totalPaid = (int) data_get($cloverOrder, 'totalPaid', 0);
            $isClosed = ($state && $state !== 'open') || $totalPaid > 0;

            if (! $isClosed) {
                continue;
            }

            $batch->update([
                'metered_closed_at' => now(),
            ]);

            $this->closeSessionIfCompleted($batch);

            if ($billingClient && $eventId && $merchantId) {
                try {
                    $billingClient->reportEvent($eventId, $merchantId, 1);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }
        }
    }


    private function closeSessionIfCompleted(OrderBatch $batch): void
    {
        $batch->loadMissing(['order.tableSession.orders.batches']);
        $session = $batch->order?->tableSession;

        if (! $session || $session->status === 'closed') {
            return;
        }

        $hasActiveBatch = $session->orders
            ->flatMap(fn (Order $order) => $order->batches)
            ->contains(function (OrderBatch $batch) {
                if (! in_array($batch->status, ['pending', 'confirmed'], true)) {
                    return false;
                }
                if ($batch->cancelled_at || $batch->metered_closed_at) {
                    return false;
                }
                if ($batch->source === 'server' && ! $batch->clover_order_id) {
                    return false;
                }
                return true;
            });

        if (! $hasActiveBatch) {
            $session->update([
                'status' => 'closed',
                'closed_at' => $session->closed_at ?? now(),
                'paid_at' => $session->paid_at ?? now(),
            ]);
            if ($session->diningTable) {
                $session->diningTable->update(['status' => 'dirty']);
                event(new HostDashboardUpdated('tables', $session->diningTable->id));
                $this->clearWaitingListEntryForTable($session->diningTable);
            }
        }
    }

    private function clearWaitingListEntryForTable(?DiningTable $table): void
    {
        if (! $table) {
            return;
        }

        $assignment = $table->activeAssignment()
            ->with(['waitingListEntry.assignments.diningTable'])
            ->first();

        if (! $assignment || ! $assignment->waitingListEntry) {
            return;
        }

        $entry = $assignment->waitingListEntry;
        if ($entry->status !== 'seated') {
            return;
        }

        $assignments = $entry->assignments()->whereNull('released_at')->get();
        foreach ($assignments as $entryAssignment) {
            $entryAssignment->update(['released_at' => now()]);
            if ($entryAssignment->diningTable && $entryAssignment->diningTable->status === 'reserved') {
                $entryAssignment->diningTable->update(['status' => 'available']);
                event(new HostDashboardUpdated('tables', $entryAssignment->diningTable->id));
            }
        }

        $entryId = $entry->id;
        $entry->delete();
        event(new HostDashboardUpdated('waiting_list', $entryId));
    }

    private function formatSession(TableSession $session, bool $withOrders = false): array
    {
        $seatedAt = $session->seated_at ?? $session->created_at;
        $clockEnd = $session->closed_at ?? now();
        $elapsedMinutes = $seatedAt ? $clockEnd->diffInMinutes($seatedAt) : null;
        $estimatedTurn = TableTurnTimeEstimator::estimateTurnMinutes($session->party_size);
        $remainingMinutes = $elapsedMinutes !== null
            ? max($estimatedTurn - $elapsedMinutes, 0)
            : null;
        $elapsedSinceFirstOrder = $session->first_order_at
            ? $clockEnd->diffInMinutes($session->first_order_at)
            : null;

        $payload = [
            'id' => $session->id,
            'open_order_id' => $session->open_order_id,
            'server_id' => $session->server_id,
            'server_name' => $session->server?->name,
            'table_label' => $session->table_label,
            'dining_table_id' => $session->dining_table_id,
            'waiting_list_entry_id' => $session->waiting_list_entry_id,
            'dining_table' => $session->diningTable ? [
                'id' => $session->diningTable->id,
                'label' => $session->diningTable->label,
                'capacity' => $session->diningTable->capacity,
                'section' => $session->diningTable->section,
                'status' => $session->diningTable->status,
            ] : null,
            'party_size' => $session->party_size,
            'guest_name' => $session->guest_name,
            'guest_email' => $session->guest_email,
            'guest_phone' => $session->guest_phone,
            'loyalty_visit_id' => $session->loyalty_visit_id,
            'order_mode' => $session->order_mode ?? 'table',
            'service_channel' => $session->service_channel ?? 'table',
            'status' => $session->status,
            'seated_at' => optional($session->seated_at)->toIso8601String(),
            'first_order_at' => optional($session->first_order_at)->toIso8601String(),
            'paid_at' => optional($session->paid_at)->toIso8601String(),
            'expires_at' => optional($session->expires_at)->toIso8601String(),
            'closed_at' => optional($session->closed_at)->toIso8601String(),
            'timeclock' => [
                'elapsed_minutes' => $elapsedMinutes,
                'estimated_turn_minutes' => $estimatedTurn,
                'remaining_minutes' => $remainingMinutes,
                'elapsed_since_first_order_minutes' => $elapsedSinceFirstOrder,
            ],
            'qr_url' => route('table.order.show', $session->qr_token),
            'created_at' => optional($session->created_at)->toIso8601String(),
        ];

        if ($withOrders) {
            if ($session->openOrder) {
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
            }
            $cloverStates = $this->resolveCloverStates($session->orders);
            $payload['orders'] = $this->formatBatches($session->orders, $cloverStates);
        }

        return $payload;
    }

    private function formatBatches($orders, array $cloverStates = []): array
    {
        return $orders
            ->flatMap(fn (Order $order) => $order->batches)
            ->filter(function (OrderBatch $batch) {
                if (! in_array($batch->status, ['pending', 'confirmed'], true)) {
                    return false;
                }
                if ($batch->cancelled_at || $batch->metered_closed_at) {
                    return false;
                }
                if ($batch->source === 'server' && ! $batch->clover_order_id) {
                    return false;
                }
                return true;
            })
            ->map(fn (OrderBatch $batch) => $this->formatBatch($batch, $cloverStates))
            ->sortByDesc(fn ($batch) => $batch['created_at'] ?? '')
            ->values()
            ->all();
    }

    private function formatBatch($batch, array $cloverStates = []): array
    {
        $items = $batch->items->filter(fn ($item) => !$item->voided_at);
        $cloverInfo = $batch->clover_order_id ? ($cloverStates[$batch->clover_order_id] ?? null) : null;

        return [
            'id' => $batch->id,
            'order_id' => $batch->order_id,
            'source' => $batch->source,
            'status' => $batch->status,
            'clover_order_id' => $batch->clover_order_id,
            'clover_status' => $cloverInfo['state'] ?? null,
            'clover_total_paid' => $cloverInfo['total_paid'] ?? null,
            'created_at' => optional($batch->created_at)->toIso8601String(),
            'confirmed_at' => optional($batch->confirmed_at)->toIso8601String(),
            'cancelled_at' => optional($batch->cancelled_at)->toIso8601String(),
            'metered_closed_at' => optional($batch->metered_closed_at)->toIso8601String(),
            'items' => $items->map(function ($item) {
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

    private function resolveCloverStates($orders): array
    {
        $liveMetrics = config('services.clover.live_metrics', false) && request()->boolean('clover_live');
        if (! $liveMetrics) {
            return [];
        }

        $batches = collect($orders)
            ->flatMap(fn (Order $order) => $order->batches)
            ->filter(fn (OrderBatch $batch) => (bool) $batch->clover_order_id)
            ->values();

        if ($batches->isEmpty()) {
            return [];
        }

        $batchesByCloverId = $batches->groupBy('clover_order_id');
        $settings = Setting::first();
        $client = CloverClient::fromSettings($settings);
        if (! $client) {
            return [];
        }

        $states = [];
        foreach ($batchesByCloverId as $cloverOrderId => $batchGroup) {
            if (isset($this->cloverStateCache[$cloverOrderId])) {
                $states[$cloverOrderId] = $this->cloverStateCache[$cloverOrderId];
                continue;
            }

            try {
                $cloverOrder = $client->getOrder($cloverOrderId, '');
            } catch (Throwable $exception) {
                if ($this->isCloverNotFound($exception)) {
                    foreach ($batchGroup as $batch) {
                        $batch->update([
                            'clover_order_id' => null,
                            'clover_print_event_id' => null,
                        ]);
                    }
                    continue;
                }
                report($exception);
                continue;
            }

            $payload = [
                'state' => data_get($cloverOrder, 'state'),
                'total_paid' => (int) data_get($cloverOrder, 'totalPaid', 0),
            ];

            $this->cloverStateCache[$cloverOrderId] = $payload;
            $states[$cloverOrderId] = $payload;
        }

        return $states;
    }

    private function isCloverNotFound(Throwable $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, '(404)')
            || str_contains($message, 'Order not found')
            || str_contains($message, 'Not Found');
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

    private function normalizeOrderItems(array $items): array
    {
        return array_map(function (array $item): array {
            $note = $item['notes'] ?? $item['note'] ?? null;
            if (is_string($note)) {
                $note = trim($note);
            } else {
                $note = null;
            }
            $item['notes'] = $note ?: null;
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
}
