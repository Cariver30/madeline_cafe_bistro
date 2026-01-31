<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Popup;
use App\Models\Setting;

class MenuController extends Controller
{
    public function index()
    {
        $settings = Setting::first();

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

        $categories = Category::with([
                'dishes' => $dishQuery,
                'subcategories' => function ($query) use ($dishQuery) {
                    $query->orderBy('order')
                        ->orderBy('id')
                        ->with(['dishes' => $dishQuery]);
                },
            ])
            ->orderBy('order')
            ->get();

        $categories->each(function ($category) {
            $category->setAttribute('scope', 'menu');
            $category->setRelation('items', $category->dishes);
        });

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
