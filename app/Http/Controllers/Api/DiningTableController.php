<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\HostDashboardUpdated;
use App\Events\ServerSessionsUpdated;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderBatch;
use App\Models\Setting;
use App\Models\TableSession;
use App\Support\CloverClient;
use App\Support\TableTurnTimeEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DiningTableController extends Controller
{
    public function index(Request $request)
    {
        if ($request->boolean('clover_live', true)) {
            $this->syncCloverClosedSessions();
        }

        $query = DiningTable::query()->with(['activeAssignment.waitingListEntry', 'activeSession.server']);

        if ($request->filled('status')) {
            $statuses = collect(explode(',', $request->string('status')->toString()))
                ->map(fn ($status) => trim($status))
                ->filter();
            if ($statuses->isNotEmpty()) {
                $query->whereIn('status', $statuses->all());
            }
        }

        if ($request->filled('section')) {
            $query->where('section', $request->string('section')->toString());
        }

        $tables = $query
            ->orderBy('position')
            ->orderBy('label')
            ->get();

        $tableIds = $tables->pluck('id')->all();
        $groupSessions = TableSession::with(['server', 'tables'])
            ->where('status', 'active')
            ->whereHas('tables', fn ($q) => $q->whereIn('dining_tables.id', $tableIds))
            ->get();

        $groupSessionByTable = [];
        foreach ($groupSessions as $session) {
            foreach ($session->tables as $sessionTable) {
                $groupSessionByTable[$sessionTable->id] = $session;
            }
        }

        return response()->json([
            'tables' => $tables->map(fn (DiningTable $table) => $this->formatTable(
                $table,
                $groupSessionByTable[$table->id] ?? null,
            )),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255', 'unique:dining_tables,label'],
            'capacity' => ['required', 'integer', 'min:1', 'max:99'],
            'section' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])],
            'position' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $table = DiningTable::create($data);

        event(new HostDashboardUpdated('tables', $table->id));

        return response()->json([
            'message' => 'Mesa creada.',
            'table' => $this->formatTable($table->fresh()),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, DiningTable $diningTable)
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:255', Rule::unique('dining_tables', 'label')->ignore($diningTable->id)],
            'capacity' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'section' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])],
            'position' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $diningTable->update($data);

        event(new HostDashboardUpdated('tables', $diningTable->id));

        return response()->json([
            'message' => 'Mesa actualizada.',
            'table' => $this->formatTable($diningTable->fresh(['activeAssignment.waitingListEntry'])),
        ]);
    }

    public function updateStatus(Request $request, DiningTable $diningTable)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])],
        ]);

        $diningTable->update(['status' => $data['status']]);

        event(new HostDashboardUpdated('tables', $diningTable->id));

        return response()->json([
            'message' => 'Estado actualizado.',
            'table' => $this->formatTable($diningTable->fresh(['activeAssignment.waitingListEntry'])),
        ]);
    }

    public function destroy(DiningTable $diningTable)
    {
        $hasActiveSession = TableSession::where('status', 'active')
            ->where(function ($query) use ($diningTable) {
                $query
                    ->where('dining_table_id', $diningTable->id)
                    ->orWhereHas('tables', fn ($sub) => $sub->where('dining_tables.id', $diningTable->id));
            })
            ->exists();

        if ($hasActiveSession || $diningTable->assignments()->whereNull('released_at')->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una mesa con actividad activa.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $diningTable->delete();

        event(new HostDashboardUpdated('tables', $diningTable->id));

        return response()->json([
            'message' => 'Mesa eliminada.',
        ]);
    }

    private function formatTable(DiningTable $table, ?TableSession $overrideSession = null): array
    {
        $assignment = $table->activeAssignment;
        $session = $overrideSession ?? $table->activeSession;
        $seatedAt = $session?->seated_at ?? $session?->created_at;
        $elapsedMinutes = $this->safeMinutesDiff($seatedAt, now());
        $estimatedTurn = $session ? TableTurnTimeEstimator::estimateTurnMinutes($session->party_size) : null;
        $remainingMinutes = $estimatedTurn !== null && $elapsedMinutes !== null
            ? max($estimatedTurn - $elapsedMinutes, 0)
            : null;

        return [
            'id' => $table->id,
            'label' => $table->label,
            'capacity' => $table->capacity,
            'section' => $table->section,
            'status' => $table->status,
            'position' => $table->position,
            'notes' => $table->notes,
            'active_assignment' => $assignment ? [
                'id' => $assignment->id,
                'waiting_list_entry_id' => $assignment->waiting_list_entry_id,
                'assigned_at' => optional($assignment->assigned_at)->toIso8601String(),
                'entry' => $assignment->waitingListEntry ? [
                    'id' => $assignment->waitingListEntry->id,
                    'guest_name' => $assignment->waitingListEntry->guest_name,
                    'party_size' => $assignment->waitingListEntry->party_size,
                    'status' => $assignment->waitingListEntry->status,
                ] : null,
            ] : null,
            'active_session' => $session ? [
                'id' => $session->id,
                'server_id' => $session->server_id,
                'server_name' => $session->server?->name,
                'guest_name' => $session->guest_name,
                'party_size' => $session->party_size,
                'seated_at' => optional($session->seated_at ?? $session->created_at)->toIso8601String(),
                'first_order_at' => optional($session->first_order_at)->toIso8601String(),
                'closed_at' => optional($session->closed_at)->toIso8601String(),
                'elapsed_minutes' => $elapsedMinutes,
                'estimated_turn_minutes' => $estimatedTurn,
                'remaining_minutes' => $remainingMinutes,
            ] : null,
            'created_at' => optional($table->created_at)->toIso8601String(),
            'updated_at' => optional($table->updated_at)->toIso8601String(),
        ];
    }

    private function safeMinutesDiff($start, $end): ?int
    {
        if (! $start || ! $end) {
            return null;
        }

        $diffSeconds = $end->diffInSeconds($start, false);
        $diffSeconds = max($diffSeconds, 0);

        return (int) floor($diffSeconds / 60);
    }

    private function syncCloverClosedSessions(): void
    {
        $lockKey = 'clover_table_index_sync_lock';
        $lockTtl = (int) env('CLOVER_SYNC_LOCK_SECONDS', 8);
        if (! Cache::add($lockKey, true, now()->addSeconds($lockTtl))) {
            return;
        }

        try {
            $batches = OrderBatch::query()
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereNotNull('clover_order_id')
                ->whereNull('metered_closed_at')
                ->whereHas('order.tableSession', fn ($query) => $query->where('status', 'active'))
                ->limit(80)
                ->get();

            if ($batches->isEmpty()) {
                return;
            }

            $settings = Setting::first();
            $client = CloverClient::fromSettings($settings);
            if (! $client) {
                return;
            }

            foreach ($batches as $batch) {
                $cloverOrderId = trim((string) ($batch->clover_order_id ?? ''));
                if ($cloverOrderId === '') {
                    continue;
                }

                try {
                    $cloverOrder = $client->getOrder($cloverOrderId, '');
                } catch (Throwable $exception) {
                    if ($this->isCloverNotFound($exception)) {
                        $batch->update([
                            'status' => 'cancelled',
                            'cancelled_at' => $batch->cancelled_at ?? now(),
                            'clover_order_id' => null,
                            'clover_print_event_id' => null,
                        ]);
                        $this->closeSessionIfCompleted($batch);
                    }
                    continue;
                }

                $state = strtolower((string) data_get($cloverOrder, 'state', ''));
                $paymentState = strtolower((string) data_get($cloverOrder, 'paymentState', ''));
                $totalPaid = (int) data_get($cloverOrder, 'totalPaid', 0);
                $isClosed = $totalPaid > 0
                    || ($paymentState !== '' && ! in_array($paymentState, ['open', 'unpaid'], true))
                    || in_array($state, ['paid', 'closed'], true);

                if (! $isClosed) {
                    continue;
                }

                $batch->update([
                    'metered_closed_at' => now(),
                ]);

                $this->closeSessionIfCompleted($batch);
            }
        } finally {
            Cache::forget($lockKey);
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
            ->contains(function (OrderBatch $currentBatch) {
                if (! in_array($currentBatch->status, ['pending', 'confirmed'], true)) {
                    return false;
                }
                if ($currentBatch->cancelled_at || $currentBatch->metered_closed_at) {
                    return false;
                }
                if ($currentBatch->source === 'server' && ! $currentBatch->clover_order_id) {
                    return false;
                }
                return true;
            });

        if ($hasActiveBatch) {
            return;
        }

        $session->update([
            'status' => 'closed',
            'closed_at' => $session->closed_at ?? now(),
            'paid_at' => $session->paid_at ?? now(),
            'open_order_id' => null,
        ]);

        foreach ($this->resolveSessionTables($session) as $table) {
            if ($table->status !== 'available') {
                $table->update(['status' => 'available']);
            }
            $table->assignments()->whereNull('released_at')->update(['released_at' => now()]);
            event(new HostDashboardUpdated('tables', $table->id));
        }

        if ($session->server_id) {
            event(new ServerSessionsUpdated($session->server_id, $session->id));
        }
    }

    private function resolveSessionTables(TableSession $session)
    {
        $session->loadMissing(['diningTable', 'tables']);

        $tables = $session->tables ?? collect();
        if ($session->diningTable) {
            $tables = $tables->concat([$session->diningTable]);
        }

        return $tables
            ->unique('id')
            ->values();
    }

    private function isCloverNotFound(Throwable $exception): bool
    {
        $message = $exception->getMessage();

        return str_contains($message, '(404)')
            || str_contains($message, 'Order not found')
            || str_contains($message, 'Not Found');
    }
}
