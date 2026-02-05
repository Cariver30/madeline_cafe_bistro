<?php

namespace App\Http\Controllers;

use App\Models\CantinaCategory;
use Illuminate\Http\Request;

class CantinaCategoryController extends Controller
{
    public function index()
    {
        $categories = CantinaCategory::orderBy('order')->get();

        return view('cantina.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('cantina.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
        ]);

        $data['order'] = (CantinaCategory::max('order') ?? 0) + 1;
        $data['show_on_cover'] = $request->boolean('show_on_cover');

        CantinaCategory::create($data);

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina',
            'open' => 'cantina-create',
            'expand' => 'cantina-categories',
        ])->with('success', 'Categoría de cantina creada con éxito.');
    }

    public function edit(CantinaCategory $cantinaCategory)
    {
        return view('cantina.categories.edit', compact('cantinaCategory'));
    }

    public function update(Request $request, CantinaCategory $cantinaCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'show_on_cover' => ['nullable', 'boolean'],
            'cover_title' => ['nullable', 'string', 'max:255'],
            'cover_subtitle' => ['nullable', 'string', 'max:255'],
        ]);

        $data['show_on_cover'] = $request->boolean('show_on_cover');

        $cantinaCategory->update($data);

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina',
            'open' => 'cantina-create',
            'expand' => 'cantina-categories',
        ])->with('success', 'Categoría de cantina actualizada con éxito.');
    }

    public function destroy(CantinaCategory $cantinaCategory)
    {
        $cantinaCategory->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina',
            'open' => 'cantina-create',
            'expand' => 'cantina-categories',
        ])->with('success', 'Categoría de cantina eliminada con éxito.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:cantina_categories,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            CantinaCategory::where('id', $id)->update(['order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function toggleCover(CantinaCategory $cantinaCategory)
    {
        $cantinaCategory->show_on_cover = ! $cantinaCategory->show_on_cover;
        if ($cantinaCategory->show_on_cover && ! $cantinaCategory->cover_title) {
            $cantinaCategory->cover_title = $cantinaCategory->name;
        }
        $cantinaCategory->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina',
            'open' => 'cantina-create',
            'expand' => 'cantina-categories',
        ])->with('success', 'Categoría actualizada en la portada.');
    }

    public function updateFeaturedItems(Request $request, CantinaCategory $cantinaCategory)
    {
        $data = $request->validate([
            'featured_items' => ['nullable', 'array'],
            'featured_items.*' => ['integer', 'exists:cantina_items,id'],
        ]);

        $ids = collect($data['featured_items'] ?? []);
        $cantinaCategory->load('items');

        foreach ($cantinaCategory->items as $item) {
            $item->featured_on_cover = $ids->contains($item->id);
            $item->save();
        }

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina',
            'open' => 'cantina-create',
            'expand' => 'cantina-categories',
        ])->with('success', 'Destacados de cantina actualizados.');
    }
}
