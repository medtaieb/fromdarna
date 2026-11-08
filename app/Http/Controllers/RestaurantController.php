<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    // Get all restaurants (GET /api/restaurants)
    public function index()
    {
        $restaurants = Restaurant::all();
        return response()->json($restaurants, 200);
    }

    // Create a new restaurant (POST /api/restaurants)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'image' => 'nullable|string',
            'is_open' => 'boolean',
        ]);

        $restaurant = Restaurant::create($validated);

        return response()->json($restaurant, 201);
    }

    // Get a specific restaurant (GET /api/restaurants/{id})
    public function show($id)
    {
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json(['message' => 'Restaurant not found'], 404);
        }

        return response()->json($restaurant, 200);
    }
}