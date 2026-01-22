<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrepLabel;
use Illuminate\Http\Request;

class PrepLabelAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        PrepLabel::create($data);

        return redirect()->route('admin.new-panel', ['section' => 'prep'])
            ->with('success', 'Label creado.');
    }

    public function update(Request $request, PrepLabel $prepLabel)
    {
        $data = $this->validatePayload($request);

        $prepLabel->update($data);

        return redirect()->route('admin.new-panel', ['section' => 'prep'])
            ->with('success', 'Label actualizado.');
    }

    public function destroy(PrepLabel $prepLabel)
    {
        $prepLabel->delete();

        return redirect()->route('admin.new-panel', ['section' => 'prep'])
            ->with('success', 'Label eliminado.');
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prep_area_id' => ['required', 'exists:prep_areas,id'],
            'printer_id' => ['nullable', 'exists:printers,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active', false);

        return $data;
    }
}
