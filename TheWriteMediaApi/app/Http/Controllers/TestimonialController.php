<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use App\Http\Requests\StoreTestimonialRequest;
use App\Http\Requests\UpdateTestimonialRequest;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       // Retrieve all testimonials
       $testimonials = Testimonial::all();

       return response()->json([
           'status' => 'success',
           'message' => 'Testimonials retrieved successfully',
           'testimonials' => $testimonials,
       ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:1000',
            'testimonial_url' => 'required|string|max:1000',
           
        ]);
    
        $upcomingbookfair = Testimonial::create([
            'title' => $request->title,
            'testimonial_url' => $request->testimonial_url,
        ]);

        return response()->json($upcomingbookfair, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Testimonial $testimonial)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Testimonial retrieved successfully',
            'testimonial' => $testimonial,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Testimonial $testimonial)
    {
        $fields = $request->validate([
            'title' => 'required|string|max:1000',
            'testimonial_url' => 'required|string|max:1000',
           
        ]);
    
         // Update book fair details
         $testimonial->update($fields);
    
        return response()->json([
            'message' => 'Testimonial updated successfully.',
            'Testimonial' => $testimonial
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Testimonial deleted successfully',
        ], 200);
    }
}
