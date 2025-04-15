<?php

namespace App\Http\Controllers;

use App\Models\TotalAccumulatedRoyalty;
use Illuminate\Http\Request;

class TotalAccumulatedRoyaltyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the currently authenticated user
        $user = $request->user();
        
        // Get the total accumulated royalty for this user
        $royalty = TotalAccumulatedRoyalty::where('user_id', $user->id)
            ->first();
            
        // If no record exists, return 0 as default value
        $balance = $royalty ? $royalty->value : 0;
        
        return response()->json([
            'status' => 'success',
            'user_id' => $user->id,
            'total_accumulated_royalty' => $balance
        ]);
    }

    /**
     * Get the total accumulated royalty for a specific user
     */
    public function getUserRoyalty(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
        
        $royalty = TotalAccumulatedRoyalty::where('user_id', $request->user_id)
            ->first();
            
        $balance = $royalty ? $royalty->value : 0;
        
        return response()->json([
            'status' => 'success',
            'user_id' => $request->user_id,
            'total_accumulated_royalty' => $balance
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TotalAccumulatedRoyalty $totalAccumulatedRoyalty)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TotalAccumulatedRoyalty $totalAccumulatedRoyalty)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TotalAccumulatedRoyalty $totalAccumulatedRoyalty)
    {
        //
    }
}
