<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraManagementController extends Controller
{
    public function index(Request $request)
    {
        $viewScope = $request->query('view_scope');
        $onlyActive = $request->boolean('active', false);

        $extras = Extra::query()
            ->when($viewScope, fn ($q) => $q->forView($viewScope))
            ->when($onlyActive, fn ($q) => $q->where('active', true))
            ->orderBy('name')
            ->get();

        return response()->json($extras);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $extra = Extra::create($data);

        return response()->json($extra, 201);
    }

    public function update(Request $request, Extra $extra)
    {
        $data = $this->validatedData($request);

        $extra->update($data);

        return response()->json($extra);
    }

    public function destroy(Extra $extra)
    {
        $extra->delete();

        return response()->json(['status' => 'deleted']);
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'group_required' => ['nullable', 'boolean'],
            'max_select' => ['nullable', 'integer', 'min:1', 'max:99'],
            'min_select' => ['nullable', 'integer', 'min:1', 'max:99'],
            'kind' => ['required', Rule::in(Extra::KINDS)],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'view_scope' => ['required', Rule::in(Extra::VIEW_SCOPES)],
            'active' => ['boolean'],
        ]);
    }
}
