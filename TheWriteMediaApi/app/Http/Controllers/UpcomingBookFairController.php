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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'required|string|max:1000',
            'theme_color' => 'required|string|max:7',
            'summary' => 'required|string|max:1000',
            'detailed_description' => 'required|string',
            'services' => 'required|array',
            'services.*.service_title' => 'required|string|max:255',
            'services.*.table_data' => 'required|array',
            'services.*.table_data.headers' => 'required|array',
            'services.*.table_data.headers.*.name' => 'required|string',
            'services.*.table_data.headers.*.price' => 'nullable|string',
            'services.*.table_data.row_groups' => 'required|array',
            'services.*.table_data.row_groups.*.category' => 'required|string',
            'services.*.table_data.row_groups.*.rows' => 'required|array',
            'services.*.table_data.row_groups.*.rows.*.row_name' => 'required|string',
            'services.*.table_data.row_groups.*.rows.*.values' => 'required|array',
            'services.*.table_data.row_groups.*.rows.*.values.*' => 'nullable|string',
        ]);
    
        $upcomingbookfair = UpcomingBookFair::create($request->all());
    
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
    // Validate the complete request payload (same as store)
    $validated = $request->validate([
        'book_fair_title' => 'required|string|max:1000',
        'image_url' => 'required|string|max:1000',
        'logo_url' => 'required|string|max:1000',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'location' => 'required|string|max:1000',
        'theme_color' => 'required|string|max:7',
        'summary' => 'required|string|max:1000',
        'detailed_description' => 'required|string',
        'services' => 'required|array',
        'services.*.service_title' => 'required|string|max:255',
        'services.*.table_data' => 'required|array',
        'services.*.table_data.headers' => 'required|array',
        'services.*.table_data.headers.*.name' => 'required|string',
        'services.*.table_data.headers.*.price' => 'nullable|string',
        'services.*.table_data.row_groups' => 'required|array',
        'services.*.table_data.row_groups.*.category' => 'required|string',
        'services.*.table_data.row_groups.*.rows' => 'required|array',
        'services.*.table_data.row_groups.*.rows.*.row_name' => 'required|string',
        'services.*.table_data.row_groups.*.rows.*.values' => 'required|array',
        'services.*.table_data.row_groups.*.rows.*.values.*' => 'nullable|string',
    ]);

    // Fully replace all data
    $upcomingBookFair->update($validated);

    return response()->json([
        'message' => 'Book fair completely updated',
        'data' => $upcomingBookFair->fresh()
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
