<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cocktail;
use App\Models\CocktailCategory;
use App\Models\Dish;
use App\Models\Wine;
use App\Models\WineCategory;

class ServerMenuController extends Controller
{
    public function menuCategories()
    {
        $categories = Category::with(['dishes' => function ($query) {
            $query->where('visible', true)
                ->orderBy('position')
                ->orderBy('id')
                ->with([
                    'subcategory:id,name,category_id',
                    'recommendedDishes:id,name,price',
                    'wines:id,name,price',
                    'cocktails:id,name,price',
                    'extras:id,name,group_name,group_required,max_select,min_select,kind,price,description,active',
                ]);
        }])->orderBy('order')->get();

        $categories = $categories
            ->filter(fn (Category $category) => $category->dishes->isNotEmpty())
            ->values();

        return response()->json([
            'categories' => $categories->map(fn (Category $category) => $this->serializeMenuCategory($category)),
        ]);
    }

    public function cocktailCategories()
    {
        $categories = CocktailCategory::with(['items' => function ($query) {
            $query->where('visible', true)
                ->orderBy('position')
                ->orderBy('id')
                ->with([
                    'subcategory:id,name,cocktail_category_id',
                    'dishes:id,name,price',
                    'extras:id,name,group_name,group_required,max_select,min_select,kind,price,description,active',
                ]);
        }])->orderBy('order')->get();

        $categories = $categories
            ->filter(fn (CocktailCategory $category) => $category->items->isNotEmpty())
            ->values();

        return response()->json([
            'categories' => $categories->map(fn (CocktailCategory $category) => $this->serializeCocktailCategory($category)),
        ]);
    }

    public function wineCategories()
    {
        $categories = WineCategory::with(['items' => function ($query) {
            $query->where('visible', true)
                ->orderBy('position')
                ->orderBy('id')
                ->with([
                    'subcategory:id,name,wine_category_id',
                    'dishes:id,name,price',
                    'extras:id,name,group_name,group_required,max_select,min_select,kind,price,description,active',
                ]);
        }])->orderBy('order')->get();

        $categories = $categories
            ->filter(fn (WineCategory $category) => $category->items->isNotEmpty())
            ->values();

        return response()->json([
            'categories' => $categories->map(fn (WineCategory $category) => $this->serializeWineCategory($category)),
        ]);
    }

    private function serializeMenuCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'dishes' => $category->dishes->map(fn (Dish $dish) => $this->serializeItem($dish, $category->id, $category->name)),
        ];
    }

    private function serializeCocktailCategory(CocktailCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'dishes' => $category->items->map(fn (Cocktail $item) => $this->serializeItem($item, $category->id, $category->name)),
        ];
    }

    private function serializeWineCategory(WineCategory $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'order' => $category->order,
            'dishes' => $category->items->map(fn (Wine $item) => $this->serializeItem($item, $category->id, $category->name)),
        ];
    }

    private function serializeItem($item, int $categoryId, string $categoryName): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description ?? '',
            'price' => (float) ($item->price ?? 0),
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'subcategory_id' => $item->subcategory_id ?? null,
            'subcategory_name' => $item->subcategory?->name,
            'image' => $item->image ?? null,
            'visible' => (bool) ($item->visible ?? true),
            'featured_on_cover' => (bool) ($item->featured_on_cover ?? false),
            'position' => (int) ($item->position ?? 0),
            'upsells' => $this->buildUpsells($item),
            'extras' => $item->extras?->map(fn ($extra) => [
                'id' => $extra->id,
                'name' => $extra->name,
                'group_name' => $extra->group_name,
                'group_required' => (bool) $extra->group_required,
                'max_select' => $extra->max_select,
                'min_select' => $extra->min_select,
                'kind' => $extra->kind,
                'price' => (float) ($extra->price ?? 0),
                'description' => $extra->description,
                'active' => (bool) $extra->active,
            ]) ?? [],
        ];
    }

    private function buildUpsells($item): array
    {
        $upsells = collect();

        if ($item instanceof Dish) {
            $upsells = $upsells
                ->merge(($item->recommendedDishes ?? collect())->map(fn ($dish) => [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'price' => (float) ($dish->price ?? 0),
                    'type' => 'dish',
                ]))
                ->merge(($item->wines ?? collect())->map(fn ($wine) => [
                    'id' => $wine->id,
                    'name' => $wine->name,
                    'price' => (float) ($wine->price ?? 0),
                    'type' => 'wine',
                ]))
                ->merge(($item->cocktails ?? collect())->map(fn ($cocktail) => [
                    'id' => $cocktail->id,
                    'name' => $cocktail->name,
                    'price' => (float) ($cocktail->price ?? 0),
                    'type' => 'cocktail',
                ]));
        } elseif ($item instanceof Cocktail || $item instanceof Wine) {
            $upsells = $upsells->merge(($item->dishes ?? collect())->map(fn ($dish) => [
                'id' => $dish->id,
                'name' => $dish->name,
                'price' => (float) ($dish->price ?? 0),
                'type' => 'dish',
            ]));
        }

        return $upsells
            ->unique(fn ($upsell) => $upsell['type'] . '-' . $upsell['id'])
            ->values()
            ->all();
    }
}
