<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Report;
use App\Models\TotalAccumulatedRoyalty;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Get the currently authenticated user
    $user = $request->user();

    // Load reports with related book and author
    if ($user->user_type === User::USER_TYPE_WEB_ADMIN) {
        // Admin: Show all reports with books and authors
        $reports = Report::with(['book', 'author'])->latest()->get();
    } else {
        // Author: Show only reports for the logged-in author and active books
        $reports = Report::with(['book', 'author'])
        ->latest()->get();
    }

    return response()->json([
        'reports' => $reports
    ]);
}


/**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'book_id' => 'required|exists:books,_id',
        'author_id' => 'required|exists:authors,user_id',
        'sales_data' => 'required|array|min:1',
        'sales_data.*.book_format' => 'required|string|in:paperback,hardback,ebook',
        'sales_data.*.number_of_book_sold' => 'required|integer|min:1',
        'sales_data.*.country' => 'required|string|max:100',
        'quarter' => 'required|integer|min:1|max:4',
        'year' => 'required|integer|min:1900|max:' . now()->year,
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    $bookId = $request->input('book_id');
    $authorId = $request->input('author_id');
    $salesData = $request->input('sales_data');
    $quarter = $request->input('quarter');
    $year = $request->input('year');

    // Ensure the book exists and belongs to the author
    $book = Book::where('_id', $bookId)->where('author_id', $authorId)->first();

    if (!$book) {
        return response()->json([
            'status' => 'error',
            'message' => 'This author does not own the specified book.',
        ], 403);
    }

    $totalRoyalty = 0;
    $formattedSalesData = [];

    try {
        foreach ($salesData as $data) {
            $format = strtolower($data['book_format']);
            $booksSold = $data['number_of_book_sold'];
            $country = $data['country'];

            // Determine price increase based on format
            $priceIncrease = match ($format) {
                'paperback' => $book->paperback_price_increase,
                'hardback' => $book->hardback_price_increase,
                'ebook' => $book->ebook_price_increase,
                default => null,
            };

            if ($priceIncrease === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Invalid book format: $format",
                ], 422);
            }

            // Calculate royalty per book and total royalty
            $royaltyPerBook = round(($priceIncrease * 0.80) + 2.3, 2);
            $totalRoyaltyForSale = round($royaltyPerBook * $booksSold, 2);
            $totalRoyalty += $totalRoyaltyForSale;

            // Append entry to sales data
            $formattedSalesData[] = [
                'book_format' => $format,
                'number_of_book_sold' => $booksSold,
                'country' => $country,
                'royalty_per_book' => $royaltyPerBook,
                'total_royalty' => number_format($totalRoyaltyForSale, 2)
            ];
        }

        // Ensure total royalty is also rounded
        $totalRoyalty = round($totalRoyalty, 2);

        // Save the report as a single document with sales data array
        $report = Report::create([
            'book_id' => $bookId,
            'author_id' => $authorId,
            'sales_data' => $formattedSalesData,
            'total_royalty' => $totalRoyalty,
            'quarter' => $quarter, 
            'year' => $year, 
        ]);
        

        $existing = TotalAccumulatedRoyalty::where('user_id', $authorId)->first();

        if ($existing) {
            $existing->value += $totalRoyalty;
            $existing->save();
        } else {
            TotalAccumulatedRoyalty::create([
                'user_id' => $authorId,
                'value' => $totalRoyalty
            ]);
        }
            
          // After creating the report and updating royalties:
          $this->notifyAuthorAboutReport($authorId, $book, $totalRoyalty, $quarter, $year);

        return response()->json([
            'status' => 'success',
            'message' => 'Sales report stored successfully',
            'report' => $report,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to store sales report',
            'error' => $e->getMessage(),
        ], 500);
    }
}

/**
 * Notify author about new sales report
 */
protected function notifyAuthorAboutReport($authorId, Book $book, $totalRoyalty, $quarter, $year)
{
    $author = User::find($authorId);
    
    if (!$author || empty($author->fcm_tokens)) {
        Log::warning('Report notification failed: Author not found or has no FCM tokens', [
            'author_id' => $authorId,
            'book_id' => $book->id,
            'has_tokens' => !empty($author->fcm_tokens) // Log whether tokens exist
        ]);
        return;
    }

    $title = 'New Sales Report Available';
    $message = "Your book '{$book->book_title}' earned \${$totalRoyalty} in Q{$quarter} {$year}";

    NotificationService::sendNotification(
        $author->id,
        $title,
        $message,
        'sales_report',
        $book->id
    );
}



/**
 * Display the specified resource.
 */
