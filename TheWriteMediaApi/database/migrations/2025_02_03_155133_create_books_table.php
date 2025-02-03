<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // User relation (foreign key reference)
            $table->string('author_name');
            $table->string('book_title');
            $table->string('ebook_price');
            $table->string('paperback_price');
            $table->string('paperback_isbn');
            $table->string('ebook_isbn');
            $table->json('img_urls'); 
            $table->enum('status', ['active', 'inactive'])->default('active'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
