<?php

namespace App\Http\Controllers;

use App\Models\CantinaCategory;
use App\Models\CantinaItem;
use Illuminate\Http\Request;

class CantinaItemController extends Controller
{
    public function create()
    {
        $categories = CantinaCategory::orderBy('order')->get();

        return view('cantina.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:cantina_categories,id',
            'image' => 'nullable|image',
            'visible' => ['nullable', 'boolean'],
            'featured_on_cover' => ['nullable', 'boolean'],
        ]);

        $validated['visible'] = $request->boolean('visible', true);
        $validated['featured_on_cover'] = $request->boolean('featured_on_cover');
        $validated['position'] = (CantinaItem::where('category_id', $validated['category_id'])->max('position') ?? 0) + 1;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('cantina_images', 'public');
        }

        $cantinaItem = CantinaItem::create($validated);

        return redirect()->route('cantina-items.edit', $cantinaItem)->with('success', 'Artículo de cantina creado con éxito.');
    }

    public function edit(CantinaItem $cantinaItem)
    {
        $categories = CantinaCategory::orderBy('order')->get();

        return view('cantina.edit', compact('cantinaItem', 'categories'));
    }

    public function update(Request $request, CantinaItem $cantinaItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:cantina_categories,id',
            'image' => 'nullable|image',
            'visible' => ['nullable', 'boolean'],
            'featured_on_cover' => ['nullable', 'boolean'],
        ]);

        $validated['visible'] = $request->boolean('visible', true);
        $validated['featured_on_cover'] = $request->boolean('featured_on_cover');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('cantina_images', 'public');
        }

        if ($cantinaItem->category_id !== (int) $validated['category_id']) {
            $validated['position'] = (CantinaItem::where('category_id', $validated['category_id'])->max('position') ?? 0) + 1;
        }

        $cantinaItem->update($validated);

        return redirect()->route('cantina-items.edit', $cantinaItem)->with('success', 'Artículo de cantina actualizado con éxito.');
    }

    public function destroy(CantinaItem $cantinaItem)
    {
        $cantinaItem->delete();

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina-section',
            'open' => 'cantina-create',
        ])->with('success', 'Artículo de cantina eliminado con éxito.');
    }

    public function toggleVisibility(CantinaItem $cantinaItem)
    {
        $cantinaItem->visible = ! $cantinaItem->visible;
        $cantinaItem->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina-section',
            'open' => 'cantina-create',
        ])->with('success', 'Visibilidad del artículo de cantina actualizada.');
    }

    public function toggleFeatured(CantinaItem $cantinaItem)
    {
        $cantinaItem->featured_on_cover = ! $cantinaItem->featured_on_cover;
        $cantinaItem->save();

        return redirect()->route('admin.new-panel', [
            'section' => 'cantina-section',
            'open' => 'cantina-create',
        ])->with('success', 'Destacado en portada actualizado.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:cantina_categories,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:cantina_items,id',
        ]);

        foreach ($data['order'] as $index => $id) {
            CantinaItem::where('id', $id)
                ->where('category_id', $data['category_id'])
                ->update(['position' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
