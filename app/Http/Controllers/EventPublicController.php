<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSection;
use App\Models\EventTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventPublicController extends Controller
{
    public function index()
    {
        $events = Event::where('is_active', true)
            ->orderBy('start_at')
            ->get();

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        abort_unless($event->is_active, 404);

        $sections = $event->sections()
            ->where('is_active', true)
            ->orderBy('price_per_person')
            ->get();

        $ticket = session('ticket_success');

        return view('events.show', compact('event', 'sections', 'ticket'));
    }

    public function purchase(Request $request, Event $event)
    {
        abort_unless($event->is_active, 404);

        $data = $request->validate([
            'event_section_id' => 'required|exists:event_sections,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'guest_count' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        /** @var EventSection $section */
        $section = $event->sections()
            ->where('is_active', true)
            ->where('id', $data['event_section_id'])
            ->firstOrFail();

        if (!is_null($section->available_slots) && $section->available_slots < $data['guest_count']) {
            return back()->withErrors([
                'guest_count' => 'No hay asientos suficientes en esta sección.',
            ])->withInput();
        }

        $pricePerGuest = $section->price_per_person ?? 0;
        $total = $section->flat_price ?? ($pricePerGuest * $data['guest_count']);

        $ticket = EventTicket::create([
            'event_id' => $event->id,
            'event_section_id' => $section->id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'guest_count' => $data['guest_count'],
            'total_paid' => $total,
            'ticket_code' => strtoupper(Str::random(10)),
            'meta' => [
                'notes' => $data['notes'] ?? null,
            ],
            'status' => 'paid',
        ]);

        if (!is_null($section->available_slots)) {
            $section->decrement('available_slots', $data['guest_count']);
        }

        return redirect()
            ->route('experiences.show', $event)
            ->with('ticket_success', [
                'code' => $ticket->ticket_code,
                'customer' => $ticket->customer_name,
                'section' => $section->name,
                'total_paid' => $ticket->total_paid,
                'guest_count' => $ticket->guest_count,
            ]);
    }
}
