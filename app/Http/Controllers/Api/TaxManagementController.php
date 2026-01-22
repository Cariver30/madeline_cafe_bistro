<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaxManagementController extends Controller
{
    public function index()
    {
        $taxes = Tax::orderBy('name')->get();

        return response()->json([
            'taxes' => $taxes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        $tax = Tax::create($data);

        return response()->json([
            'message' => 'Impuesto creado.',
            'tax' => $tax,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Tax $tax)
    {
        $data = $this->validatePayload($request);
        $tax->update($data);

        return response()->json([
            'message' => 'Impuesto actualizado.',
            'tax' => $tax,
        ]);
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();

        return response()->json([
            'message' => 'Impuesto eliminado.',
        ]);
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['rate'] = round((float) $data['rate'], 2);

        return $data;
    }
}
