<?php
namespace App\Http\Controllers;

use App\Models\WineType;
use Illuminate\Http\Request;

class WineTypeController extends Controller
{
    public function index()
    {
        $types = WineType::all();
        return view('wine-types.index', compact('types'));
    }

    public function create()
    {
        return view('wine-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        WineType::create($request->only('name'));

        return redirect()->route('wine-types.index')->with('success', 'Tipo de vino creado correctamente.');
    }

    public function edit(WineType $wineType)
    {
        return view('wine-types.edit', compact('wineType'));
    }

    public function update(Request $request, WineType $wineType)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $wineType->update($request->only('name'));

        return redirect()->route('wine-types.index')->with('success', 'Tipo de vino actualizado.');
    }

    public function destroy(WineType $wineType)
    {
        $wineType->delete();
        return redirect()->route('wine-types.index')->with('success', 'Tipo de vino eliminado.');
    }
}
