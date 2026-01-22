<?php

namespace App\Http\Controllers;

use App\Models\Extra;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExtraController extends Controller
{
    public function index()
    {
        $extras = Extra::orderBy('name')->get();

        return view('extras.index', [
            'extras' => $extras,
            'viewScopes' => Extra::VIEW_SCOPES,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('options')) {
            $data = $this->validatedGroupData($request);
            $active = (bool) ($data['active'] ?? false);
            $groupRequired = (bool) ($data['group_required'] ?? false);
            $maxSelect = $data['max_select'] ?? null;
            $options = $data['options'] ?? [];

            foreach ($options as $option) {
                Extra::create([
                    'name' => $option['name'],
                    'group_name' => $data['group_name'],
                    'group_required' => $groupRequired,
                    'max_select' => $maxSelect,
                    'kind' => $data['kind'],
                    'price' => $option['price'],
                    'description' => $option['description'] ?? null,
                    'view_scope' => $data['view_scope'],
                    'active' => $active,
                ]);
            }

            $count = count($options);

            return redirect($this->redirectTo($request))
                ->with('success', $count > 1 ? 'Opciones creadas correctamente.' : 'Opcion creada correctamente.');
        }

        $data = $this->validatedData($request);

        Extra::create($data);

        return redirect($this->redirectTo($request))
            ->with('success', 'Opcion creada correctamente.');
    }

    public function edit(Extra $extra)
    {
        return view('extras.edit', [
            'extra' => $extra,
            'viewScopes' => Extra::VIEW_SCOPES,
        ]);
    }

    public function update(Request $request, Extra $extra)
    {
        $data = $this->validatedData($request);

        $extra->update($data);

        return redirect($this->redirectTo($request))
            ->with('success', 'Extra actualizado correctamente.');
    }

    public function destroy(Request $request, Extra $extra)
    {
        $extra->delete();

        return redirect($this->redirectTo($request))
            ->with('success', 'Extra eliminado correctamente.');
    }

    protected function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'group_name' => ['nullable', 'string', 'max:255'],
            'kind' => ['required', Rule::in(Extra::KINDS)],
            'group_required' => ['nullable', 'boolean'],
            'max_select' => ['nullable', 'integer', 'min:1', 'max:99'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'view_scope' => ['required', Rule::in(Extra::VIEW_SCOPES)],
            'active' => ['boolean'],
        ]);
    }

    protected function validatedGroupData(Request $request): array
    {
        return $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'kind' => ['required', Rule::in(Extra::KINDS)],
            'group_required' => ['nullable', 'boolean'],
            'max_select' => ['nullable', 'integer', 'min:1', 'max:99'],
            'view_scope' => ['required', Rule::in(Extra::VIEW_SCOPES)],
            'active' => ['boolean'],
            'options' => ['required', 'array', 'min:1'],
            'options.*.name' => ['required', 'string', 'max:255'],
            'options.*.price' => ['required', 'numeric', 'min:0'],
            'options.*.description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    protected function redirectTo(Request $request): string
    {
        return $request->input('redirect_to', route('extras.index'));
    }
}
