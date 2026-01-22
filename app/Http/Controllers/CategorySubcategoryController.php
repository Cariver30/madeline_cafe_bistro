<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategorySubcategory;
use Illuminate\Http\Request;

class CategorySubcategoryController extends Controller
{
    public function store(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->subcategories()->create([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.new-panel', [
            'section' => 'menu',
            'expand' => 'dish-categories',
        ])->with('success', 'Subcategoría creada con éxito.');
    }

    public function update(Request $request, CategorySubcategory $subcategory)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $subcategory->update([
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.new-panel', [
            'section' => 'menu',
            'expand' => 'dish-categories',
        ])->with('success', 'Subcategoría actualizada.');
    }

    public function destroy(CategorySubcategory $subcategory)
    {
        $subcategory->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'menu',
            'expand' => 'dish-categories',
        ])->with('success', 'Subcategoría eliminada.');
    }
}
