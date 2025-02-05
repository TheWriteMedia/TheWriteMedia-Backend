<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Get the currently authenticated user
    $user = $request->user();

    // Check the user type
    if ($user->user_type === 'web_admin') {
        // If the user is a web_admin, show all books (active and inactive)
        $books = Book::with('user')->latest()->get();
    } else {
        // If the user is an author, show only active books
        $books = Book::with('user')->where('status', 'ACTIVE')->latest()->get();
    }

    return response()->json([
        'books' => $books
    ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'book_title' => 'required|string|max:255',
            'ebook_price' => 'required|string|max:255', // Validate as an array
            'paperback_price' => 'required|string|max:255',
            'paperback_isbn' => 'required|string|max:255',
            'ebook_isbn' => 'required|string|max:255',
            'img_urls' => 'required|array',    // Validate as an array
            'img_urls.*' => 'string',           // Ensure each item in the array is a string     // Ensure each item in the array is a file
        ]);
        // Access the currently authenticated user (Author)
        $user = $request->user(); // This retrieves the currently authenticated User instance
        // Create the news
        $book = Book::create([
        'user_id' => $user->id,
        'author_name' => $user->user_name,
        'book_title' => $request->book_title,
        'ebook_price' => $request->ebook_price, // Store the array of strings as a JSON
        'paperback_price' => $request->paperback_price, 
        'paperback_isbn' => $request->paperback_isbn,  
        'ebook_isbn' => $request->ebook_isbn,    
        'img_urls' => $request->img_urls,       // Validate as an array

        'status' => 'ACTIVE',  // Set status to ACTIVE by default
        ]);
        return response()->json([
            'message' => 'Book created successfully.',
            'books' => $book
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, Book $book)
    {
        // Get the currently authenticated user
        $user = $request->user();
    
        // If the user is an author and the book is inactive, deny access
        if ($user->user_type === 'author' && $book->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Book not found or is inactive.',
            ], 404);
        }
    
        // Return the book with its related user data
        return response()->json([
            'book' => $book->load('user')
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
    // Validate all fields as required
        $fields = $request->validate([
            'book_title' => 'required|string|max:255',
            'ebook_price' => 'required|numeric', 
            'paperback_price' => 'required|numeric',
            'paperback_isbn' => 'required|string|max:255',
            'ebook_isbn' => 'required|string|max:255',
            'img_urls' => 'required|array', // Ensure it's an array
            'img_urls.*' => 'string'  // Validate each item in the array
        ]);


        // Update book details
        $book->update($fields);

        return response()->json([
            'message' => 'Book updated successfully.',
            'book' => $book
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        if (!$book) {
            return response()->json(['message' => 'Book not found.'], 404);
        }

        // Update the user's status to INACTIVE
        $book->update(['status' => 'INACTIVE']);

        return response()->json(['message' => 'Book has been deactivated.'], 200);
    }
    public function restore(Book $book)
    {

        // Update the user's status to ACTIVE
        $book->update(['status' => 'ACTIVE']);

        return response()->json(['message' => 'Book has been reactivated.'], 200);
    }
}
