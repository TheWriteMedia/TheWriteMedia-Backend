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
    public function index(Request $request)
    {
        // Get the currently authenticated user
    $user = $request->user();

    // Check the user type
    if ($user->user_type === User::USER_TYPE_WEB_ADMIN) {
        // If the user is a web_admin, show all reviews (active and inactive)
        $reviews = Review::with('user')->latest()->get();
    } else {
        // If the user is an author, show only active reviews
        $reviews = Review::with('user')->where('status', 'ACTIVE')->latest()->get();
    }

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
            'rating' => 'required|integer|min:1|max:5',
            'review_message' => 'required|string|max:1000',
        ]);

        $review = Review::create([
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
