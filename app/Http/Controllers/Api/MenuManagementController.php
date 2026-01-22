<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class MenuManagementController extends Controller
{
    public function categories()
    {
        $categories = Category::with(['dishes' => function ($query) {
            $query->orderBy('position')
                ->orderBy('id')
                ->with([
                    'subcategory:id,name,category_id',
                    'recommendedDishes:id,name',
                    'extras:id,name,price,view_scope',
                    'prepLabels:id,name,prep_area_id',
                    'taxes:id,name,rate',
                ]);
        }])->orderBy('order')->get();

        return response()->json([
            'categories' => $categories->map(fn (Category $category) => $this->serializeCategory($category)),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['order'] = (Category::max('order') ?? 0) + 1;

        $category = Category::create($data);

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'category' => $this->serializeCategory($category),
        ], Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $this->validateCategory($request);
        $category->update($data);

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'category' => $this->serializeCategory($category->fresh('dishes')),
        ]);
    }

    public function destroyCategory(Category $category)
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
            'order.*' => ['integer', 'exists:categories,id'],
        ]);

        foreach ($data['order'] as $index => $categoryId) {
            Category::where('id', $categoryId)->update(['order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden de categorías actualizado.',
        ]);
    }

    public function storeDish(Request $request)
    {
        [$data, $relations] = $this->validateDish($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('dish_images', 'public');
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);
        $data['position'] = (Dish::where('category_id', $data['category_id'])->max('position') ?? 0) + 1;

        $dish = Dish::create($data);
        $this->syncRelations($dish, $relations);

        return response()->json([
            'message' => 'Plato creado correctamente.',
            'dish' => $this->serializeDish($dish->fresh(['category', 'subcategory'])),
        ], Response::HTTP_CREATED);
    }

    public function updateDish(Request $request, Dish $dish)
    {
        [$data, $relations] = $this->validateDish($request, $dish->id);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('dish_images', 'public');
            if ($dish->image) {
                Storage::disk('public')->delete($dish->image);
            }
            $data['image'] = $newPath;
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);

        $dish->update($data);
        $this->syncRelations($dish, $relations);

        return response()->json([
            'message' => 'Plato actualizado correctamente.',
            'dish' => $this->serializeDish($dish->fresh(['category', 'subcategory'])),
        ]);
    }

    public function destroyDish(Dish $dish)
    {
        if ($dish->image) {
            Storage::disk('public')->delete($dish->image);
        }

        $dish->delete();

        return response()->json([
            'message' => 'Plato eliminado.',
        ]);
    }

    public function toggleDish(Dish $dish)
    {
        $dish->visible = !$dish->visible;
        $dish->save();

        return response()->json([
            'message' => 'Visibilidad actualizada.',
            'dish' => $this->serializeDish($dish),
        ]);
    }

    public function reorderDishes(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => [
                'nullable',
                Rule::exists('category_subcategories', 'id')->where(
                    fn ($query) => $query->where('category_id', $request->input('category_id'))
                ),
            ],
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:dishes,id'],
        ]);

        foreach ($data['order'] as $index => $dishId) {
            Dish::where('id', $dishId)
                ->where('category_id', $data['category_id'])
                ->update(['position' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden actualizado.',
        ]);
    }

    protected function validateDish(Request $request, ?int $dishId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'featured_on_cover' => ['nullable', 'boolean'],
            'visible' => ['nullable', 'boolean'],
            'image' => [$request->hasFile('image') ? 'required' : 'nullable', 'image', 'max:5120'],
            'recommended_dishes' => ['nullable', 'array'],
            'recommended_dishes.*' => [
                'integer',
                'exists:dishes,id',
                $dishId ? Rule::notIn([$dishId]) : null,
            ],
            'extra_ids' => ['nullable', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
            'prep_label_ids' => ['nullable', 'array'],
            'prep_label_ids.*' => ['integer', 'exists:prep_labels,id'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ], [
            'description.required' => 'Falta la descripción del plato.',
        ]);

        unset($validated['image']);

        $relations = [
            'recommended_dishes' => collect($request->input('recommended_dishes', []))
                ->filter(fn ($id) => (int) $id !== (int) $dishId)
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

    protected function syncRelations(Dish $dish, array $relations): void
    {
        if (array_key_exists('recommended_dishes', $relations)) {
            $dish->recommendedDishes()->sync($relations['recommended_dishes']);
        }

        if (array_key_exists('extras', $relations)) {
            $dish->extras()->sync($relations['extras']);
        }

        if (array_key_exists('prep_labels', $relations)) {
            $dish->prepLabels()->sync($relations['prep_labels']);
        }

        if (array_key_exists('taxes', $relations)) {
            $dish->taxes()->sync($relations['taxes']);
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

    protected function serializeCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'show_on_cover' => (bool) $category->show_on_cover,
            'cover_title' => $category->cover_title,
            'cover_subtitle' => $category->cover_subtitle,
            'dishes' => $category->dishes?->map(fn (Dish $dish) => $this->serializeDish($dish))->values() ?? [],
        ];
    }

    protected function serializeDish(Dish $dish): array
    {
        $recommended = $dish->relationLoaded('recommendedDishes')
            ? $dish->recommendedDishes->map(fn (Dish $recommendedDish) => [
                'id' => $recommendedDish->id,
                'name' => $recommendedDish->name,
            ])->values()
            : $dish->recommendedDishes()->get(['id', 'name'])->map(fn (Dish $recommendedDish) => [
                'id' => $recommendedDish->id,
                'name' => $recommendedDish->name,
            ])->values();

        $extras = $dish->relationLoaded('extras')
            ? $dish->extras
            : $dish->extras()->get(['extras.id', 'name', 'price', 'view_scope']);
        $prepLabels = $dish->relationLoaded('prepLabels')
            ? $dish->prepLabels
            : $dish->prepLabels()->get(['prep_labels.id', 'name', 'prep_area_id']);
        $taxes = $dish->relationLoaded('taxes')
            ? $dish->taxes
            : $dish->taxes()->get(['taxes.id', 'name', 'rate']);

        return [
            'id' => $dish->id,
            'name' => $dish->name,
            'description' => $dish->description,
            'price' => (float) $dish->price,
            'category_id' => $dish->category_id,
            'category_name' => $dish->category?->name,
            'subcategory_id' => $dish->subcategory_id,
            'subcategory_name' => $dish->subcategory?->name,
            'image' => $dish->image ? asset('storage/' . $dish->image) : null,
            'visible' => (bool) $dish->visible,
            'featured_on_cover' => (bool) $dish->featured_on_cover,
            'position' => $dish->position,
            'recommended_dishes' => $recommended,
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
