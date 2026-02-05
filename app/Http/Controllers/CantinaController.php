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

        $cloverScopeMap = CloverCategory::select('clover_id', 'scope')
            ->get()
            ->keyBy('clover_id');

        $cantinaCategories = CantinaCategory::with(['items' => $itemQuery])
            ->orderBy('order')
            ->get()
            ->filter(function ($category) use ($cloverScopeMap) {
                if (empty($category->clover_id)) {
                    return true;
                }

                return ($cloverScopeMap[$category->clover_id]->scope ?? null) === 'cantina';
            });

        $popups = Popup::where('active', 1)
            ->where('view', 'cantina')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return view('cantina.index', compact('settings', 'cantinaCategories', 'popups'));
    }
}
