<?php

namespace App\Http\Controllers;

use App\Models\CantinaCategory;
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

        return view('cantina.index', compact('settings', 'cantinaCategories'));
    }
}
