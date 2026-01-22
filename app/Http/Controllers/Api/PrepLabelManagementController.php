<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrepLabel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrepLabelManagementController extends Controller
{
    public function index()
    {
        $labels = PrepLabel::with(['area', 'printer'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'labels' => $labels,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateLabel($request);
        $label = PrepLabel::create($data);

        return response()->json([
            'message' => 'Label creado correctamente.',
            'label' => $label->fresh(['area', 'printer']),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, PrepLabel $prepLabel)
    {
        $data = $this->validateLabel($request);
        $prepLabel->update($data);

        return response()->json([
            'message' => 'Label actualizado.',
            'label' => $prepLabel->fresh(['area', 'printer']),
        ]);
    }

    public function destroy(PrepLabel $prepLabel)
    {
        $prepLabel->delete();

        return response()->json([
            'message' => 'Label eliminado.',
        ]);
    }

    protected function validateLabel(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'prep_area_id' => ['required', 'exists:prep_areas,id'],
            'printer_id' => ['nullable', 'exists:printers,id'],
            'active' => ['nullable', 'boolean'],
        ]);
    }
}
