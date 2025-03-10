<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
   
   
        $reviews = Review::with('book')->latest()->get();
    

    return response()->json([
        'reviews' => $reviews
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_id' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'review_message' => 'required|string|max:1000',
        ]);

        $review = Review::create([
            'book_id' => $request->book_id,
            'rating' => $request->rating,
            'review_message' => $request->review_message,
            'status' => 'PENDING', // Default status
        ]);

        return response()->json($review, 201);
    }


    public function approve(Review $review)
    {
        $review->update(['status' => 'ACTIVE']);
        return response()->json(['message' => 'Review approved successfully']);
    }

    // Decline a review (Admin only)
    public function decline(Review $review)
    {
        $review->update(['status' => 'DECLINED']);
        return response()->json(['message' => 'Review declined successfully']);
    }

    // Delete a review (Admin only)
    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json(['message' => 'Review deleted successfully']);
    }



    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

}
