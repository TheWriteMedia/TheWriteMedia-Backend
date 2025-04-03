<?php

namespace App\Http\Controllers;

use App\Models\AddOn;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AddOnController extends Controller
{
    /**
     * Display a listing of all add-ons.
     */
    public function index(): JsonResponse
    {
        // Retrieve all services
        $addOns = AddOn::all();

        return response()->json([
            'status' => 'success',
            'message' => 'AddOns retrieved successfully',
            'addOns' => $addOns,
        ], 200);
    }

    /**
     * Store a newly created add-on in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rows' => 'required|array',
            'rows.*' => 'array|size:2', // Each row must have exactly 2 elements
        ]);

        $addOn = AddOn::create([
            'name' => $validated['name'],
            'rows' => $validated['rows'],
        ]);

        return response()->json($addOn, 201);
    }

    /**
     * Display the specified add-on.
     */
    public function show(AddOn $addOn): JsonResponse
    {
        return response()->json($addOn);
    }

    /**
     * Update the specified add-on in storage.
     */
    public function update(Request $request, AddOn $addOn): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'rows' => 'sometimes|array',
            'rows.*' => 'array|size:2', // Each row must have exactly 2 elements
        ]);

        $addOn->update($validated);
        return response()->json($addOn);
    }

    /**
     * Remove the specified add-on from storage.
     */
    public function destroy(AddOn $addOn): JsonResponse
    {
        $addOn->delete();
        return response()->json(null, 204);
    }
}