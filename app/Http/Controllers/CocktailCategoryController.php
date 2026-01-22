<?php

namespace App\Http\Controllers;

use App\Models\CocktailCategory;
use App\Models\Tax;
use Illuminate\Http\Request;

class CocktailCategoryController extends Controller
{
    public function index()
    {
        $categories = CocktailCategory::orderBy('order')->get();
        return view('cocktail.categories.index', compact('categories'));
    }

    public function create()
    {
        $taxes = Tax::orderBy('name')->get();

        return view('cocktail.categories.create', compact('taxes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        $data['order'] = (CocktailCategory::max('order') ?? 0) + 1;
        $data['show_on_cover'] = $request->boolean('show_on_cover');

        $category = CocktailCategory::create($data);
        $category->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Categoría de cocktail creada con éxito.');
    }

    public function edit(CocktailCategory $cocktailCategory)
    {
        $taxes = Tax::orderBy('name')->get();
        $cocktailCategory->loadMissing('taxes:id,name');

        return view('cocktail.categories.edit', compact('cocktailCategory', 'taxes'));
    }

    public function update(Request $request, CocktailCategory $cocktailCategory)
    {
        $data = $request->validate([
            'name' => 'required',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        $data['show_on_cover'] = $request->boolean('show_on_cover');

        $cocktailCategory->update($data);
        $cocktailCategory->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Categoría de cocktail actualizada con éxito.');
    }

    public function destroy(CocktailCategory $cocktailCategory)
    {
        $cocktailCategory->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Categoría de cocktail eliminada con éxito.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:cocktail_categories,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            CocktailCategory::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleCover(CocktailCategory $cocktailCategory)
    {
        $cocktailCategory->show_on_cover = !$cocktailCategory->show_on_cover;
        if ($cocktailCategory->show_on_cover && !$cocktailCategory->cover_title) {
            $cocktailCategory->cover_title = $cocktailCategory->name;
        }
        $cocktailCategory->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Categoría actualizada en la portada.');
    }

    public function updateFeaturedItems(Request $request, CocktailCategory $cocktailCategory)
    {
        $data = $request->validate([
            'featured_items' => ['nullable', 'array'],
            'featured_items.*' => ['integer', 'exists:cocktails,id'],
        ]);

        $ids = collect($data['featured_items'] ?? []);
        $cocktailCategory->load('items');

        foreach ($cocktailCategory->items as $item) {
            $item->featured_on_cover = $ids->contains($item->id);
            $item->save();
        }

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Cócteles destacados actualizados.');
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
