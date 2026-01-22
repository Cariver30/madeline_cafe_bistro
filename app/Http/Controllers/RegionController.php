<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::all();
        return view('regions.index', compact('regions'));
    }

    public function create()
    {
        return view('regions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        Region::create($request->all());

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])->with('success', 'Región creada con éxito.');
    }

    public function edit(Region $region)
    {
        return view('regions.edit', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $region->update($request->all());

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])->with('success', 'Región actualizada con éxito.');
    }

    public function destroy(Region $region)
    {
        $region->delete();

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])->with('success', 'Región eliminada con éxito.');
    }
}
