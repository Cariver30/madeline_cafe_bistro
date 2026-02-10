<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\HostDashboardUpdated;
use App\Events\ServerSessionsUpdated;
use App\Models\DiningTable;
use App\Models\TableAssignment;
use App\Models\TableSession;
use App\Models\User;
use App\Models\Setting;
use App\Models\WaitingListEntry;
use App\Models\WaitingListSetting;
use App\Support\TwilioSmsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class WaitingListController extends Controller
{
    private ?int $defaultWaitMinutes = null;
    private ?string $defaultAreaCode = null;

    public function index(Request $request)
    {
        $query = WaitingListEntry::query()->with(['assignments.diningTable']);

        if ($request->filled('status')) {
            $statuses = collect(explode(',', $request->string('status')->toString()))
                ->map(fn ($status) => trim($status))
                ->filter();
            if ($statuses->isNotEmpty()) {
                $query->whereIn('status', $statuses->all());
            }
        } else {
            $query->whereIn('status', ['waiting', 'notified', 'seated', 'confirmed']);
        }

        $entries = $query
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'entries' => $entries->map(fn (WaitingListEntry $entry) => $this->formatEntry($entry)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:30'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'party_size' => ['required', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'quoted_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'reservation_at' => ['nullable', 'string'],
        ]);

        $data['guest_phone'] = $this->normalizePhone($data['guest_phone']);
        $data['quoted_at'] = ! empty($data['quoted_minutes']) ? now() : null;
        $data['cancel_token'] = Str::uuid()->toString();

        if (array_key_exists('reservation_at', $data)) {
            $reservationAt = $this->normalizeReservationAt($data['reservation_at']);
            if ($reservationAt === false) {
                return response()->json([
                    'message' => 'La reserva debe ser para hoy y en el futuro.',
                    'errors' => [
                        'reservation_at' => ['La hora de reserva debe ser para hoy y en el futuro.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data['reservation_at'] = $reservationAt;
        }

        $entry = WaitingListEntry::create($data);

        event(new HostDashboardUpdated('waiting_list', $entry->id));

        return response()->json([
            'message' => 'Entrada creada.',
            'entry' => $this->formatEntry($entry->fresh(['assignments.diningTable'])),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, WaitingListEntry $waitingListEntry)
    {
        $data = $request->validate([
            'guest_name' => ['sometimes', 'string', 'max:255'],
            'guest_phone' => ['sometimes', 'string', 'max:30'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'party_size' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'quoted_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'reservation_at' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['waiting', 'notified', 'seated', 'cancelled', 'no_show', 'confirmed'])],
        ]);

        $statusChanged = false;
        $statusValue = null;
        if (Arr::has($data, 'guest_phone')) {
            $data['guest_phone'] = $this->normalizePhone($data['guest_phone']);
        }

        if (! empty($data['status'])) {
            $statusChanged = true;
            $statusValue = $data['status'];
            $this->applyStatus($waitingListEntry, $data['status']);
            unset($data['status']);
        }

        if (Arr::has($data, 'quoted_minutes')) {
            $data['quoted_minutes'] = $data['quoted_minutes'] ?: null;
            $data['quoted_at'] = $data['quoted_minutes'] ? now() : null;
        }

        if (Arr::has($data, 'reservation_at')) {
            $reservationAt = $this->normalizeReservationAt($data['reservation_at']);
            if ($reservationAt === false) {
                return response()->json([
                    'message' => 'La reserva debe ser para hoy y en el futuro.',
                    'errors' => [
                        'reservation_at' => ['La hora de reserva debe ser para hoy y en el futuro.'],
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data['reservation_at'] = $reservationAt;
            $data['confirmation_received_at'] = null;
            $data['reminder_30_sent_at'] = null;
            $data['reminder_10_sent_at'] = null;
            $data['auto_cancelled_at'] = null;
            if ($reservationAt && ! Arr::has($data, 'status')) {
                $data['status'] = 'waiting';
            }
        }

        if ($data) {
            $waitingListEntry->update($data);
        }

        event(new HostDashboardUpdated('waiting_list', $waitingListEntry->id));
        if ($statusChanged && in_array($statusValue, ['seated', 'cancelled', 'no_show'], true)) {
            event(new HostDashboardUpdated('tables'));
        }

        return response()->json([
            'message' => 'Entrada actualizada.',
            'entry' => $this->formatEntry($waitingListEntry->fresh(['assignments.diningTable'])),
        ]);
    }

    public function notify(Request $request, WaitingListEntry $waitingListEntry, TwilioSmsClient $smsClient)
    {
        if (in_array($waitingListEntry->status, ['cancelled', 'no_show', 'seated'], true)) {
            return response()->json([
                'message' => 'No se puede notificar esta entrada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $settings = WaitingListSetting::current();
        $message = $this->buildMessage($settings, $waitingListEntry);
        $results = [
            'sms' => false,
            'email' => false,
        ];

        if ($settings->sms_enabled && $waitingListEntry->guest_phone) {
            $results['sms'] = $smsClient->send($waitingListEntry->guest_phone, $message);
        }

        if ($settings->email_enabled && $waitingListEntry->guest_email) {
            Mail::raw($message, function ($mail) use ($waitingListEntry) {
                $mail->to($waitingListEntry->guest_email)
                    ->subject('Tu mesa está lista');
            });
            $results['email'] = true;
        }

        $waitingListEntry->update([
            'status' => 'notified',
            'notified_at' => now(),
        ]);

        event(new HostDashboardUpdated('waiting_list', $waitingListEntry->id));

        return response()->json([
            'message' => 'Notificación enviada.',
            'results' => $results,
            'entry' => $this->formatEntry($waitingListEntry->fresh(['assignments.diningTable'])),
        ]);
    }

    public function assignTables(Request $request, WaitingListEntry $waitingListEntry)
    {
        $data = $request->validate([
            'table_ids' => ['required', 'array', 'min:1'],
            'table_ids.*' => ['integer', Rule::exists('dining_tables', 'id')],
            'mode' => ['nullable', Rule::in(['reserve', 'seat'])],
            'replace' => ['nullable', 'boolean'],
            'server_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'server')->where('active', true)],
        ]);

        if (in_array($waitingListEntry->status, ['cancelled', 'no_show'], true)) {
            return response()->json([
                'message' => 'Esta entrada ya fue cancelada.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $mode = $data['mode'] ?? 'reserve';
        $replace = $data['replace'] ?? true;
        $tableIds = array_values(array_unique($data['table_ids']));
        $tables = DiningTable::whereIn('id', $tableIds)->get();
        $serverId = $data['server_id'] ?? null;
        $server = null;

        if ($mode === 'seat' && ! $serverId) {
            return response()->json([
                'message' => 'Selecciona un mesero para sentar la mesa.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($serverId) {
            $server = User::where('id', $serverId)
                ->where('role', 'server')
                ->where('active', true)
                ->first();
            if (! $server) {
                return response()->json([
                    'message' => 'El mesero seleccionado no está disponible.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if ($tables->count() !== count($tableIds)) {
            return response()->json([
                'message' => 'No se encontraron todas las mesas seleccionadas.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($tables as $table) {
            if (! in_array($table->status, ['available', 'reserved'], true)) {
                return response()->json([
                    'message' => "La mesa {$table->label} no está disponible.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $conflict = TableAssignment::where('dining_table_id', $table->id)
                ->whereNull('released_at')
                ->where('waiting_list_entry_id', '!=', $waitingListEntry->id)
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => "La mesa {$table->label} ya está asignada.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $hasActiveSession = TableSession::where('status', 'active')
                ->where(function ($query) use ($table) {
                    $query
                        ->where('dining_table_id', $table->id)
                        ->orWhereHas('tables', fn ($sub) => $sub->where('dining_tables.id', $table->id));
                })
                ->exists();

            if ($hasActiveSession) {
                return response()->json([
                    'message' => "La mesa {$table->label} tiene una sesión activa.",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $existingAssignments = $waitingListEntry->assignments()->whereNull('released_at')->get();
        $existingTableIds = $existingAssignments->pluck('dining_table_id')->all();

        if ($replace) {
            foreach ($existingAssignments as $assignment) {
                if (! in_array($assignment->dining_table_id, $tableIds, true)) {
                    $assignment->update(['released_at' => now()]);
                    if ($assignment->diningTable && $assignment->diningTable->status === 'reserved') {
                        $assignment->diningTable->update(['status' => 'available']);
                    }
                }
            }
        }

        foreach ($tables as $table) {
            if (! in_array($table->id, $existingTableIds, true)) {
                $waitingListEntry->assignments()->create([
                    'dining_table_id' => $table->id,
                    'assigned_at' => now(),
                ]);
            }

            $table->update(['status' => $mode === 'seat' ? 'occupied' : 'reserved']);
        }

        if ($mode === 'seat' && $server) {
            $primaryTable = $tables->sortBy('position')->first();
            $tableLabel = $tables
                ->sortBy('position')
                ->pluck('label')
                ->filter()
                ->implode(' + ');

            $session = TableSession::create([
                'server_id' => $server->id,
                'dining_table_id' => $primaryTable?->id,
                'waiting_list_entry_id' => $waitingListEntry->id,
                'table_label' => $tableLabel ?: ($primaryTable?->label ?? 'Mesa'),
                'party_size' => $waitingListEntry->party_size,
                'guest_name' => $waitingListEntry->guest_name,
                'guest_email' => strtolower(trim((string) $waitingListEntry->guest_email)),
                'guest_phone' => $waitingListEntry->guest_phone,
                // El mesero decide si habilita QR (modo table) o tradicional.
                'order_mode' => 'traditional',
                'status' => 'active',
                'expires_at' => now()->addHour(),
                'seated_at' => now(),
            ]);
            $session->tables()->sync($tables->pluck('id')->all());

            $this->applyStatus($waitingListEntry, 'seated');
            event(new ServerSessionsUpdated($server->id, $session->id));
        }

        event(new HostDashboardUpdated('waiting_list', $waitingListEntry->id));
        event(new HostDashboardUpdated('tables'));

        return response()->json([
            'message' => 'Mesas asignadas.',
            'entry' => $this->formatEntry($waitingListEntry->fresh(['assignments.diningTable'])),
        ]);
    }

    public function settings()
    {
        $settings = WaitingListSetting::current();

        return response()->json([
            'settings' => $this->formatSettings($settings),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'default_wait_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'notify_after_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'sms_enabled' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'notify_message_template' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = WaitingListSetting::current();
        $settings->update($data);

        event(new HostDashboardUpdated('settings'));

        return response()->json([
            'message' => 'Configuración actualizada.',
            'settings' => $this->formatSettings($settings->fresh()),
        ]);
    }

    private function formatEntry(WaitingListEntry $entry): array
    {
        $estimatedWait = $entry->quoted_minutes ?? $this->resolveDefaultWaitMinutes();
        $startAt = $entry->created_at;
        $endAt = $entry->seated_at ?? now();
        $elapsedWait = $startAt ? (int) $endAt->diffInMinutes($startAt) : null;
        $elapsedWait = $elapsedWait !== null ? max($elapsedWait, 0) : null;
        $remainingWait = $elapsedWait !== null ? max($estimatedWait - $elapsedWait, 0) : null;
        $waitedMinutes = $entry->seated_at && $entry->created_at
            ? max((int) $entry->seated_at->diffInMinutes($entry->created_at), 0)
            : null;

        return [
            'id' => $entry->id,
            'guest_name' => $entry->guest_name,
            'guest_phone' => $entry->guest_phone,
            'guest_email' => $entry->guest_email,
            'party_size' => $entry->party_size,
            'notes' => $entry->notes,
            'status' => $entry->status,
            'quoted_minutes' => $entry->quoted_minutes,
            'quoted_at' => optional($entry->quoted_at)->toIso8601String(),
            'reservation_at' => optional($entry->reservation_at)->toIso8601String(),
            'confirmation_received_at' => optional($entry->confirmation_received_at)->toIso8601String(),
            'reminder_30_sent_at' => optional($entry->reminder_30_sent_at)->toIso8601String(),
            'reminder_10_sent_at' => optional($entry->reminder_10_sent_at)->toIso8601String(),
            'auto_cancelled_at' => optional($entry->auto_cancelled_at)->toIso8601String(),
            'notified_at' => optional($entry->notified_at)->toIso8601String(),
            'seated_at' => optional($entry->seated_at)->toIso8601String(),
            'cancelled_at' => optional($entry->cancelled_at)->toIso8601String(),
            'no_show_at' => optional($entry->no_show_at)->toIso8601String(),
            'tables' => $entry->assignments->whereNull('released_at')->map(function ($assignment) {
                return [
                    'id' => $assignment->diningTable?->id,
                    'label' => $assignment->diningTable?->label,
                    'capacity' => $assignment->diningTable?->capacity,
                    'status' => $assignment->diningTable?->status,
                    'assigned_at' => optional($assignment->assigned_at)->toIso8601String(),
                ];
            })->values(),
            'timeclock' => [
                'estimated_wait_minutes' => $estimatedWait,
                'elapsed_wait_minutes' => $elapsedWait,
                'remaining_wait_minutes' => $remainingWait,
                'waited_minutes' => $waitedMinutes,
            ],
            'created_at' => optional($entry->created_at)->toIso8601String(),
            'updated_at' => optional($entry->updated_at)->toIso8601String(),
        ];
    }

    private function formatSettings(WaitingListSetting $settings): array
    {
        return [
            'id' => $settings->id,
            'default_wait_minutes' => $settings->default_wait_minutes,
            'notify_after_minutes' => $settings->notify_after_minutes,
            'sms_enabled' => (bool) $settings->sms_enabled,
            'email_enabled' => (bool) $settings->email_enabled,
            'notify_message_template' => $settings->notify_message_template,
        ];
    }

    private function resolveDefaultWaitMinutes(): int
    {
        if ($this->defaultWaitMinutes !== null) {
            return $this->defaultWaitMinutes;
        }

        $settings = WaitingListSetting::current();
        $this->defaultWaitMinutes = (int) ($settings?->default_wait_minutes ?? 15);

        return $this->defaultWaitMinutes;
    }

    private function buildMessage(WaitingListSetting $settings, WaitingListEntry $entry): string
    {
        $template = $settings->notify_message_template;
        if (! $template) {
            $template = 'Hola {name}, tu mesa está lista en {restaurant}. Responde CANCELAR si ya no vienes.';
        }

        return strtr($template, [
            '{name}' => $entry->guest_name,
            '{party_size}' => (string) $entry->party_size,
            '{restaurant}' => config('app.name'),
            '{wait_minutes}' => (string) ($entry->quoted_minutes ?? $settings->default_wait_minutes),
        ]);
    }

    private function normalizeReservationAt(?string $value): Carbon|bool|null
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        try {
            $reservation = Carbon::parse($raw, config('app.timezone'));
        } catch (\Throwable) {
            return false;
        }

        $reservation = $reservation->setTimezone(config('app.timezone'));
        $now = now(config('app.timezone'));

        if ($reservation->toDateString() !== $now->toDateString()) {
            return false;
        }

        if ($reservation->lessThan($now)) {
            return false;
        }

        return $reservation;
    }

    private function applyStatus(WaitingListEntry $entry, string $status): void
    {
        $updates = ['status' => $status];

        if ($status === 'seated') {
            $updates['seated_at'] = now();
        }

        if ($status === 'cancelled') {
            $updates['cancelled_at'] = now();
            $this->releaseAssignments($entry, true);
        }

        if ($status === 'no_show') {
            $updates['no_show_at'] = now();
            $this->releaseAssignments($entry, true);
        }

        $entry->update($updates);
    }

    private function releaseAssignments(WaitingListEntry $entry, bool $releaseTables): void
    {
        $assignments = $entry->assignments()->whereNull('released_at')->get();

        foreach ($assignments as $assignment) {
            $assignment->update(['released_at' => now()]);
            if ($releaseTables && $assignment->diningTable && $assignment->diningTable->status === 'reserved') {
                $assignment->diningTable->update(['status' => 'available']);
            }
        }
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');
        if (! $digits) {
            return $phone;
        }

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        if (strlen($digits) === 7) {
            $area = $this->resolveDefaultAreaCode();
            if ($area) {
                return '+1' . $area . $digits;
            }
        }

        return $phone;
    }

    private function resolveDefaultAreaCode(): ?string
    {
        if ($this->defaultAreaCode !== null) {
            return $this->defaultAreaCode !== '' ? $this->defaultAreaCode : null;
        }

        $settingsPhone = Setting::first()?->phone_number;
        $digits = preg_replace('/\D+/', '', $settingsPhone ?? '');
        $area = null;

        if (strlen($digits) === 10) {
            $area = substr($digits, 0, 3);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $area = substr($digits, 1, 3);
        }

        $this->defaultAreaCode = $area ?: '';

        return $area;
    }
}
