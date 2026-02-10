<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiningTable;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiningTableAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255', 'unique:dining_tables,label'],
            'capacity' => ['required', 'integer', 'min:1', 'max:99'],
            'section' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])],
            'position' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DiningTable::create($data);

        return redirect()
            ->route('admin.new-panel', ['section' => 'host'])
            ->with('success', 'Mesa creada correctamente.');
    }

    public function update(Request $request, DiningTable $diningTable)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255', Rule::unique('dining_tables', 'label')->ignore($diningTable->id)],
            'capacity' => ['required', 'integer', 'min:1', 'max:99'],
            'section' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])],
            'position' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $diningTable->update($data);

        return redirect()
            ->route('admin.new-panel', ['section' => 'host'])
            ->with('success', 'Mesa actualizada.');
    }

    public function updateStatus(Request $request, DiningTable $diningTable)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['available', 'reserved', 'occupied', 'dirty', 'out_of_service'])],
        ]);

        $diningTable->update(['status' => $data['status']]);

        return redirect()
            ->route('admin.new-panel', ['section' => 'host'])
            ->with('success', 'Estado actualizado.');
    }

    public function destroy(DiningTable $diningTable)
    {
        $hasActiveSession = TableSession::where('status', 'active')
            ->where(function ($query) use ($diningTable) {
                $query
                    ->where('dining_table_id', $diningTable->id)
                    ->orWhereHas('tables', fn ($sub) => $sub->where('dining_tables.id', $diningTable->id));
            })
            ->exists();

        $hasAssignments = $diningTable->assignments()->whereNull('released_at')->exists();

        if ($hasActiveSession || $hasAssignments) {
            return redirect()
                ->route('admin.new-panel', ['section' => 'host'])
                ->with('error', 'No se puede eliminar una mesa con actividad activa.');
        }

        $diningTable->delete();

        return redirect()
            ->route('admin.new-panel', ['section' => 'host'])
            ->with('success', 'Mesa eliminada.');
    }
}
