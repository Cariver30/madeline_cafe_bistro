<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\HostDashboardUpdated;
use App\Models\DiningTable;
use App\Models\TableSession;
use App\Support\TableTurnTimeEstimator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DiningTableController extends Controller
{
    public function index(Request $request)
    {
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
}
