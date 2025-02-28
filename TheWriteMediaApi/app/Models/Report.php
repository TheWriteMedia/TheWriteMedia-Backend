<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';  
    protected $collection = 'reports'; 

    protected $fillable = [
        'book_id',
        'author_id',
        'sales_data', // Array of objects
        'total_royalty',
        'status'
    ];

    protected $casts = [
        'sales_data' => 'array', // Ensure sales_data is stored as an array
        'total_royalty' => 'float',
    ];


    protected static function boot()
    {
        parent::boot();
 
        // Automatically set 'status' to 'ACTIVE' when creating a news record
        static::creating(function ($report) {
            if (!$report->status) {
                $report->status = 'ACTIVE';
            }
        });
    }

    /**
     * Relationship: A report belongs to a book.
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', '_id');
    }

    /**
     * Relationship: A report belongs to an author.
     */
    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id', 'user_id');
    }
}
