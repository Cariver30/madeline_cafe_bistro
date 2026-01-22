<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tax;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Carga las categorías y sus platos relacionados
        $categories = Category::with('dishes')->orderBy('order')->get();
        return view('categories.index', ['categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $taxes = Tax::orderBy('name')->get();

        return view('categories.create', compact('taxes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        $data['order'] = (Category::max('order') ?? 0) + 1;
        $data['show_on_cover'] = $request->boolean('show_on_cover');

        $category = Category::create($data);
        $category->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('admin.new-panel', [
            'section' => 'menu-section',
            'open' => 'menu-create',
            'expand' => 'dish-categories',
        ])->with('success', 'Categoría creada con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $taxes = Tax::orderBy('name')->get();
        $category->loadMissing('taxes:id,name');

        return view('categories.edit', compact('category', 'taxes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
            'tax_ids' => ['nullable', 'array'],
            'tax_ids.*' => ['integer', 'exists:taxes,id'],
        ]);

        $data['show_on_cover'] = $request->boolean('show_on_cover');
        $data['cover_title'] = filled($data['cover_title']) ? $data['cover_title'] : null;
        $data['cover_subtitle'] = filled($data['cover_subtitle']) ? $data['cover_subtitle'] : null;

        if ($data['show_on_cover'] && blank($data['cover_title'])) {
            $data['cover_title'] = $data['name'];
        }

        $category->update($data);
        $category->taxes()->sync($this->collectTaxIds($request));

        return redirect()->route('admin.new-panel', [
            'section' => 'menu-section',
            'open' => 'menu-create',
            'expand' => 'dish-categories',
        ])->with('success', 'Categoría actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'menu-section',
            'open' => 'menu-create',
            'expand' => 'dish-categories',
        ])->with('success', 'Categoría eliminada con éxito.');
    }

    public function updateOrder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            Category::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function getCategoriesJson()
    {
        $categories = Category::all(); // Get all categories from your database
        return response()->json($categories); // Return the categories as a JSON response
    }

    public function toggleCover(Category $category)
    {
        $category->show_on_cover = !$category->show_on_cover;
        if ($category->show_on_cover && !$category->cover_title) {
            $category->cover_title = $category->name;
        }
        $category->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'menu-section',
            'open' => 'menu-create',
            'expand' => 'dish-categories',
        ])->with('success', 'Se actualizó la visibilidad en portada.');
    }

    public function updateFeaturedItems(Request $request, Category $category)
    {
        $data = $request->validate([
            'featured_items' => ['nullable', 'array'],
            'featured_items.*' => ['integer', 'exists:dishes,id'],
        ]);

        $ids = collect($data['featured_items'] ?? []);

        $category->load('dishes');

        foreach ($category->dishes as $dish) {
            $dish->featured_on_cover = $ids->contains($dish->id);
            $dish->save();
        }

        return redirect()->route('admin.new-panel', [
            'section' => 'menu-section',
            'open' => 'menu-create',
            'expand' => 'dish-categories',
        ])->with('success', 'Destacados actualizados.');
    }

    private function collectTaxIds(Request $request): array
    {
        return collect($request->input('tax_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
