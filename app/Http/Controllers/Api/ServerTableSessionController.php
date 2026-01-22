<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\TableSession;
use App\Models\User;
use App\Mail\PosReceiptMail;
use App\Support\Loyalty\LoyaltyRewardService;
use App\Support\Orders\PosReceiptBuilder;
use App\Support\Orders\TableOrderService;
use App\Support\Payments\ProcessorPayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ServerTableSessionController extends Controller
{
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
            'table_label' => ['required', 'string', 'max:255'],
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

        $session = TableSession::create([
            'server_id' => $server->id,
            'table_label' => $validated['table_label'],
            'party_size' => $validated['party_size'],
            'guest_name' => $validated['guest_name'],
            'guest_email' => strtolower(trim($validated['guest_email'])),
            'guest_phone' => $validated['guest_phone'],
            'order_mode' => $validated['order_mode'] ?? 'table',
            'expires_at' => now()->addHour(),
        ]);

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

        return response()->json([
            'sessions' => $sessions->map(fn (TableSession $session) => $this->formatSession($session, true)),
        ]);
    }

    public function show(Request $request, TableSession $tableSession)
    {
        $server = $request->user();
        $isManager = $server?->isManager() ?? false;

        abort_unless($isManager || $tableSession->server_id === $server->id, Response::HTTP_FORBIDDEN);

        $this->expireSessions($isManager ? null : $server->id);

        $tableSession->load(['server', 'orders.batches.items.extras', 'orders.batches.items.prepLabels.area', 'openOrder.payments']);

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

        try {
            $batch = app(TableOrderService::class)->createBatch(
                $tableSession,
                $validator->validated()['items'],
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
            'table_label' => ['required', 'string', 'max:255'],
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

        $tableSession->update([
            'table_label' => trim($data['table_label']),
            'server_id' => $data['server_id'],
        ]);

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

    private function formatSession(TableSession $session, bool $withOrders = false): array
    {
        $payload = [
            'id' => $session->id,
            'open_order_id' => $session->open_order_id,
            'server_id' => $session->server_id,
            'server_name' => $session->server?->name,
            'table_label' => $session->table_label,
            'party_size' => $session->party_size,
            'guest_name' => $session->guest_name,
            'guest_email' => $session->guest_email,
            'guest_phone' => $session->guest_phone,
            'loyalty_visit_id' => $session->loyalty_visit_id,
            'order_mode' => $session->order_mode ?? 'table',
            'service_channel' => $session->service_channel ?? 'table',
            'status' => $session->status,
            'expires_at' => optional($session->expires_at)->toIso8601String(),
            'closed_at' => optional($session->closed_at)->toIso8601String(),
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
            $payload['orders'] = $this->formatBatches($session->orders);
        }

        return $payload;
    }

    private function formatBatches($orders): array
    {
        return $orders
            ->flatMap(fn (Order $order) => $order->batches->map(fn ($batch) => $this->formatBatch($batch)))
            ->sortByDesc(fn ($batch) => $batch['created_at'] ?? '')
            ->values()
            ->all();
    }

    private function formatBatch($batch): array
    {
        $items = $batch->items->filter(fn ($item) => !$item->voided_at);

        return [
            'id' => $batch->id,
            'order_id' => $batch->order_id,
            'status' => $batch->status,
            'created_at' => optional($batch->created_at)->toIso8601String(),
            'confirmed_at' => optional($batch->confirmed_at)->toIso8601String(),
            'cancelled_at' => optional($batch->cancelled_at)->toIso8601String(),
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
