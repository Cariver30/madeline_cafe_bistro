<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrepArea;
use Illuminate\Http\Request;

class PrepAreaAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        if (!empty($data['is_default'])) {
            PrepArea::where('is_default', true)->update(['is_default' => false]);
        }

        PrepArea::create($data);

        return redirect()->route('admin.new-panel', ['section' => 'prep'])
            ->with('success', 'Área creada.');
    }

    public function update(Request $request, PrepArea $prepArea)
    {
        $data = $this->validatePayload($request);

        if (!empty($data['is_default'])) {
            PrepArea::where('is_default', true)
                ->where('id', '!=', $prepArea->id)
                ->update(['is_default' => false]);
        }

        $prepArea->update($data);

        return redirect()->route('admin.new-panel', ['section' => 'prep'])
            ->with('success', 'Área actualizada.');
    }

    public function destroy(PrepArea $prepArea)
    {
        $prepArea->delete();

        return redirect()->route('admin.new-panel', ['section' => 'prep'])
            ->with('success', 'Área eliminada.');
    }

    private function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active', false);
        $data['is_default'] = $request->boolean('is_default', false);

        return $data;
    }
}
