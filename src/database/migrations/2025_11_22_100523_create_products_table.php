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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Assuming you have a categories table. If not, change this to $table->string('category');
            $table->foreignId('category_id')->nullable(); 
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('₱'); // Fits your JSON currency
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('stock_quantity')->default(100); // Default since JSON doesn't have stock
            $table->string('image_filename')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
