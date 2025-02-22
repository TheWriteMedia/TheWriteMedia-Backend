<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;

class NewsController extends Controller
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
        // If the user is a web_admin, show all books (active and inactive)
        $news = News::with('user')->latest()->get();
    } else {
        // If the user is an author, show only active books
        $news = News::with('user')->where('status', 'ACTIVE')->latest()->get();
    }

    return response()->json([
        'news' => $news
    ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          // Validate the incoming request
        $fields = $request->validate([
            'news_title' => 'required|string|max:255',
            'news_description' => 'required|string|max:255',
            'conclusion' => 'required|string|max:255',
            'template_no' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'news_plugs' => 'required|array',  // Validate as an array
            'news_plugs.*' => 'string',         // Ensure each item in the array is a string
            'img_urls' => 'required|array',    // Validate as an array
            'img_urls.*' => 'string',           // Ensure each item in the array is a string
        ]);

         // Access the currently authenticated user (Author)
         $user = $request->user(); // This retrieves the currently authenticated User instance
          // Create the news
        $news = News::create([
            'user_id' => $user->id,
            'news_title' => $request->news_title,
            'template_no' => $request->template_no,
            'type' => $request->type,
            'conclusion' => $request->conclusion,
            'news_description' => $request->news_description,
            'news_plugs' => $request->news_plugs, // Store the array of strings as a JSON
            'img_urls' => $request->img_urls,     // Store the array of strings as a JSON
            'status' => 'ACTIVE',  // Set status to ACTIVE by default
        ]);
            return response()->json([
                'message' => 'News created successfully.',
                'news' => $news
            ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, News $news)
    {
    // Get the currently authenticated user
    $user = $request->user();
    
    // If the user is a web_admin, allow access to all books (ACTIVE & INACTIVE)
    if ($user && $user->user_type === User::USER_TYPE_WEB_ADMIN) {
        return response()->json([
            'news' => $news->load('user')
        ]);
    }
    
    // If the book is inactive, restrict access for authors and guests
    if ($news->status !== 'ACTIVE') {
        return response()->json([
            'message' => 'News not found or is inactive.',
        ], 404);
    }
    // For authors and viewers (guests), only show ACTIVE books
    return response()->json([
    'news' => $news->load('user')
    ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
{
    // Validate only the fields provided in the request
    $fields = $request->validate([
        'news_title' => 'required|string|max:255',
        'news_description' => 'required|string|max:255',
        'conclusion' => 'required|string|max:255',
        
        'news_plugs' => 'required|array',  // Validate as an array
        'news_plugs.*' => 'string',          // Ensure each item in the array is a string
        'img_urls' => 'required|array',    // Validate as an array
        'img_urls.*' => 'string',            // Ensure each item in the array is a string
    ]);

    // No need to explicitly find the news since it's passed as a route model binding

     // Update book details
     $news->update($fields);

    return response()->json([
        'message' => 'News updated successfully.',
        'news' => $news
    ], 200);
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
   
         if (!$news) {
             return response()->json(['message' => 'News not found.'], 404);
         }
 
         // Update the user's status to INACTIVE
         $news->update(['status' => 'INACTIVE']);
 
         return response()->json(['message' => 'News has been deactivated.'], 200);
    }
    public function restore(News $news)
    {
  
        // Update the user's status to ACTIVE
        $news->update(['status' => 'ACTIVE']);

        return response()->json(['message' => 'News has been reactivated.'], 200);
    }
}
