<?php

namespace App\Support;

use App\Events\HostDashboardUpdated;
use App\Models\WaitingListEntry;
use App\Models\WaitingListSetting;
use Illuminate\Support\Facades\Mail;

class WaitingListReservationReminder
{
    public function __construct(private TwilioSmsClient $smsClient)
    {
    }

    public function sendReminders(): void
    {
        $now = now();
        $entries = WaitingListEntry::query()
            ->whereNotNull('reservation_at')
            ->whereDate('reservation_at', $now->toDateString())
            ->whereIn('status', ['waiting', 'notified', 'confirmed'])
            ->get();

        if ($entries->isEmpty()) {
            return;
        }

        $settings = WaitingListSetting::current();

        foreach ($entries as $entry) {
            if ($entry->confirmation_received_at) {
                continue;
            }

            if (in_array($entry->status, ['cancelled', 'no_show', 'seated'], true)) {
                continue;
            }

            $reservationAt = $entry->reservation_at;
            if (! $reservationAt) {
                continue;
            }

            $minutesUntil = $now->diffInMinutes($reservationAt, false);

            if ($minutesUntil <= 5) {
                $this->autoCancel($settings, $entry);
                continue;
            }

            if ($minutesUntil <= 10 && ! $entry->reminder_10_sent_at) {
                $message = $this->buildReminderMessage($entry, 10);
                if ($this->sendMessage($settings, $entry, $message)) {
                    $entry->update(['reminder_10_sent_at' => now()]);
                    event(new HostDashboardUpdated('waiting_list', $entry->id));
                }
                continue;
            }

            if ($minutesUntil <= 30 && ! $entry->reminder_30_sent_at) {
                $message = $this->buildReminderMessage($entry, 30);
                if ($this->sendMessage($settings, $entry, $message)) {
                    $entry->update(['reminder_30_sent_at' => now()]);
                    event(new HostDashboardUpdated('waiting_list', $entry->id));
                }
            }
        }
    }

    private function buildReminderMessage(WaitingListEntry $entry, int $minutes): string
    {
        $timeLabel = $entry->reservation_at
            ? $entry->reservation_at->timezone(config('app.timezone'))->format('g:i A')
            : 'pronto';

        if ($minutes <= 10) {
            return "Recordatorio: tu reserva es en 10 minutos ({$timeLabel}). Responde SI para confirmar o CANCELAR para cancelar.";
        }

        return "Hola {$entry->guest_name}, tu reserva es hoy a las {$timeLabel}. Responde SI para confirmar o CANCELAR si no vienes.";
    }

    private function sendMessage(WaitingListSetting $settings, WaitingListEntry $entry, string $message): bool
    {
        $sent = false;

        if ($settings->sms_enabled && $entry->guest_phone) {
            $sent = $this->smsClient->send($entry->guest_phone, $message) || $sent;
        }

        if ($settings->email_enabled && $entry->guest_email) {
            Mail::raw($message, function ($mail) use ($entry) {
                $mail->to($entry->guest_email)
                    ->subject('Recordatorio de reserva');
            });
            $sent = true;
        }

        return $sent;
    }

    private function autoCancel(WaitingListSetting $settings, WaitingListEntry $entry): void
    {
        if ($entry->auto_cancelled_at || $entry->confirmation_received_at) {
            return;
        }

        $entry->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_cancelled_at' => now(),
        ]);

        $this->releaseAssignments($entry);

        $message = 'Tu reserva fue cancelada por falta de confirmación.';
        $this->sendMessage($settings, $entry, $message);

        event(new HostDashboardUpdated('waiting_list', $entry->id));
        event(new HostDashboardUpdated('tables'));
    }

    private function releaseAssignments(WaitingListEntry $entry): void
    {
        $assignments = $entry->assignments()->whereNull('released_at')->get();

        foreach ($assignments as $assignment) {
            $assignment->update(['released_at' => now()]);
            if ($assignment->diningTable && $assignment->diningTable->status === 'reserved') {
                $assignment->diningTable->update(['status' => 'available']);
            }
        }
    }
}
