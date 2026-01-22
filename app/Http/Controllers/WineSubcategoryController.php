<?php

namespace App\Http\Controllers;

use App\Models\WineCategory;
use App\Models\WineSubcategory;
use Illuminate\Http\Request;

class WineSubcategoryController extends Controller
{
    public function store(Request $request, WineCategory $wineCategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $wineCategory->subcategories()->create([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'expand' => 'wine-categories',
        ])->with('success', 'Subcategoría creada con éxito.');
    }

    public function update(Request $request, WineSubcategory $subcategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subcategory->update([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'expand' => 'wine-categories',
        ])->with('success', 'Subcategoría actualizada.');
    }

    public function destroy(WineSubcategory $subcategory)
    {
        $subcategory->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'wines',
            'expand' => 'wine-categories',
        ])->with('success', 'Subcategoría eliminada.');
    }
}
