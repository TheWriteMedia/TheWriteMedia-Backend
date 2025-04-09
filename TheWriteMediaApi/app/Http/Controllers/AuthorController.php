<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    try {
        // Validate the incoming request
        $fields = $request->validate([
            'unique_author_id' => 'nullable|string|max:255',
            'author_name' => 'required|string|max:255',
            'author_country' => 'required|string|max:255',
            'author_sex' => 'nullable|string|max:255',
            'author_age' => 'nullable|string|max:255',
            'author_address_line_1' => 'required|string|max:255',
            'author_address_line_2' => 'nullable|string|max:255',
            'author_city' => 'required|string|max:255',
            'author_contact_no' => 'required|string|max:255',
            'author_zip' => 'required|string|max:255',
            'author_po_box' => 'required|string|max:255',
            'user_email' => 'required|email|unique:users,user_email',
            'user_password' => 'required|confirmed|min:8',
            'user_profile' => 'required|string' // Validate image
        ]);

        // Create the user for the Author
        $user = User::create([
            'user_name' => $fields['author_name'],
            'user_email' => $fields['user_email'],
            'user_password' => Hash::make($fields['user_password']),
            'user_profile' => $fields['user_profile'],
            'user_type' => User::USER_TYPE_AUTHOR,
            'status' => 'ACTIVE', // Default status
        ]);

        // Create the Author record
        $author = Author::create([
            'user_id' => $user->id,
            'unique_author_id' => $fields['unique_author_id'],
            'author_name' => $fields['author_name'],
            'author_country' => $fields['author_country'],
            'author_sex' => $fields['author_sex'],
            'author_age' => $fields['author_age'],
            'author_address_line_1' => $fields['author_address_line_1'],
            'author_address_line_2' => $fields['author_address_line_2'],
            'author_city' => $fields['author_city'],
            'author_contact_no' => $fields['author_contact_no'],
            'author_zip' => $fields['author_zip'],
            'author_po_box' => $fields['author_po_box'],
        ]);

        return response()->json([
            'message' => 'Author created successfully.',
            'author' => $author
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while creating the author',
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Display the specified resource.
     */

     public function show(Request $request, Author $author)
     {
     
         return response()->json([
            'author' => $author->load('user')
        ]);

     }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Author $author)
    {
        try {
            // Retrieve the user using the user_id from the Author table
            $user = User::find($author->user_id);
    
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }
    
            // Validate the incoming request
            $fields = $request->validate([
                'unique_author_id' => 'nullable|string|max:255',
                'author_name' => 'required|string|max:255',
                'author_country' => 'required|string|max:255',
                'author_sex' => 'nullable|string|max:255',
                'author_age' => 'nullable|string|max:255',
                'author_address_line_1' => 'required|string|max:255',
                'author_address_line_2' => 'nullable|string|max:255',
                'author_city' => 'required|string|max:255',
                'author_contact_no' => 'required|string|max:255',
                'author_zip' => 'required|string|max:255',
                'author_po_box' => 'required|string|max:255',
                'user_email' => 'required|email|unique:users,user_email,' . $user->id,
                'user_password' => 'nullable|confirmed|min:8',
                'user_profile' => 'nullable|string',
            ]);
    
            // Update user details
            $user->update([
                'user_name' => $fields['author_name'] ,
                'user_email' => $fields['user_email'],
                'user_password' => isset($fields['user_password']) ? Hash::make($fields['user_password']) : $user->user_password,
                'user_profile' => $fields['user_profile'] ?? $user->user_profile,
            ]);
    
            // Update author details
            $author->update([
                'unique_author_id' => $fields['unique_author_id'],
                'author_name' => $fields['author_name'],
                'author_country' => $fields['author_country'],
                'author_sex' => $fields['author_sex'],
                'author_age' => $fields['author_age'],
                'author_address_line_1' => $fields['author_address_line_1'],
                'author_address_line_2' => $fields['author_address_line_2'],
                'author_city' => $fields['author_city'],
                'author_contact_no' => $fields['author_contact_no'],
                'author_zip' => $fields['author_zip'],
                'author_po_box' => $fields['author_po_box'],
            ]);
    
            // If the updated user is an author, update the author_name in all associated books
            if ($user->user_type === 'author') {
                Book::where('user_id', $user->id)->update(['author_name' => $user->user_name]);
            }
    
            return response()->json([
                'message' => 'Author updated successfully.',
                'author' => $author->load('user'),
            ], 200);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the author',
                'error' => $e->getMessage()
            ], 500);
        }
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
