<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CocktailManagementController extends Controller
{
    public function categories()
    {
        $categories = CocktailCategory::with(['items' => function ($query) {
            $query->orderBy('position')
                ->orderBy('id')
                ->with([
                    'dishes:id,name',
                    'extras:id,name,price,view_scope',
                    'prepLabels:id,name,prep_area_id',
                    'taxes:id,name,rate',
                ]);
        }])->orderBy('order')->get();

        return response()->json([
            'categories' => $categories->map(fn (CocktailCategory $category) => $this->serializeCategory($category)),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['order'] = (CocktailCategory::max('order') ?? 0) + 1;

        $category = CocktailCategory::create($data);

        return response()->json([
            'message' => 'Categoría creada.',
            'category' => $this->serializeCategory($category),
        ], Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, CocktailCategory $category)
    {
        $data = $this->validateCategory($request);
        $category->update($data);

        return response()->json([
            'message' => 'Categoría actualizada.',
            'category' => $this->serializeCategory($category->fresh('items')),
        ]);
    }

    public function destroyCategory(CocktailCategory $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Categoría eliminada.',
        ]);
    }

    public function reorderCategories(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:cocktail_categories,id'],
        ]);

        foreach ($data['order'] as $index => $categoryId) {
            CocktailCategory::where('id', $categoryId)->update([
                'order' => $index + 1,
                'manual_order' => true,
            ]);
        }

        return response()->json([
            'message' => 'Orden de categorías actualizado.',
        ]);
    }

    public function store(Request $request)
    {
        [$data, $relations] = $this->validateCocktail($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cocktail_images', 'public');
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);
        $data['position'] = (Cocktail::where('category_id', $data['category_id'])->max('position') ?? 0) + 1;

        $cocktail = Cocktail::create($data);
        $this->syncRelations($cocktail, $relations);

        return response()->json([
            'message' => 'Cóctel creado correctamente.',
            'item' => $this->serializeCocktail($cocktail->fresh('category')),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, Cocktail $cocktail)
    {
        [$data, $relations] = $this->validateCocktail($request, $cocktail->id);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('cocktail_images', 'public');
            if ($cocktail->image) {
                Storage::disk('public')->delete($cocktail->image);
            }
            $data['image'] = $newPath;
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);

        $cocktail->update($data);
        $this->syncRelations($cocktail, $relations);

        return response()->json([
            'message' => 'Cóctel actualizado.',
            'item' => $this->serializeCocktail($cocktail->fresh(['category', 'dishes'])),
        ]);
    }

    public function destroy(Cocktail $cocktail)
    {
        if ($cocktail->image) {
            Storage::disk('public')->delete($cocktail->image);
        }

        $cocktail->delete();

        return response()->json([
            'message' => 'Cóctel eliminado.',
        ]);
    }

    public function toggle(Cocktail $cocktail)
    {
        $cocktail->visible = !$cocktail->visible;
        $cocktail->save();

        return response()->json([
            'message' => 'Visibilidad actualizada.',
            'item' => $this->serializeCocktail($cocktail),
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:cocktail_categories,id'],
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:cocktails,id'],
        ]);

        foreach ($data['order'] as $index => $cocktailId) {
            Cocktail::where('id', $cocktailId)
                ->where('category_id', $data['category_id'])
                ->update(['position' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden actualizado.',
        ]);
    }

    protected function validateCocktail(Request $request, ?int $cocktailId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:cocktail_categories,id'],
            'visible' => ['nullable', 'boolean'],
            'featured_on_cover' => ['nullable', 'boolean'],
            'image' => [$request->hasFile('image') ? 'required' : 'nullable', 'image', 'max:5120'],
            'recommended_dishes' => ['nullable', 'array'],
            'recommended_dishes.*' => ['integer', 'exists:dishes,id'],
            'extra_ids' => ['nullable', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
            'prep_label_ids' => ['nullable', 'array'],
            'prep_label_ids.*' => ['integer', 'exists:prep_labels,id'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ];

        $validated = $request->validate($rules, [
            'description.required' => 'Falta la descripción del cóctel.',
        ]);

        unset($validated['image']);

        $relations = [
            'recommended_dishes' => collect($request->input('recommended_dishes', []))
                ->unique()
                ->values()
                ->all(),
            'extras' => collect($request->input('extra_ids', []))
                ->unique()
                ->values()
                ->all(),
            'prep_labels' => collect($request->input('prep_label_ids', []))
                ->unique()
                ->values()
                ->all(),
            'taxes' => collect($request->input('tax_ids', []))
                ->unique()
                ->values()
                ->all(),
        ];

        return [$validated, $relations];
    }

    protected function syncRelations(Cocktail $cocktail, array $relations): void
    {
        if (array_key_exists('recommended_dishes', $relations)) {
            $cocktail->dishes()->sync($relations['recommended_dishes']);
        }

        if (array_key_exists('extras', $relations)) {
            $cocktail->extras()->sync($relations['extras']);
        }

        if (array_key_exists('prep_labels', $relations)) {
            $cocktail->prepLabels()->sync($relations['prep_labels']);
        }

        if (array_key_exists('taxes', $relations)) {
            $cocktail->taxes()->sync($relations['taxes']);
        }
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

    protected function serializeCategory(CocktailCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'show_on_cover' => (bool) $category->show_on_cover,
            'cover_title' => $category->cover_title,
            'cover_subtitle' => $category->cover_subtitle,
            'dishes' => $category->items?->map(fn (Cocktail $cocktail) => $this->serializeCocktail($cocktail))->values() ?? [],
        ];
    }

    protected function serializeCocktail(Cocktail $cocktail): array
    {
        $recommended = $cocktail->relationLoaded('dishes')
            ? $cocktail->dishes->map(fn (Dish $dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
            ])->values()
            : $cocktail->dishes()->get(['id', 'name'])->map(fn (Dish $dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
            ])->values();

        $extras = $cocktail->relationLoaded('extras')
            ? $cocktail->extras
            : $cocktail->extras()->get(['extras.id', 'name', 'price', 'view_scope']);
        $prepLabels = $cocktail->relationLoaded('prepLabels')
            ? $cocktail->prepLabels
            : $cocktail->prepLabels()->get(['prep_labels.id', 'name', 'prep_area_id']);
        $taxes = $cocktail->relationLoaded('taxes')
            ? $cocktail->taxes
            : $cocktail->taxes()->get(['taxes.id', 'name', 'rate']);

        return [
            'id' => $cocktail->id,
            'name' => $cocktail->name,
            'description' => $cocktail->description,
            'price' => (float) $cocktail->price,
            'category_id' => $cocktail->category_id,
            'category_name' => $cocktail->category?->name,
            'image' => $cocktail->image ? asset('storage/' . $cocktail->image) : null,
            'visible' => (bool) $cocktail->visible,
            'featured_on_cover' => (bool) $cocktail->featured_on_cover,
            'position' => $cocktail->position,
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
