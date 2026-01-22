<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrepArea;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrepAreaManagementController extends Controller
{
    public function index()
    {
        $areas = PrepArea::with('labels')
            ->orderBy('name')
            ->get();

        return response()->json([
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateArea($request);

        if ($data['is_default'] ?? false) {
            PrepArea::where('is_default', true)->update(['is_default' => false]);
        }

        $area = PrepArea::create($data);

        return response()->json([
            'message' => 'Área creada correctamente.',
            'area' => $area->fresh(),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, PrepArea $prepArea)
    {
        $data = $this->validateArea($request);

        if ($data['is_default'] ?? false) {
            PrepArea::where('is_default', true)
                ->where('id', '!=', $prepArea->id)
                ->update(['is_default' => false]);
        }

        $prepArea->update($data);

        return response()->json([
            'message' => 'Área actualizada.',
            'area' => $prepArea->fresh(),
        ]);
    }

    public function destroy(PrepArea $prepArea)
    {
        $prepArea->delete();

        return response()->json([
            'message' => 'Área eliminada.',
        ]);
    }

    protected function validateArea(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }
}
