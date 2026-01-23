<?php

namespace App\Http\Controllers;

use App\Models\CantinaCategory;
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

        $cantinaCategories = CantinaCategory::with(['items' => $itemQuery])
            ->orderBy('order')
            ->get();

        $popups = Popup::where('active', 1)
            ->where('view', 'cantina')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return view('cantina.index', compact('settings', 'cantinaCategories', 'popups'));
    }
}
