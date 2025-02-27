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
          
           // Paperback Fields
           $table->decimal('paperback_price_increase', 10, 2)->nullable();
           $table->decimal('paperback_srp', 10, 2)->nullable();
           $table->decimal('paperback_price', 10, 2)->nullable();
           $table->string('paperback_isbn', 255)->nullable();

           // Hardback Fields
           $table->decimal('hardback_price_increase', 10, 2)->nullable();
           $table->decimal('hardback_srp', 10, 2)->nullable();
           $table->decimal('hardback_price', 10, 2)->nullable();
           $table->string('hardback_isbn', 255)->nullable();

           // Ebook Fields
           $table->decimal('ebook_price_increase', 10, 2)->nullable();
           $table->decimal('ebook_srp', 10, 2)->nullable();
           $table->decimal('ebook_price', 10, 2)->nullable();
           $table->string('ebook_isbn', 255)->nullable();


            $table->string('description');
            $table->string('additional_info');
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
