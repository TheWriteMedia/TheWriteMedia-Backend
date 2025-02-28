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
        'author_name',
        'author_country',
        'author_age',
        'author_sex'
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
