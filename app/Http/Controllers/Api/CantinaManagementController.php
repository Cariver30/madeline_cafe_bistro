<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CantinaCategory;
use App\Models\CantinaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CantinaManagementController extends Controller
{
    public function categories()
    {
        $categories = CantinaCategory::with(['items' => function ($query) {
            $query->orderBy('position')
                ->orderBy('id')
                ->with([
                    'extras:id,name,price,view_scope',
                ]);
        }])->orderBy('order')->get();

        return response()->json([
            'categories' => $categories->map(fn (CantinaCategory $category) => $this->serializeCategory($category)),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['order'] = (CantinaCategory::max('order') ?? 0) + 1;

        $category = CantinaCategory::create($data);

        return response()->json([
            'message' => 'Categoría creada.',
            'category' => $this->serializeCategory($category),
        ], Response::HTTP_CREATED);
    }

    public function updateCategory(Request $request, CantinaCategory $category)
    {
        $data = $this->validateCategory($request);
        $category->update($data);

        return response()->json([
            'message' => 'Categoría actualizada.',
            'category' => $this->serializeCategory($category->fresh('items')),
        ]);
    }

    public function destroyCategory(CantinaCategory $category)
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
            'order.*' => ['integer', 'exists:cantina_categories,id'],
        ]);

        foreach ($data['order'] as $index => $categoryId) {
            CantinaCategory::where('id', $categoryId)->update(['order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden de categorías actualizado.',
        ]);
    }

    public function store(Request $request)
    {
        [$data, $relations] = $this->validateItem($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('cantina_images', 'public');
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);
        $data['position'] = (CantinaItem::where('category_id', $data['category_id'])->max('position') ?? 0) + 1;

        $item = CantinaItem::create($data);
        $this->syncRelations($item, $relations);

        return response()->json([
            'message' => 'Artículo creado correctamente.',
            'item' => $this->serializeItem($item->fresh('category')),
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, CantinaItem $cantinaItem)
    {
        [$data, $relations] = $this->validateItem($request, $cantinaItem->id);

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('cantina_images', 'public');
            if ($cantinaItem->image) {
                Storage::disk('public')->delete($cantinaItem->image);
            }
            $data['image'] = $newPath;
        }

        $data['visible'] = $request->boolean('visible', true);
        $data['featured_on_cover'] = $request->boolean('featured_on_cover', false);

        $cantinaItem->update($data);
        $this->syncRelations($cantinaItem, $relations);

        return response()->json([
            'message' => 'Artículo actualizado.',
            'item' => $this->serializeItem($cantinaItem->fresh(['category', 'extras'])),
        ]);
    }

    public function destroy(CantinaItem $cantinaItem)
    {
        if ($cantinaItem->image) {
            Storage::disk('public')->delete($cantinaItem->image);
        }

        $cantinaItem->delete();

        return response()->json([
            'message' => 'Artículo eliminado.',
        ]);
    }

    public function toggle(CantinaItem $cantinaItem)
    {
        $cantinaItem->visible = ! $cantinaItem->visible;
        $cantinaItem->save();

        return response()->json([
            'message' => 'Visibilidad actualizada.',
            'item' => $this->serializeItem($cantinaItem),
        ]);
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:cantina_categories,id'],
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:cantina_items,id'],
        ]);

        foreach ($data['order'] as $index => $itemId) {
            CantinaItem::where('id', $itemId)
                ->where('category_id', $data['category_id'])
                ->update(['position' => $index + 1]);
        }

        return response()->json([
            'message' => 'Orden actualizado.',
        ]);
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

    protected function validateItem(Request $request, ?int $itemId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'exists:cantina_categories,id'],
            'visible' => ['nullable', 'boolean'],
            'featured_on_cover' => ['nullable', 'boolean'],
            'image' => [$request->hasFile('image') ? 'required' : 'nullable', 'image', 'max:5120'],
            'recommended_dishes' => ['nullable', 'array'],
            'recommended_dishes.*' => ['integer'],
            'extra_ids' => ['nullable', 'array'],
            'extra_ids.*' => ['integer', 'exists:extras,id'],
            'prep_label_ids' => ['nullable', 'array'],
            'prep_label_ids.*' => ['integer'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer'],
        ];

        $validated = $request->validate($rules, [
            'description.required' => 'Falta la descripción del artículo.',
        ]);

        unset($validated['image']);

        $relations = [
            'extras' => collect($request->input('extra_ids', []))
                ->unique()
                ->values()
                ->all(),
        ];

        return [$validated, $relations];
    }

    protected function syncRelations(CantinaItem $item, array $relations): void
    {
        if (array_key_exists('extras', $relations)) {
            $item->extras()->sync($relations['extras']);
        }
    }

    protected function serializeCategory(CantinaCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'show_on_cover' => (bool) $category->show_on_cover,
            'cover_title' => $category->cover_title,
            'cover_subtitle' => $category->cover_subtitle,
            'dishes' => $category->items?->map(fn (CantinaItem $item) => $this->serializeItem($item, $category))->values() ?? [],
        ];
    }

    protected function serializeItem(CantinaItem $item, ?CantinaCategory $category = null): array
    {
        $extras = $item->relationLoaded('extras')
            ? $item->extras
            : $item->extras()->get(['extras.id', 'name', 'price', 'view_scope']);

        $category = $category ?? $item->category;

        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description ?? '',
            'price' => (float) $item->price,
            'category_id' => $item->category_id,
            'category_name' => $category?->name,
            'subcategory_id' => null,
            'subcategory_name' => null,
            'image' => $item->image ? asset('storage/' . $item->image) : null,
            'visible' => (bool) $item->visible,
            'featured_on_cover' => (bool) $item->featured_on_cover,
            'position' => $item->position ?? 0,
            'recommended_dishes' => [],
            'extras' => $extras->map(fn ($extra) => [
                'id' => $extra->id,
                'name' => $extra->name,
                'price' => (float) $extra->price,
                'view_scope' => $extra->view_scope,
            ])->values(),
            'prep_labels' => [],
            'taxes' => [],
        ];
    }
}
