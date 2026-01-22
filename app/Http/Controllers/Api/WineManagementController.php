<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wine;
use App\Models\WineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class WineManagementController extends Controller
{
    public function categories()
    {
        $categories = WineCategory::with(['items' => function ($query) {
            $query->orderBy('position')
                ->orderBy('id')
                ->with([
                    'type',
                    'region',
                    'grapes',
                    'foodPairings',
                    'dishes',
                    'extras',
                    'prepLabels:id,name,prep_area_id',
                    'taxes:id,name,rate',
                ]);
        }])->orderBy('order')->get();

        return response()->json([
            'categories' => $categories->map(fn (WineCategory $category) => $this->serializeCategory($category)),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['order'] = (WineCategory::max('order') ?? 0) + 1;

        $category = WineCategory::create($data);

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'category' => $this->serializeCategory($category),
        ], Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, WineCategory $category)
    {
        $data = $this->validateCategory($request);
        $category->update($data);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'category' => $this->serializeCategory($category->fresh('items')),
        ]);
    }

    public function destroyCategory(WineCategory $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }

    public function reorderCategories(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:wine_categories,id'],
        ]);

        foreach ($data['order'] as $index => $categoryId) {
            WineCategory::where('id', $categoryId)->update(['order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden de categorías actualizado.',
        ]);
    }

    public function store(Request $request)
    {
        [$data, $relations] = $this->validateWine($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('wine_images', 'public');
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);
        $data['position'] = (Wine::where('category_id', $data['category_id'])->max('position') ?? 0) + 1;

        $wine = Wine::create($data);
        $this->syncRelations($wine, $relations);

        return response()->json([
            'message' => 'Bebida creada correctamente.',
            'item' => $this->serializeWine(
                $wine->fresh(['category', 'type', 'region', 'grapes', 'foodPairings', 'dishes'])
            ),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Wine $wine)
    {
        [$data, $relations] = $this->validateWine($request, $wine->id);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('wine_images', 'public');
            if ($wine->image) {
                Storage::disk('public')->delete($wine->image);
            }
            $data['image'] = $newPath;
        }

        $data['visible'] = $request->boolean('visible', $wine->visible);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', $wine->featured_on_cover);

        $wine->update($data);
        $this->syncRelations($wine, $relations);

        return response()->json([
            'message' => 'Bebida actualizada.',
            'item' => $this->serializeWine(
                $wine->fresh(['category', 'type', 'region', 'grapes', 'foodPairings', 'dishes'])
            ),
        ]);
    }

    public function destroy(Wine $wine)
    {
        if ($wine->image) {
            Storage::disk('public')->delete($wine->image);
        }

        $wine->grapes()->detach();
        $wine->foodPairings()->detach();
        $wine->dishes()->detach();
        $wine->delete();

        return response()->json([
            'message' => 'Bebida eliminada.',
        ]);
    }

    public function toggle(Wine $wine)
    {
        $wine->visible = !$wine->visible;
        $wine->save();

        return response()->json([
            'message' => 'Visibilidad actualizada.',
            'item' => $this->serializeWine(
                $wine->load(['category', 'type', 'region', 'grapes', 'foodPairings', 'dishes'])
            ),
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:wine_categories,id'],
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:wines,id'],
        ]);

        foreach ($data['order'] as $index => $wineId) {
            Wine::where('id', $wineId)
                ->where('category_id', $data['category_id'])
                ->update(['position' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden actualizado.',
        ]);
    }

    /**
     * @return array{0: array, 1: array<string, array<int>>}
     */
    protected function validateWine(Request $request, ?int $wineId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:wine_categories,id'],
            'type_id' => ['nullable', 'exists:wine_types,id'],
            'region_id' => ['nullable', 'exists:regions,id'],
            'featured_on_cover' => ['nullable', 'boolean'],
            'visible' => ['nullable', 'boolean'],
            'grapes' => ['nullable', 'array'],
            'grapes.*' => ['integer', 'exists:grapes,id'],
            'food_pairings' => ['nullable', 'array'],
            'food_pairings.*' => ['integer', 'exists:food_pairings,id'],
            'recommended_dishes' => ['nullable', 'array'],
            'recommended_dishes.*' => ['integer', 'exists:dishes,id'],
            'extra_ids' => ['nullable', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
            'prep_label_ids' => ['nullable', 'array'],
            'prep_label_ids.*' => ['integer', 'exists:prep_labels,id'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
            'image' => [$request->hasFile('image') ? 'required' : 'nullable', 'image', 'max:5120'],
        ], [
            'description.required' => 'Falta la descripción de la bebida.',
        ]);

        $recommended = $request->input(
            'recommended_dishes',
            $request->input('dishes', [])
        );

        $relations = [
            'grapes' => $validated['grapes'] ?? [],
            'food_pairings' => $validated['food_pairings'] ?? [],
            'recommended_dishes' => collect($recommended)->unique()->values()->all(),
            'extras' => collect($request->input('extra_ids', []))->unique()->values()->all(),
            'prep_labels' => collect($request->input('prep_label_ids', []))->unique()->values()->all(),
            'taxes' => collect($request->input('tax_ids', []))->unique()->values()->all(),
        ];

        unset(
            $validated['image'],
            $validated['grapes'],
            $validated['food_pairings'],
            $validated['recommended_dishes']
        );
        unset($validated['extra_ids']);
        unset($validated['tax_ids']);

        return [$validated, $relations];
    }

    protected function syncRelations(Wine $wine, array $relations): void
    {
        $wine->grapes()->sync($relations['grapes']);
        $wine->foodPairings()->sync($relations['food_pairings']);
        $wine->dishes()->sync($relations['recommended_dishes']);
        $wine->extras()->sync($relations['extras']);
        $wine->prepLabels()->sync($relations['prep_labels']);
        $wine->taxes()->sync($relations['taxes'] ?? []);
    }

    protected function validateCategory(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
        ]);

        $data['show_on_cover'] = $request->boolean('show_on_cover', false);
        $data['cover_title'] = filled($data['cover_title']) ? $data['cover_title'] : null;
        $data['cover_subtitle'] = filled($data['cover_subtitle']) ? $data['cover_subtitle'] : null;

        if ($data['show_on_cover'] && blank($data['cover_title'])) {
            $data['cover_title'] = $data['name'];
        }

        return $data;
    }

    protected function serializeCategory(WineCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'show_on_cover' => (bool) $category->show_on_cover,
            'cover_title' => $category->cover_title,
            'cover_subtitle' => $category->cover_subtitle,
            'dishes' => $category->items?->map(fn (Wine $wine) => $this->serializeWine($wine))->values() ?? [],
        ];
    }

    protected function serializeWine(Wine $wine): array
    {
        $recommended = $wine->relationLoaded('dishes')
            ? $wine->dishes->map(fn ($dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
            ])->values()
            : $wine->dishes()->get(['id', 'name'])->map(fn ($dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
            ])->values();

        $extras = $wine->relationLoaded('extras')
            ? $wine->extras
            : $wine->extras()->get(['extras.id', 'name', 'price', 'view_scope']);
        $prepLabels = $wine->relationLoaded('prepLabels')
            ? $wine->prepLabels
            : $wine->prepLabels()->get(['prep_labels.id', 'name', 'prep_area_id']);
        $taxes = $wine->relationLoaded('taxes')
            ? $wine->taxes
            : $wine->taxes()->get(['taxes.id', 'name', 'rate']);

        return [
            'id' => $wine->id,
            'name' => $wine->name,
            'description' => $wine->description,
            'price' => (float) $wine->price,
            'category_id' => $wine->category_id,
            'category_name' => $wine->category?->name,
            'type_id' => $wine->type_id,
            'type_name' => $wine->type?->name,
            'region_id' => $wine->region_id,
            'region_name' => $wine->region?->name,
            'image' => $wine->image ? asset('storage/' . $wine->image) : null,
            'visible' => (bool) $wine->visible,
            'featured_on_cover' => (bool) $wine->featured_on_cover,
            'position' => $wine->position,
            'grapes' => $wine->relationLoaded('grapes') ? $wine->grapes->map(fn ($grape) => [
                'id' => $grape->id,
                'name' => $grape->name,
            ])->values() : [],
            'food_pairings' => $wine->relationLoaded('foodPairings') ? $wine->foodPairings->map(fn ($pairing) => [
                'id' => $pairing->id,
                'name' => $pairing->name,
            ])->values() : [],
            'recommended_dishes' => $recommended,
            'dishes' => $recommended,
            'extras' => $extras->map(fn ($extra) => [
                'id' => $extra->id,
                'name' => $extra->name,
                'price' => (float) $extra->price,
                'view_scope' => $extra->view_scope,
            ])->values(),
            'prep_labels' => $prepLabels->map(fn ($label) => [
                'id' => $label->id,
                'name' => $label->name,
                'prep_area_id' => $label->prep_area_id,
            ])->values(),
            'taxes' => $taxes->map(fn ($tax) => [
                'id' => $tax->id,
                'name' => $tax->name,
                'rate' => (float) $tax->rate,
            ])->values(),
        ];
    }
}
