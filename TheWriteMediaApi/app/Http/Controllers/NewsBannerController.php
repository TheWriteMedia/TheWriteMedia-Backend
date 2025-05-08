<?php

namespace App\Http\Controllers;

use App\Models\NewsBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsBannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = NewsBanner::all();
        return response()->json([
            'status' => 'success',
            'data' => $banners
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'news_banner_one' => 'required|array',
            'news_banner_one.*' => 'url',
            'news_banner_two' => 'required|array',
            'news_banner_two.*' => 'url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $banner = NewsBanner::create([
            'news_banner_one' => $request->news_banner_one,
            'news_banner_two' => $request->news_banner_two,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $banner
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $banner = NewsBanner::find($id);
        
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'News banner not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $banner
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'news_banner_one' => 'sometimes|array',
            'news_banner_one.*' => 'url',
            'news_banner_two' => 'sometimes|array',
            'news_banner_two.*' => 'url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $banner = NewsBanner::find($id);
        
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'News banner not found'
            ], 404);
        }

        if ($request->has('news_banner_one')) {
            $banner->news_banner_one = $request->news_banner_one;
        }

        if ($request->has('news_banner_two')) {
            $banner->news_banner_two = $request->news_banner_two;
        }

        $banner->save();

        return response()->json([
            'status' => 'success',
            'data' => $banner
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $banner = NewsBanner::find($id);
        
        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'News banner not found'
            ], 404);
        }

        $banner->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'News banner deleted successfully'
        ]);
    }


    
}