<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Models\Extra;
use App\Models\FoodPairing;
use App\Models\Grape;
use App\Models\Popup;
use App\Models\PrepArea;
use App\Models\Region;
use App\Models\Setting;
use App\Models\Wine;
use App\Models\WineCategory;
use App\Models\WineSubcategory;
use App\Models\WineType;
use App\Models\Tax;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WineController extends Controller
{
    public function index(Request $request)
{
    $settings = Setting::first();

    // 🔧 Detectar si hay filtros activos correctamente
    $hasFilter = $request->hasAny(['region', 'type', 'grape', 'max_price']);

    if ($hasFilter) {
        $wines = Wine::query()
            ->when($request->region, fn($q) => $q->where('region_id', $request->region))
            ->when($request->type, fn($q) => $q->where('type_id', $request->type))
            ->when($request->grape, fn($q) =>
                $q->whereHas('grapes', fn($g) => $g->where('grape_id', $request->grape))
            )
            ->when($request->max_price, fn($q) =>
                $q->where('price', '<=', $request->max_price)
            )
            ->where('visible', true)
            ->with([
                'type',
                'region',
                'grapes',
                'dishes',
                'extras' => function ($extraQuery) {
                    $extraQuery->select('extras.id', 'name', 'price', 'description', 'active');
                },
            ]) // ✅ Asegúrate de incluir 'dishes'
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('coffee.index', [
            'settings' => $settings,
            'wines'    => $wines,
            'filters'  => true,
            'regions'  => Region::all(),
            'types'    => WineType::all(),
            'grapes'   => Grape::all(),
            'popups'   => Popup::where('active', 1)
                                ->whereIn('view', ['coffee', 'wines'])
                                ->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now())
                                ->get(),
        ]);
    }

    // Comportamiento sin filtros (mostrar por categoría)
    $itemQuery = function ($query) {
        $query->where('visible', true)
            ->with([
                'subcategory:id,name,wine_category_id',
                'type',
                'region',
                'grapes',
                'dishes',
                'extras' => function ($extraQuery) {
                    $extraQuery->select('extras.id', 'name', 'price', 'description', 'active');
                },
            ])
            ->orderBy('position')
            ->orderBy('id');
    };

    return view('coffee.index', [
        'settings'       => $settings,
        'wineCategories' => WineCategory::with([
            'items' => $itemQuery,
            'subcategories' => function ($query) use ($itemQuery) {
                $query->orderBy('order')
                    ->orderBy('id')
                    ->with(['items' => $itemQuery]);
            },
        ])->orderBy('order')->get(),
        'filters'        => false,
        'regions'        => Region::all(),
        'types'          => WineType::all(),
        'grapes'         => Grape::all(),
        'popups'         => Popup::where('active', 1)
                                ->whereIn('view', ['coffee', 'wines'])
                                ->whereDate('start_date', '<=', now())
                                ->whereDate('end_date', '>=', now())
                                ->get(),
    ]);
}


    public function create()
    {
        $categories = WineCategory::with('subcategories')->orderBy('order')->get();
        $types = WineType::all();
        $regions = Region::all();
        $grapes = Grape::all();
        $foodPairings = FoodPairing::all();
        $dishes = Dish::all(); // ✅
        $availableExtras = Extra::orderBy('name')->forView('coffee')->get();
        $taxes = Tax::orderBy('name')->get();
        $prepAreas = PrepArea::with(['labels' => function ($query) {
            $query->where('active', true)->orderBy('name');
        }])->where('active', true)->orderBy('name')->get();
        $subcategories = WineSubcategory::with('category')
            ->orderBy('wine_category_id')
            ->orderBy('order')
            ->get();

        return view('wine.create', compact(
            'categories',
            'types',
            'regions',
            'grapes',
            'foodPairings',
            'dishes',
            'availableExtras',
            'taxes',
            'subcategories',
            'prepAreas'
        ));
    }

    public function store(Request $request)
    {
        $request->merge([
            'subcategory_id' => $request->input('subcategory_id') ?: null,
        ]);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'required|string',
            'price'             => 'required|numeric',
            'category_id'       => 'required|exists:wine_categories,id',
            'subcategory_id' => [
                'nullable',
                Rule::exists('wine_subcategories', 'id')->where(
                    fn ($query) => $query->where('wine_category_id', $request->input('category_id'))
                ),
            ],
            'type_id'           => 'nullable|exists:wine_types,id',
            'region_id'         => 'nullable|exists:regions,id',
            'grapes'            => 'nullable|array',
            'grapes.*'          => 'exists:grapes,id',
            'food_pairings'     => 'nullable|array',
            'food_pairings.*'   => 'exists:food_pairings,id',
            'dishes'            => 'nullable|array',
            'dishes.*'          => 'exists:dishes,id', // ✅ Validación de platos
            'extra_ids'         => ['nullable','array'],
            'extra_ids.*'       => ['integer','exists:extras,id'],
            'prep_label_id'     => ['nullable', 'integer', 'exists:prep_labels,id'],
            'image'             => 'nullable|image',
            'featured_on_cover' => ['nullable', 'boolean'],
            'tax_ids'           => ['nullable', 'array'],
            'tax_ids.*'         => ['integer', 'exists:taxes,id'],
        ], [
            'description.required' => 'Falta la descripción del producto.',
        ]);
    
        $wine = new Wine($validated);
        $wine->visible = $request->boolean('visible', true);
    
        if ($request->hasFile('image')) {
            $wine->image = $request->file('image')->store('wine_images', 'public');
        }
    
        $wine->featured_on_cover = $request->boolean('featured_on_cover');
        $wine->save();

        // ✅ Sincronizar relaciones
        $wine->grapes()->sync($request->input('grapes', []));
        $wine->foodPairings()->sync($request->input('food_pairings', []));
        $wine->dishes()->sync($request->input('dishes', [])); // ✅ sincroniza los platos
        $wine->extras()->sync($request->input('extra_ids', []));
        $wine->prepLabels()->sync($this->collectPrepLabelIds($request));
        $wine->taxes()->sync($this->collectTaxIds($request));

    
        return redirect()->route('wines.edit', $wine)->with('success', 'Bebida creada con éxito.');
    }
    

    public function edit(Wine $wine)
    {
        $categories = WineCategory::with('subcategories')->orderBy('order')->get();
        $types = WineType::all();
        $regions = Region::all();
        $grapes = Grape::all();
        $foodPairings = FoodPairing::all();
        $dishes = Dish::all();
        $availableExtras = Extra::orderBy('name')->forView('coffee')->get();
        $taxes = Tax::orderBy('name')->get();
        $prepAreas = PrepArea::with(['labels' => function ($query) {
            $query->where('active', true)->orderBy('name');
        }])->where('active', true)->orderBy('name')->get();
        $subcategories = WineSubcategory::with('category')
            ->orderBy('wine_category_id')
            ->orderBy('order')
            ->get();

        $wine->loadMissing('prepLabels:id,name', 'taxes:id,name');

        return view('wine.edit', compact(
            'wine',
            'categories',
            'types',
            'regions',
            'grapes',
            'foodPairings',
            'dishes',
            'availableExtras',
            'taxes',
            'subcategories',
            'prepAreas'
        ));
    }

    public function update(Request $request, Wine $wine)
    {
        $request->merge([
            'subcategory_id' => $request->input('subcategory_id') ?: null,
        ]);

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'required|string',
            'price'             => 'required|numeric',
            'category_id'       => 'required|exists:wine_categories,id',
            'subcategory_id' => [
                'nullable',
                Rule::exists('wine_subcategories', 'id')->where(
                    fn ($query) => $query->where('wine_category_id', $request->input('category_id'))
                ),
            ],
            'type_id'           => 'nullable|exists:wine_types,id',
            'region_id'         => 'nullable|exists:regions,id',
            'grapes'            => 'nullable|array',
            'grapes.*'          => 'exists:grapes,id',
            'food_pairings'     => 'nullable|array',
            'food_pairings.*'   => 'exists:food_pairings,id',
            'dishes'            => 'nullable|array',   // ✅ validación
            'dishes.*'          => 'exists:dishes,id', // ✅ validación
            'extra_ids'         => ['nullable','array'],
            'extra_ids.*'       => ['integer','exists:extras,id'],
            'prep_label_id'     => ['nullable', 'integer', 'exists:prep_labels,id'],
            'image'             => 'nullable|image',    // ✅ faltaba la coma
            'featured_on_cover' => ['nullable', 'boolean'],
            'tax_ids'           => ['nullable', 'array'],
            'tax_ids.*'         => ['integer', 'exists:taxes,id'],
        ], [
            'description.required' => 'Falta la descripción del producto.',
        ]);
    
        $data = $validated;
    
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('wine_images', 'public');
        }
    
        $wine->update($data + [
            'visible' => $request->boolean('visible', true),
            'featured_on_cover' => $request->boolean('featured_on_cover'),
        ]);

        // ✅ Sincronizamos relaciones pivot
        $wine->grapes()->sync($request->input('grapes', []));
        $wine->foodPairings()->sync($request->input('food_pairings', []));
        $wine->dishes()->sync($request->input('dishes', [])); // ✅ ahora guarda los platos recomendados
        $wine->extras()->sync($request->input('extra_ids', []));
        $wine->prepLabels()->sync($this->collectPrepLabelIds($request));
        $wine->taxes()->sync($this->collectTaxIds($request));

    
        return redirect()->route('wines.edit', $wine)->with('success', 'Bebida actualizada con éxito.');
    }
    
    public function destroy(Wine $wine)
    {
        $wine->grapes()->detach();
        $wine->foodPairings()->detach();
        $wine->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'wines-section',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Bebida de café eliminada con éxito');
    }

    public function toggleVisibility(Wine $wine)
    {
        $wine->visible = !$wine->visible;
        $wine->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'wines-section',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Visibilidad del artículo actualizada');
    }

    public function toggleFeatured(Wine $wine)
    {
        $wine->featured_on_cover = !$wine->featured_on_cover;
        $wine->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'wines-section',
            'open' => 'wine-create',
            'expand' => 'wine-categories',
        ])->with('success', 'Destacado en portada actualizado');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:wine_categories,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:wines,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            Wine::where('id', $id)
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
