<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CantinaCategory;
use App\Models\CocktailCategory;
use App\Models\Popup;
use App\Models\Setting;
use App\Models\WineCategory;

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

            $categories = $categories->merge($menuCategories);
        }

        if ($visible($settings?->show_tab_cocktails)) {
            $cocktailItemQuery = function ($query) {
                $query->where('visible', true)->orderBy('position')->orderBy('id');
            };
            $cocktailCategories = CocktailCategory::with([
                    'items' => $cocktailItemQuery,
                    'subcategories' => function ($query) use ($cocktailItemQuery) {
                        $query->orderBy('order')
                            ->orderBy('id')
                            ->with(['items' => $cocktailItemQuery]);
                    },
                ])
                ->orderBy('order')
                ->get();

            $cocktailCategories->each(function ($category) {
                $category->setAttribute('scope', 'cocktails');
            });

            $categories = $categories->merge($cocktailCategories);
        }

        if ($visible($settings?->show_tab_wines)) {
            $wineItemQuery = function ($query) {
                $query->where('visible', true)->orderBy('position')->orderBy('id');
            };
            $wineCategories = WineCategory::with([
                    'items' => $wineItemQuery,
                    'subcategories' => function ($query) use ($wineItemQuery) {
                        $query->orderBy('order')
                            ->orderBy('id')
                            ->with(['items' => $wineItemQuery]);
                    },
                ])
                ->orderBy('order')
                ->get();

            $wineCategories->each(function ($category) {
                $category->setAttribute('scope', 'wines');
            });

            $categories = $categories->merge($wineCategories);
        }

        if ($visible($settings?->show_tab_cantina)) {
            $cantinaCategories = CantinaCategory::with(['items' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get();

            $cantinaCategories->each(function ($category) {
                $category->setAttribute('scope', 'cantina');
            });

            $categories = $categories->merge($cantinaCategories);
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
