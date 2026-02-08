<?php

namespace App\Http\Controllers;

use App\Models\CantinaCategory;
use App\Models\CloverCategory;
use App\Models\Popup;
use App\Models\Setting;

class CantinaController extends Controller
{
    public function index()
    {
        $settings = Setting::first();

        $itemQuery = function ($query) {
            $query->where('visible', true)
                ->orderBy('position')
                ->orderBy('id');
        };

        $cloverScopeMap = CloverCategory::select('clover_id', 'scope', 'parent_category_id')
            ->get()
            ->keyBy('clover_id');
        $matchesScope = function ($category, string $scope) use ($cloverScopeMap): bool {
            if (empty($category->clover_id)) {
                return true;
            }

            $meta = $cloverScopeMap[$category->clover_id] ?? null;
            if (! $meta || ($meta->scope ?? null) !== $scope) {
                return false;
            }

            if (! empty($meta->parent_category_id)) {
                return false;
            }

            return true;
        };

        $cantinaCategories = CantinaCategory::with(['items' => $itemQuery])
            ->orderBy('order')
            ->get()
            ->filter(fn ($category) => $matchesScope($category, 'cantina'))
            ->filter(function ($category) {
                $items = $category->items ?? collect();
                return $items->where('visible', true)->isNotEmpty();
            })
            ->values();

        $popups = Popup::where('active', 1)
            ->where('view', 'cantina')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return view('cantina.index', compact('settings', 'cantinaCategories', 'popups'));
    }
}
