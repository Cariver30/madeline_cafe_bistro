<?php

namespace App\Http\Controllers;

use App\Models\CocktailCategory;
use App\Models\CocktailSubcategory;
use Illuminate\Http\Request;

class CocktailSubcategoryController extends Controller
{
    public function store(Request $request, CocktailCategory $cocktailCategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $cocktailCategory->subcategories()->create([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Subcategoría creada con éxito.');
    }

    public function update(Request $request, CocktailSubcategory $subcategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subcategory->update([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Subcategoría actualizada.');
    }

    public function destroy(CocktailSubcategory $subcategory)
    {
        $subcategory->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'cocktails',
            'expand' => 'cocktail-categories',
        ])->with('success', 'Subcategoría eliminada.');
    }
}
