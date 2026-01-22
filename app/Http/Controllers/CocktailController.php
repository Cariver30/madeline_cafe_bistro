<?php

namespace App\Http\Controllers;

use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\CocktailSubcategory;
use App\Models\Extra;
use App\Models\Setting;
use App\Models\Dish;
use App\Models\Popup;
use App\Models\PrepArea;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CocktailController extends Controller
{
    public function index()
{
    $settings = Setting::first();
    $itemQuery = function ($query) {
        $query->where('visible', true)
            ->with([
                'subcategory:id,name,cocktail_category_id',
                'dishes:id,name',
                'extras' => function ($extraQuery) {
                    $extraQuery->select('extras.id', 'name', 'price', 'description', 'active');
                },
            ])
            ->orderBy('position');
    };

    $cocktailCategories = CocktailCategory::with([
        'items' => $itemQuery,
        'subcategories' => function ($query) use ($itemQuery) {
            $query->orderBy('order')
                ->orderBy('id')
                ->with(['items' => $itemQuery]);
        },
    ])->orderBy('order')->get();
    $popups = Popup::where('active', 1)
                    ->where('view', 'cocktails')
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->get();

    // Añade este log para depuración
    \Log::info('Datos para la vista de cocktails', compact('settings', 'cocktailCategories', 'popups'));

    return view('cocktail.index', compact('settings', 'cocktailCategories', 'popups'));
}

    public function create()
    {
        $categories = CocktailCategory::with('subcategories')->orderBy('order')->get();
        $dishes = Dish::orderBy('name')->get();
        $availableExtras = Extra::orderBy('name')->forView('cocktails')->get();
        $taxes = Tax::orderBy('name')->get();
        $prepAreas = PrepArea::with(['labels' => function ($query) {
            $query->where('active', true)->orderBy('name');
        }])->where('active', true)->orderBy('name')->get();
        $subcategories = CocktailSubcategory::with('category')
            ->orderBy('cocktail_category_id')
            ->orderBy('order')
            ->get();

        return view('cocktail.create', compact('categories', 'dishes', 'availableExtras', 'taxes', 'subcategories', 'prepAreas'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'subcategory_id' => $request->input('subcategory_id') ?: null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:cocktail_categories,id',
            'subcategory_id' => [
                'nullable',
                Rule::exists('cocktail_subcategories', 'id')->where(
                    fn ($query) => $query->where('cocktail_category_id', $request->input('category_id'))
                ),
            ],
            'image' => 'nullable|image',
            'featured_on_cover' => ['nullable', 'boolean'],
            'dishes' => ['nullable', 'array'],
            'dishes.*' => ['integer', 'exists:dishes,id'],
            'extra_ids' => ['nullable','array'],
            'extra_ids.*' => ['integer','exists:extras,id'],
            'prep_label_id' => ['nullable', 'integer', 'exists:prep_labels,id'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ], [
            'description.required' => 'Falta la descripción del cóctel.',
        ]);

        $cocktail = new Cocktail($validated);
        $cocktail->visible = $request->boolean('visible', true);

        if ($request->hasFile('image')) {
            $cocktail->image = $request->file('image')->store('cocktail_images', 'public');
        }

        $cocktail->featured_on_cover = $request->boolean('featured_on_cover');
        $cocktail->save();
        $cocktail->dishes()->sync($request->input('dishes', []));
        $cocktail->extras()->sync($request->input('extra_ids', []));
        $cocktail->prepLabels()->sync($this->collectPrepLabelIds($request));
        $cocktail->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('cocktails.edit', $cocktail)->with('success', 'Cóctel creado con éxito.');
    }

    public function edit(Cocktail $cocktail)
    {
        $categories = CocktailCategory::with('subcategories')->orderBy('order')->get();
        $dishes = Dish::orderBy('name')->get();
        $availableExtras = Extra::orderBy('name')->forView('cocktails')->get();
        $taxes = Tax::orderBy('name')->get();
        $prepAreas = PrepArea::with(['labels' => function ($query) {
            $query->where('active', true)->orderBy('name');
        }])->where('active', true)->orderBy('name')->get();
        $subcategories = CocktailSubcategory::with('category')
            ->orderBy('cocktail_category_id')
            ->orderBy('order')
            ->get();

        $cocktail->loadMissing('prepLabels:id,name', 'taxes:id,name');

        return view('cocktail.edit', compact('cocktail', 'categories', 'dishes', 'availableExtras', 'taxes', 'subcategories', 'prepAreas'));
    }

    public function update(Request $request, Cocktail $cocktail)
    {
        $request->merge([
            'subcategory_id' => $request->input('subcategory_id') ?: null,
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:cocktail_categories,id',
            'subcategory_id' => [
                'nullable',
                Rule::exists('cocktail_subcategories', 'id')->where(
                    fn ($query) => $query->where('cocktail_category_id', $request->input('category_id'))
                ),
            ],
            'image' => 'nullable|image',
            'featured_on_cover' => ['nullable', 'boolean'],
            'dishes' => ['nullable','array'],
            'dishes.*' => ['integer','exists:dishes,id'],
            'extra_ids' => ['nullable','array'],
            'extra_ids.*' => ['integer','exists:extras,id'],
            'prep_label_id' => ['nullable', 'integer', 'exists:prep_labels,id'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ], [
            'description.required' => 'Falta la descripción del cóctel.',
        ]);

        $data = $validated;
        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cocktail_images', 'public');
        }

        $cocktail->update($data);
        $cocktail->dishes()->sync($request->input('dishes', []));
        $cocktail->extras()->sync($request->input('extra_ids', []));
        $cocktail->prepLabels()->sync($this->collectPrepLabelIds($request));
        $cocktail->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('cocktails.edit', $cocktail)->with('success', 'Cóctel actualizado con éxito.');
    }

    public function destroy(Cocktail $cocktail)
    {
        $cocktail->delete();
        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Artículo de Cocktail eliminado con éxito');
    }

    public function toggleVisibility(Cocktail $cocktail)
    {
        $cocktail->visible = !$cocktail->visible;
        $cocktail->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Visibilidad del artículo de Cocktail actualizada');
    }

    public function toggleFeatured(Cocktail $cocktail)
    {
        $cocktail->featured_on_cover = !$cocktail->featured_on_cover;
        $cocktail->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails-section',
            'open' => 'cocktail-create',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Destacado en portada actualizado.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:cocktail_categories,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:cocktails,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            Cocktail::where('id', $id)
                ->where('category_id', $data['category_id'])
                ->update(['position' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    private function collectPrepLabelIds(Request $request): array
    {
        return collect([$request->input('prep_label_id')])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
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
