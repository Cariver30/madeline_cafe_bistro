<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Events\HostDashboardUpdated;
use App\Models\WaitingListEntry;
use Illuminate\Http\Request;

class TwilioWebhookController extends Controller
{
    public function waitingList(Request $request)
    {
        $from = $this->normalizePhone($request->input('From', ''));
        $body = strtolower(trim((string) $request->input('Body', '')));

        if (! $from || ! $this->isCancelMessage($body)) {
            return response('', 200);
        }

        $entry = WaitingListEntry::where('guest_phone', $from)
            ->whereIn('status', ['waiting', 'notified'])
            ->orderByDesc('created_at')
            ->first();

        if (! $entry) {
            return $this->twimlResponse('No encontramos una reserva activa para cancelar.');
        }

        $entry->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->releaseAssignments($entry);
        event(new HostDashboardUpdated('waiting_list', $entry->id));
        event(new HostDashboardUpdated('tables'));

        return $this->twimlResponse('Tu reserva fue cancelada. ¡Gracias!');
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

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');
        if (! $digits) {
            return '';
        }

        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        return '+' . $digits;
    }

    private function isCancelMessage(string $body): bool
    {
        return str_contains($body, 'cancel') || str_contains($body, 'cancelar') || str_contains($body, 'stop') || str_contains($body, 'no voy') || str_contains($body, 'no ire');
    }

    private function twimlResponse(string $message)
    {
        $safeMessage = htmlspecialchars($message, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Message>{$safeMessage}</Message></Response>";

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
