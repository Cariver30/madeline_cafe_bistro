<?php

namespace App\Http\Controllers;

use App\Models\WineCategory;
use App\Models\Tax;
use Illuminate\Http\Request;

class WineCategoryController extends Controller
{
    // Mostrar todas las categorías de vino (aunque ya no se usen en frontend)
    public function index()
    {
        $categories = WineCategory::orderBy('order')->get();
        return view('wine.categories.index', compact('categories'));
    }

    // Formulario para crear una nueva categoría
    public function create()
    {
        $taxes = Tax::orderBy('name')->get();

        return view('wine.categories.create', compact('taxes'));
    }

    // Guardar nueva categoría
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:wine_categories,name',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        $data['order'] = (WineCategory::max('order') ?? 0) + 1;
        $data['show_on_cover'] = $request->boolean('show_on_cover');

        $category = WineCategory::create($data);
        $category->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Categoría creada con éxito');
    }

    // Formulario para editar una categoría existente
    public function edit(WineCategory $wineCategory)
    {
        $taxes = Tax::orderBy('name')->get();
        $wineCategory->loadMissing('taxes:id,name');

        return view('wine.categories.edit', compact('wineCategory', 'taxes'));
    }

    // Actualizar una categoría existente
    public function update(Request $request, WineCategory $wineCategory)
    {
        $data = $request->validate([
            'name' => 'required|unique:wine_categories,name,' . $wineCategory->id,
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        $data['show_on_cover'] = $request->boolean('show_on_cover');

        $wineCategory->update($data);
        $wineCategory->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Categoría actualizada con éxito');
    }

    // Eliminar una categoría
    public function destroy(WineCategory $wineCategory)
    {
        $wineCategory->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Categoría eliminada con éxito');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:wine_categories,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            WineCategory::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleCover(WineCategory $wineCategory)
    {
        $wineCategory->show_on_cover = !$wineCategory->show_on_cover;
        if ($wineCategory->show_on_cover && !$wineCategory->cover_title) {
            $wineCategory->cover_title = $wineCategory->name;
        }
        $wineCategory->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Categoría de café actualizada en la portada.');
    }

    public function updateFeaturedItems(Request $request, WineCategory $wineCategory)
    {
        $data = $request->validate([
            'featured_items' => ['nullable', 'array'],
            'featured_items.*' => ['integer', 'exists:wines,id'],
        ]);

        $ids = collect($data['featured_items'] ?? []);
        $wineCategory->load('items');

        foreach ($wineCategory->items as $item) {
            $item->featured_on_cover = $ids->contains($item->id);
            $item->save();
        }

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Bebidas destacadas actualizadas.');
    }

    private function collectTaxIds(Request $request): array
    {
        return collect($request->input('tax_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
