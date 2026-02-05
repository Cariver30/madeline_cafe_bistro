<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CloverCategory;
use App\Models\Popup;
use App\Models\Setting;

class MenuController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        $visible = fn ($flag) => $flag ?? true;

        $dishQuery = function ($query) {
            $query->where('visible', true)
                ->with([
                    'subcategory:id,name,category_id',
                    'wines:id,name,price',
                    'cocktails:id,name,price',
                    'recommendedDishes:id,name,price',
                    'taxes:id,name,rate,active',
                    'extras' => function ($extraQuery) {
                        $extraQuery->select('extras.id', 'name', 'price', 'description', 'active');
                    },
                ])
                ->orderBy('position');
        };

        $categories = collect();
        $cloverScopeMap = CloverCategory::select('clover_id', 'scope')
            ->get()
            ->keyBy('clover_id');
        $matchesScope = function ($category, string $scope) use ($cloverScopeMap): bool {
            if (empty($category->clover_id)) {
                return true;
            }

            return ($cloverScopeMap[$category->clover_id]->scope ?? null) === $scope;
        };

        if ($visible($settings?->show_tab_menu)) {
            $menuCategories = Category::with([
                    'dishes' => $dishQuery,
                    'subcategories' => function ($query) use ($dishQuery) {
                        $query->orderBy('order')
                            ->orderBy('id')
                            ->with(['dishes' => $dishQuery]);
                    },
                ])
                ->orderBy('order')
                ->get();

            $menuCategories->each(function ($category) {
                $category->setAttribute('scope', 'menu');
                $category->setRelation('items', $category->dishes);
            });

            $menuCategories = $menuCategories->filter(fn ($category) => $matchesScope($category, 'menu'));
            $categories = $categories->merge($menuCategories);
        }

        $categories = $categories->filter(function ($category) {
            $items = $category->items ?? $category->dishes ?? collect();
            if ($items->where('visible', true)->isNotEmpty()) {
                return true;
            }

            $subcategories = $category->subcategories ?? collect();
            foreach ($subcategories as $subcategory) {
                $subItems = $subcategory->items ?? $subcategory->dishes ?? collect();
                if ($subItems->where('visible', true)->isNotEmpty()) {
                    return true;
                }
            }

            return false;
        })->values();

        $popups = Popup::where('active', 1)
            ->where('view', 'menu')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return view('menu', [
            'settings' => $settings,
            'categories' => $categories,
            'popups' => $popups,
            'orderMode' => false,
        ]);
    }
}
