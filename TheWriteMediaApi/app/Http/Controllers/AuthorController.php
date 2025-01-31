<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Get all authors (with their related user data)
         $authors = Author::with('user')->latest()->get();
         return response()->json([
             'authors' => $authors
         ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      // Validate the incoming request
        $fields = $request->validate([
        'author_name' => 'required|string|max:255',
        'author_country' => 'required|string|max:255',
        'author_age' => 'required|string|max:255',
        'author_sex' => 'required|string|max:255',
        'user_email' => 'required|email|unique:users,email',
        'user_password' => 'required|confirmed|min:8',
    ]);

      // Create the user for the Author
      $user = User::create(attributes: [
        'user_name' => $request->author_name . ' Author',
        'user_email' => $request->user_email,
        'user_password' => Hash::make($request->user_password),
        'user_type' => User::USER_TYPE_AUTHOR,
        'status' => 'ACTIVE',  // Set status to ACTIVE by default
    ]);

        // Create the Author record
        $author = Author::create([
            'user_id' => $user->id,
            'author_name' => $request->author_name . ' Author',
            'author_country' => $request->author_country,
            'author_age' => $request->author_age,
            'author_sex' => $request->author_sex
        ]);

        return response()->json([
            'message' => 'Author created successfully.',
            'author' => $author
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
         // Return the Barangay Admin details along with the associated user
         return response()->json([
            'author' => $author->load('user')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
{
    // Retrieve the user using the user_id from the Author table
    $user = User::find($author->user_id); // Get the user by the user_id referenced in Author

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Validate the incoming request
    $fields = $request->validate([
        'author_name' => 'sometimes|string|max:255',
        'author_country' => 'sometimes|string|max:255',
        'author_age' => 'sometimes|string|max:255',
        'author_sex' => 'sometimes|string|max:255',
        'user_email' => 'sometimes|email|unique:users,email,' . $user->id,
        'user_password' => 'nullable|confirmed|min:8',
    ]);

    // Update user details
    $user->update([
        'user_name' => $request->author_name ? $request->author_name . ' Author' : $user->user_name,
        'user_email' => $request->user_email ?? $user->user_email,
        'user_password' => $request->user_password ? Hash::make($request->user_password) : $user->user_password,
    ]);

    // Update author details
    $author->update([
        'author_name' => $request->author_name ? $request->author_name . ' Author' : $author->author_name,
        'author_country' => $request->author_country ?? $author->author_country,
        'author_age' => $request->author_age ?? $author->author_age,
        'author_sex' => $request->author_sex ?? $author->author_sex,
    ]);

    return response()->json([
        'message' => 'Author updated successfully.',
        'author' => $author->load('user'),
    ]);
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        // Retrieve the associated user
        $user = User::find($author->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update the user's status to INACTIVE
        $user->update(['status' => 'INACTIVE']);

        return response()->json(['message' => 'Author has been deactivated.'], 200);
    }
        public function restore(Author $author)
    {
        // Retrieve the associated user
        $user = User::find($author->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update the user's status to ACTIVE
        $user->update(['status' => 'ACTIVE']);

        return response()->json(['message' => 'Author has been reactivated.'], 200);
    }
}
