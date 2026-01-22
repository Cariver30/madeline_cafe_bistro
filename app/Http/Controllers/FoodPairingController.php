<?php

namespace App\Http\Controllers;

use App\Models\FoodPairing;
use App\Models\Dish;
use Illuminate\Http\Request;

class FoodPairingController extends Controller
{
    public function index()
    {
        $foodPairings = FoodPairing::with('dish')->get();
        return view('food-pairings.index', compact('foodPairings'));
    }

    public function create()
    {
        $dishes = Dish::all();
        return view('food-pairings.create', compact('dishes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dish_id' => 'required|exists:dishes,id'
        ]);

        FoodPairing::create($request->only('name', 'dish_id'));

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])->with('success', 'Maridaje creado con éxito.');
    }

    public function edit(FoodPairing $foodPairing)
    {
        $dishes = Dish::all();
        return view('food-pairings.edit', compact('foodPairing', 'dishes'));
    }

    public function update(Request $request, FoodPairing $foodPairing)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'dish_id' => 'required|exists:dishes,id'
        ]);

        $foodPairing->update($request->only('name', 'dish_id'));

        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])->with('success', 'Maridaje actualizado con éxito.');
    }

    public function destroy(FoodPairing $foodPairing)
    {
        $foodPairing->delete();
        return redirect()->route('admin.new-panel', ['section' => 'wines-section'])->with('success', 'Maridaje eliminado con éxito.');
    }
}
