<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PrepArea;

class KitchenAreaController extends Controller
{
    public function index()
    {
        $areas = PrepArea::with(['labels' => function ($query) {
            $query->where('active', true)->orderBy('name');
        }])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'areas' => $areas,
        ]);
    }
}
