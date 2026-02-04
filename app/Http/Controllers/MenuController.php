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
            $cocktailCategories = CocktailCategory::with(['items' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $category->items->where('visible', true)->isNotEmpty())
                ->values();

            $cocktailCategories->each(function ($category) {
                $category->setAttribute('scope', 'cocktails');
            });

            $categories = $categories->merge($cocktailCategories);
        }

        if ($visible($settings?->show_tab_wines)) {
            $wineCategories = WineCategory::with(['items' => function ($query) {
                    $query->where('visible', true)->orderBy('position')->orderBy('id');
                }])
                ->orderBy('order')
                ->get()
                ->filter(fn ($category) => $category->items->where('visible', true)->isNotEmpty())
                ->values();

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
                ->get()
                ->filter(fn ($category) => $category->items->where('visible', true)->isNotEmpty())
                ->values();

            $cantinaCategories->each(function ($category) {
                $category->setAttribute('scope', 'cantina');
            });

            $categories = $categories->merge($cantinaCategories);
        }

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
