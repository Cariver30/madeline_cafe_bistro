<?php

// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Category;
use App\Models\Popup;
use App\Support\FeaturedGroupBuilder;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function cover()
    {
        $settings = Setting::first();
        $featuredGroups = FeaturedGroupBuilder::build();
        $popups = Popup::where('active', 1)
            ->where('view', 'cover')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return view('cover', compact('settings', 'featuredGroups', 'popups'));
    }

    public function menu()
    {
        $settings = Setting::first();
        $categories = Category::with('dishes')->get();
        return view('menu', compact('settings', 'categories'));
    }
}