public function show($id)
{
    try {
        // Find the report by ID
        $report = Report::with(['author', 'book'])->find($id);

        if (!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'report' => $report
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to retrieve the sales report',
            'error' => $e->getMessage(),
        ], 500);
    }
}


/**
 * Update the specified resource in storage.
 */
public function update(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'sales_data' => 'sometimes|array|min:1',
        'sales_data.*.book_format' => 'required|string|in:paperback,hardback,ebook',
        'sales_data.*.number_of_book_sold' => 'required|integer|min:1',
        'sales_data.*.country' => 'required|string|max:100',
        'quarter' => 'sometimes|integer|min:1|max:4', // Validate quarter (1-4)
        'year' => 'sometimes|integer|min:2000|max:' . now()->year, // Validate year
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        // Find the report by ID
        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found.',
            ], 404);
        }

         // Store the original royalty value before any changes
         $originalRoyalty = $report->total_royalty;

        // Ensure the book exists and belongs to the author
        $book = Book::where('_id', $report->book_id)
                    ->where('author_id', $report->author_id)
                    ->first();

        if (!$book) {
            return response()->json([
                'status' => 'error',
                'message' => 'This author does not own the specified book.',
            ], 403);
        }

        // Initialize new sales data
        $newSalesData = $request->input('sales_data', null);
        $totalRoyalty = 0;

        if ($newSalesData !== null) {
            // If new sales data is provided, process and replace old sales data
            $processedSalesData = [];

            foreach ($newSalesData as $data) {
                $format = strtolower($data['book_format']);
                $booksSold = $data['number_of_book_sold'];
                $country = $data['country'];

                // Determine price increase based on format
                $priceIncrease = match ($format) {
                    'paperback' => $book->paperback_price_increase,
                    'hardback' => $book->hardback_price_increase,
                    'ebook' => $book->ebook_price_increase,
                    default => null,
                };

                if ($priceIncrease === null) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Invalid book format: $format",
                    ], 422);
                }

                // Calculate royalty per book
                $royaltyPerBook = round(($priceIncrease * 0.80) + 2.3, 2);
                $totalRoyaltyForSale = round($royaltyPerBook * $booksSold, 2);
                $totalRoyalty += $totalRoyaltyForSale;

                // Append new sales data
                $processedSalesData[] = [
                    'book_format' => $format,
                    'number_of_book_sold' => $booksSold,
                    'country' => $country,
                    'royalty_per_book' => $royaltyPerBook,
                    'total_royalty' => $totalRoyaltyForSale,
                ];
            }

            // Replace the existing sales data with new processed data
            $report->sales_data = $processedSalesData;
        } else {
            // If no new sales data is provided, retain the existing data
            $totalRoyalty = $report->total_royalty;
        }

        // Update quarter and year if provided
        if ($request->has('quarter')) {
            $report->quarter = $request->input('quarter');
        }
        if ($request->has('year')) {
            $report->year = $request->input('year');
        }

        // Update total royalty (rounded to 2 decimal places) and save the report
        $report->total_royalty = round($totalRoyalty, 2);

        // Calculate the difference between new and original royalty
        $royaltyDifference = round($totalRoyalty, 2) - $originalRoyalty;

        $report->save();

          // Only update accumulated royalty if there's a difference
          if ($royaltyDifference != 0) {
            $existing = TotalAccumulatedRoyalty::where('user_id', $report->author_id)->first();
            
            if ($existing) {
                $existing->value += $royaltyDifference;
                $existing->save();
            } else {
                // This case shouldn't normally happen if you create records on report creation
                TotalAccumulatedRoyalty::create([
                    'user_id' => $report->author_id,
                    'value' => $totalRoyalty
                ]);
            }
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Sales report updated successfully',
            'report' => $report,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update sales report',
            'error' => $e->getMessage(),
        ], 500);
    }
}


/**
 * Remove the specified resource from storage.
 */
public function destroy(Report $report)
{
    try {
        // Get the royalty value before deletion
        $royaltyToSubtract = $report->total_royalty;
        $authorId = $report->author_id;

        // Delete the report
        $report->delete();

        // Update the accumulated royalty
        $existing = TotalAccumulatedRoyalty::where('user_id', $authorId)->first();

        if ($existing) {
            // Ensure we don't go negative (just in case)
            $existing->value = max(0, $existing->value - $royaltyToSubtract);
            $existing->save();
        }
        // If no existing record, that means this was the only report (and it's now deleted)
        // so we don't need to create a record with negative value

        return response()->json([
            'status' => 'success',
            'message' => 'Report deleted successfully.'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to delete report',
            'error' => $e->getMessage(),
        ], 500);
    }
}
}
