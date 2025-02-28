<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the currently authenticated user (if any)
        $user = $request->user();
    
        // If the user is an admin, show all books; otherwise, show only active books
        if ($user && $user->user_type === User::USER_TYPE_WEB_ADMIN) {
            $books = Book::with('author')->latest()->get();
        } else {
            $books = Book::with('author')->where('status', 'ACTIVE')->latest()->get();
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
            'author_name' => 'required|string|max:255',
            'book_title' => 'required|string|max:255',

            'paperback_price_increase' => 'nullable|numeric',
            'paperback_srp' => 'nullable|numeric',
            'paperback_price' => 'nullable|numeric',
            'paperback_isbn' => 'nullable|string|max:255',

            'hardback_price_increase' => 'nullable|numeric',
            'hardback_srp' => 'nullable|numeric',
            'hardback_price' => 'nullable|numeric',
            'hardback_isbn' => 'nullable|string|max:255',

            'ebook_price_increase' => 'nullable|numeric',
            'ebook_srp' => 'nullable|numeric',
            'ebook_price' => 'nullable|numeric',
            'ebook_isbn' => 'nullable|string|max:255',


            'description' => 'required|string|max:1000',
            'additional_info' => 'required|string|max:255',
            'img_urls' => 'required|array',
            'img_urls.*' => 'string',
        ]);
    
        // Retrieve the author ID based on the author's name
        $author = Author::where('author_name', $request->author_name)->first();
        if (!$author) {
            return response()->json(['error' => 'Author not found.'], 404);
        }

        // Format prices to two decimal places before saving
        $book = Book::create(attributes: [
            'author_id' => $author->user_id,
            'book_title' => $request->book_title,
          
            'paperback_price_increase' => number_format((float)$request->paperback_price_increase, 2, '.', ''),
            'paperback_srp' => number_format((float)$request->paperback_srp, 2, '.', ''),
            'paperback_price' => number_format((float)$request->paperback_price, 2, '.', ''),
            'paperback_isbn' => $request->paperback_isbn,

            'hardback_price_increase' => number_format((float)$request->hardback_price_increase, 2, '.', ''),
            'hardback_srp' => number_format((float)$request->hardback_srp, 2, '.', ''),
            'hardback_price' => number_format((float)$request->hardback_price, 2, '.', ''),
            'hardback_isbn' => $request->hardback_isbn,

            'ebook_price_increase' => number_format((float)$request->ebook_price_increase, 2, '.', ''),
            'ebook_srp' => number_format((float)$request->ebook_srp, 2, '.', ''),
            'ebook_price' => number_format((float)$request->ebook_price, 2, '.', ''),
            'ebook_isbn' => $request->ebook_isbn,

            'description' => $request->description,
            'additional_info' => $request->additional_info,
            'img_urls' => $request->img_urls,
            'status' => 'ACTIVE',
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
        $user = $request->user();
    
        // If the user is a web_admin, allow access to all books
        if ($user && $user->user_type === User::USER_TYPE_WEB_ADMIN) {
            return response()->json([
                'book' => $book->load('author')  // <-- Load both user & author
            ]);
        }
    
        // If the book is inactive, restrict access for authors and guests
        if ($book->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Book not found or is inactive.',
            ], 404);
        }
    
        // For authors and viewers (guests), only show ACTIVE books
        return response()->json([
            'book' => $book->load( 'author')  // <-- Load both user & author
        ]);
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
{
    // Validate all fields as required
    $fields = $request->validate([
        'author_name' => 'required|string|max:255',
        'book_title' => 'required|string|max:255',

        'paperback_price_increase' => 'nullable|numeric',
        'paperback_srp' => 'nullable|numeric',
        'paperback_price' => 'nullable|numeric',
        'paperback_isbn' => 'nullable|string|max:255',

        'hardback_price_increase' => 'nullable|numeric',
        'hardback_srp' => 'nullable|numeric',
        'hardback_price' => 'nullable|numeric',
        'hardback_isbn' => 'nullable|string|max:255',

        'ebook_price_increase' => 'nullable|numeric',
        'ebook_srp' => 'nullable|numeric',
        'ebook_price' => 'nullable|numeric',
        'ebook_isbn' => 'nullable|string|max:255',


        'description' => 'required|string|max:1000',
        'additional_info' => 'required|string|max:255',
        'img_urls' => 'required|array',
        'img_urls.*' => 'string',
    ]);

    // Retrieve the author ID based on the provided author_name
    $author = Author::where('author_name', $request->author_name)->first();
    if (!$author) {
        return response()->json(['error' => 'Author not found.'], 404);
    }

    // Ensure prices are formatted to two decimal places before updating
    $book->update([

        'author_id' => $author->user_id, // Update author ID
        'book_title' => $fields['book_title'],
     
        'paperback_price_increase' => number_format((float)$fields['paperback_price_increase'], 2, '.', ''),
        'paperback_srp' => number_format((float)$fields['paperback_srp'], 2, '.', ''),
        'paperback_price' => number_format((float)$fields['paperback_price'], 2, '.', ''),
        'paperback_isbn' => $fields['paperback_isbn'],

        'hardback_price_increase' => number_format((float)$fields['hardback_price_increase'], 2, '.', ''),
        'hardback_srp' => number_format((float)$fields['hardback_srp'], 2, '.', ''),
        'hardback_price' => number_format((float)$fields['hardback_price'], 2, '.', ''),
        'hardback_isbn' => $fields['hardback_isbn'],

        'ebook_price_increase' => number_format((float)$fields['ebook_price_increase'], 2, '.', ''),
        'ebook_srp' => number_format((float)$fields['ebook_srp'], 2, '.', ''),
        'ebook_price' => number_format((float)$fields['ebook_price'], 2, '.', ''),
        'ebook_isbn' => $fields['ebook_isbn'],

        'description' => $fields['description'],
        'additional_info' => $fields['additional_info'],
        'img_urls' => $fields['img_urls'],
    ]);

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
