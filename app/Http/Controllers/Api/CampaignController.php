<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->query('view');

        $query = Popup::query()->orderByDesc('start_date');

        if ($view) {
            $query->where('view', $view);
        }

        $campaigns = $query->get()->map(fn (Popup $popup) => $this->serializePopup($popup));

        return response()->json([
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePopup($request, true);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('popup_images', 'public');
        }

        $popup = Popup::create($data);

        return response()->json([
            'message' => 'Campaña creada.',
            'campaign' => $this->serializePopup($popup),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Popup $popup)
    {
        $data = $this->validatePopup($request, false);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('popup_images', 'public');
            if ($popup->image) {
                Storage::disk('public')->delete($popup->image);
            }
            $data['image'] = $newPath;
        }

        $popup->update($data);

        return response()->json([
            'message' => 'Campaña actualizada.',
            'campaign' => $this->serializePopup($popup),
        ]);
    }

    public function destroy(Popup $popup)
    {
        if ($popup->image) {
            Storage::disk('public')->delete($popup->image);
        }

        $popup->delete();

        return response()->json([
            'message' => 'Campaña eliminada.',
        ]);
    }

    public function toggle(Popup $popup)
    {
        $popup->active = !$popup->active;
        $popup->save();

        return response()->json([
            'message' => 'Estado actualizado.',
            'campaign' => $this->serializePopup($popup),
        ]);
    }

    protected function validatePopup(Request $request, bool $isCreate): array
    {
        $imageRule = $isCreate ? ['required', 'image'] : ['nullable', 'image'];

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => $imageRule,
            'view' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'active' => ['required', 'boolean'],
            'repeat_days' => ['nullable', 'array'],
            'repeat_days.*' => ['integer', 'between:0,6'],
        ]);

        $validated['repeat_days'] = $request->repeat_days ? implode(',', $request->repeat_days) : null;

        return $validated;
    }

    protected function serializePopup(Popup $popup): array
    {
        return [
            'id' => $popup->id,
            'title' => $popup->title,
            'image' => $popup->image ? asset('storage/' . $popup->image) : null,
            'view' => $popup->view,
            'start_date' => $popup->start_date,
            'end_date' => $popup->end_date,
            'active' => (bool) $popup->active,
            'repeat_days' => $popup->repeat_days ? array_map('intval', explode(',', $popup->repeat_days)) : [],
        ];
    }
}
