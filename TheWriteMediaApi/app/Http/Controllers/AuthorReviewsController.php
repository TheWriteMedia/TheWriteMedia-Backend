<?php

namespace App\Http\Controllers;

use App\Models\AuthorReviews;
use Illuminate\Http\Request;

class AuthorReviewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       // Retrieve all services
       $author_reviews = AuthorReviews::all();

       return response()->json([
           'status' => 'success',
           'message' => 'Author Reviews retrieved successfully',
           'author reviews' => $author_reviews,
       ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'author_name' => 'required|string|max:1000',
            'author_type' => 'required|string|max:1000',
            'img_url' => 'required|string|max:1000',
            'review_message' => 'required|string|max:1000',
        ]);
    
        $authorReview = AuthorReviews::create([
            'author_name' => $request->author_name,
            'author_type' => $request->author_type,
            'img_url' => $request->img_url,
            'review_message' => $request->review_message, 
            'status' => 'ACTIVE', // Default status
        ]);

        return response()->json($authorReview, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AuthorReviews $authorReviews)
    {
         // Return the specified service
       return response()->json([
        'status' => 'success',
        'message' => 'Author Review retrieved successfully',
        'author review' => $authorReviews,
    ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AuthorReviews $authorReviews)
    {
        // Validate only the fields provided in the request
    $fields = $request->validate([
        'author_name' => 'required|string|max:1000',
        'author_type' => 'required|string|max:1000',
        'img_url' => 'required|string|max:1000',
        'review_message' => 'required|string|max:1000',
    ]);

     // Update book fair details
     $authorReviews->update($fields);

    return response()->json([
        'message' => 'Author Review updated successfully.',
        'Author Review' => $authorReviews
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AuthorReviews $authorReviews)
    {
        // Delete the service
        $authorReviews->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Author Review deleted successfully',
        ], 200);
    }
}
