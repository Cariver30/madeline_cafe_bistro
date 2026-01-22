<?php

namespace App\Http\Controllers;

use App\Models\Grape;
use Illuminate\Http\Request;

class GrapeController extends Controller
{
    public function index()
    {
        $grapes = Grape::all();
        return view('grapes.index', compact('grapes'));
    }

    public function create()
    {
        return view('grapes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        Grape::create($request->all());

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])
                        ->with('success', 'Uva creada con éxito.');
    }

    public function edit(Grape $grape)
    {
        return view('grapes.edit', compact('grape'));
    }

    public function update(Request $request, Grape $grape)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $grape->update($request->all());

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])
                        ->with('success', 'Uva actualizada con éxito.');
    }

    public function destroy(Grape $grape)
    {
        $grape->delete();

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])
                        ->with('success', 'Uva eliminada con éxito.');
    }
}
