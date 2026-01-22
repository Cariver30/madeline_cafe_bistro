<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;

class TaxAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        Tax::create($data);

        return redirect()->route('admin.new-panel', ['section' => 'taxes'])
            ->with('success', 'Impuesto creado.');
    }

    public function update(Request $request, Tax $tax)
    {
        $data = $this->validatePayload($request);

        $tax->update($data);

        return redirect()->route('admin.new-panel', ['section' => 'taxes'])
            ->with('success', 'Impuesto actualizado.');
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();

        return redirect()->route('admin.new-panel', ['section' => 'taxes'])
            ->with('success', 'Impuesto eliminado.');
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
