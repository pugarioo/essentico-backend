<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category; // Make sure you have this model
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $json = File::get(database_path('data/products.json'));
        $data = json_decode($json, true);

        foreach ($data as $item) {
            
            // 1. Handle the Category Relationship
            // The JSON gives "Furniture", but DB needs an ID. 
            // This finds "Furniture" in DB or creates it if missing.
            $category = Category::firstOrCreate(
                ['category_name' => $item['category']]
            );

            // 2. Create the Product
            // We search by 'name' to avoid duplicates
            Product::updateOrCreate(
                ['name' => $item['name']], 
                [
                    'category_id'    => $category->id,
                    'description'    => $item['description'],
                    'price'          => $item['price'],
                    'currency'       => $item['currency'] ?? '₱',
                    'rating'         => $item['rating'],
                    'review_count'   => $item['review_count'],
                    'image_filename' => $item['image_filename'],
                    
                    // Fields not in JSON but in your fillable
                    'stock_quantity' => 50, 
                    'is_available'   => true,
                ]
            );
        }
    }
}