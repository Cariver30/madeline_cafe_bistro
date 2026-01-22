<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventNotificationController extends Controller
{
    public function subscribe(Request $request, Event $event)
    {
        return $this->handleSubscription($request, $event);
    }

    public function subscribeGeneral(Request $request)
    {
        $event = Event::where('is_active', true)->orderBy('start_at')->first();
        return $this->handleSubscription($request, $event, 'experiences.index');
    }

    public function subscribeFromCover(Request $request)
    {
        $event = Event::where('is_active', true)->orderBy('start_at')->first();
        return $this->handleSubscription($request, $event, 'cover');
    }

    protected function handleSubscription(Request $request, ?Event $event = null, ?string $redirectRoute = null)
    {
        if ($event && !$event->is_active) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $query = EventNotification::where('email', $data['email']);
        if ($event) {
            $query->where('event_id', $event->id);
        } else {
            $query->whereNull('event_id');
        }

        $notification = $query->first();

        if ($notification) {
            $message = $event
                ? 'Ya estabas en la lista, actualizamos tus datos.'
                : 'Ya estabas en nuestra lista general, actualizamos tus datos.';
            $notification->update([
                'name' => $data['name'],
            ]);

            return redirect()
                ->route($redirectRoute ?? 'experiences.show', $event ? $event : [])
                ->with('notification_success', $message);
        }

        $notification = EventNotification::create([
            'event_id' => $event?->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'confirmation_token' => Str::random(32),
        ]);

        $this->syncWithSendGrid($notification, $event);

        $message = $event
            ? 'Te hemos registrado para recibir novedades de este evento.'
            : 'Te registramos para enterarte de las experiencias del restaurante.';

        return redirect()
            ->route($redirectRoute ?? 'experiences.show', $event ? $event : [])
            ->with('notification_success', $message);
    }

    protected function syncWithSendGrid(EventNotification $notification, ?Event $event): void
    {
        $apiKey = config('services.sendgrid.key');
        if (!$apiKey) {
            Log::info('SendGrid API key missing; skipping sync.');
            return;
        }

        $contact = [
            'email' => $notification->email,
            'first_name' => $notification->name,
        ];

        $customFields = [];
        if ($field = config('services.sendgrid.event_title_field')) {
            $customFields[$field] = $event?->title ?? 'Eventos especiales';
        }
        if ($field = config('services.sendgrid.event_date_field')) {
            $customFields[$field] = optional($event?->start_at)->toIso8601String();
        }
        if ($customFields) {
            $contact['custom_fields'] = $customFields;
        }

        $payload = [
            'contacts' => [$contact],
        ];

        $listId = config('services.sendgrid.events_list_id');
        if ($listId) {
            $payload['list_ids'] = [$listId];
        }

        try {
            Http::withToken($apiKey)
                ->acceptJson()
                ->post('https://api.sendgrid.com/v3/marketing/contacts', $payload)
                ->throw();
        } catch (\Throwable $e) {
            Log::error('SendGrid sync failed', ['message' => $e->getMessage()]);
        }
    }

    public function index()
    {
        $notifications = EventNotification::with('event')
            ->latest()
            ->paginate(30);

        return view('admin.events.notifications', compact('notifications'));
    }
}
