<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSection;
use App\Models\EventTicket;
use Illuminate\Http\Request;

class EventManagementController extends Controller
{
    public function index()
    {
        $events = Event::latest()->paginate(20);
        $stats = [
            'total' => Event::count(),
            'active' => Event::where('is_active', true)->count(),
            'upcoming' => Event::whereDate('start_at', '>=', now())->count(),
        ];
        $latestTickets = EventTicket::latest()->take(5)->get();

        return view('admin.events.index', compact('events', 'stats', 'latestTickets'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:events,slug',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'hero_image' => 'nullable|image',
            'map_image' => 'nullable|image',
            'additional_info' => 'nullable|array',
        ]);

        foreach (['hero_image', 'map_image'] as $field) {
            if ($request->hasFile($field)) {
                $validated[$field] = $request->file($field)->store('events', 'public');
            }
        }

        $event = Event::create($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.events.edit', $event)->with('success', 'Evento creado.');
    }

    public function edit(Event $event)
    {
        $sections = $event->sections;
        return view('admin.events.edit', compact('event', 'sections'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => "required|string|max:255|unique:events,slug,{$event->id}",
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'hero_image' => 'nullable|image',
            'map_image' => 'nullable|image',
            'additional_info' => 'nullable|array',
        ]);

        foreach(['hero_image','map_image'] as $field){
            if($request->hasFile($field)){
                $validated[$field] = $request->file($field)->store('events', 'public');
            }
        }

        $event->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.events.edit', $event)->with('success', 'Evento actualizado.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Evento eliminado.');
    }

    public function storeSection(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'available_slots' => 'nullable|integer|min:0',
            'price_per_person' => 'nullable|numeric|min:0',
            'flat_price' => 'nullable|numeric|min:0',
            'layout_coordinates' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if (!isset($data['available_slots'])) {
            $data['available_slots'] = $data['capacity'];
        }

        $event->sections()->create($data);

        return back()->with('success', 'Sección creada.');
    }

    public function destroySection(EventSection $section)
    {
        $section->delete();
        return back()->with('success', 'Sección eliminada.');
    }
}
