<?php

namespace App\Http\Controllers;

use App\Models\UpcomingBookFair;
use Illuminate\Http\Request;

class UpcomingBookFairController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all services
        $upcomingbookfairs = UpcomingBookFair::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Upcoming Book Fairs retrieved successfully',
            'upcomingbookfairs' => $upcomingbookfairs,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'book_fair_title' => 'required|string|max:1000',
            'image_url' => 'required|string|max:1000',
            'logo_url' => 'required|string|max:1000',
            'start_date' => 'required|date', // Validate start date
            'end_date' => 'required|date|after_or_equal:start_date', // Validate end date
            'location' => 'required|string|max:1000',
             'theme_color' => 'required|string|max:7',
            'summary' => 'required|string|max:1000',
        ]);
    
        $upcomingbookfair = UpcomingBookFair::create([
            'book_fair_title' => $request->book_fair_title,
            'image_url' => $request->image_url,
            'logo_url' => $request->logo_url,
            'start_date' => $request->start_date, // Store start date
            'end_date' => $request->end_date, // Store end date
            'location' => $request->location,
            'summary' => $request->summary,
            'theme_color' => $request->theme_color,
            'status' => 'ACTIVE', // Default status
        ]);

        return response()->json($upcomingbookfair, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(UpcomingBookFair $upcomingBookFair)
    {
       // Return the specified service
       return response()->json([
        'status' => 'success',
        'message' => 'Upcoming Book Fair retrieved successfully',
        'upcomingBookFair' => $upcomingBookFair,
    ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UpcomingBookFair $upcomingBookFair)
    {
    // Validate only the fields provided in the request
    $fields = $request->validate([
        'book_fair_title' => 'required|string|max:1000',
        'image_url' => 'required|string|max:1000',
        'logo_url' => 'required|string|max:1000',
        'start_date' => 'required|date', // Validate start date
        'end_date' => 'required|date|after_or_equal:start_date', // Validate end date
        'location' => 'required|string|max:1000',
        'summary' => 'required|string|max:1000',
        'theme_color' => 'required|string|max:7',
    ]);

     // Update book fair details
     $upcomingBookFair->update($fields);

    return response()->json([
        'message' => 'Upcoming book fair updated successfully.',
        'Upcoming Book Fair' => $upcomingBookFair
    ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UpcomingBookFair $upcomingBookFair)
    {
        // Delete the service
        $upcomingBookFair->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Upcoming Book Fair deleted successfully',
        ], 200);
    }
}
