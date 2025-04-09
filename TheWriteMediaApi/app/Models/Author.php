<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Author extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorFactory> */
    use HasFactory;

    protected $connection = 'mongodb';  
     protected $collection = 'authors'; 

    protected $fillable = [
        'user_id',
        'unique_author_id',
        'author_name',
        'author_country',
        'author_sex',
        'author_age',
        'author_address_line_1',
        'author_address_line_2',
        'author_city',
        'author_contact_no',
        'author_zip',
        'author_po_box'
    ];

    // Author Model
protected $casts = [
  'user_id' => 'string',
    '_id' => 'string',
];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


      /**
     * An author has many books.
     */
    public function books()
    {
        return $this->hasMany(Book::class, 'author_id', 'user_id');
    }

 /**
     * An author has many reports (through books).
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'author_id', 'user_id');
    }
}
