<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->get();

        $formattedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'currency' => $product->currency,
                'rating' => $product->ratings_avg_rating ? round($product->ratings_avg_rating, 1) : 0,
                'ratings_count' => $product->ratings_count ?? 0,
                'stock_quantity' => $product->stock_quantity,
                'image_filename' => $product->image_filename,
                'is_available' => $product->is_available,
                'category' => $product->category ? $product->category->category_name : 'Uncategorized', 
            ];
        });

        return response()->json($formattedProducts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            'is_available' => 'sometimes|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');

            $filename = basename($path);
            $validated['image_filename'] = $filename;
            unset($validated['image']);
        }

        $product = Product::create($validated);
        $product->load('category');

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('category');
        $product->loadAvg('ratings', 'rating');
        $product->loadCount('ratings');
        
        // Add calculated rating to the response
        $productData = $product->toArray();
        $productData['rating'] = $product->ratings_avg_rating ? round($product->ratings_avg_rating, 1) : 0;
        $productData['ratings_count'] = $product->ratings_count ?? 0;
        
        return response()->json($productData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Not needed for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            // Check for file BEFORE validation
            $hasFile = $request->hasFile('image');
            $file = $request->file('image');
            $allFiles = $request->allFiles();
            
            // If file exists in allFiles but not detected by hasFile, use it
            if (!$hasFile && isset($allFiles['image'])) {
                $file = $allFiles['image'];
                $hasFile = true;
            }
            
            $validated = $request->validate([
                'category_id' => 'sometimes|required|exists:categories,id',
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'price' => 'sometimes|required|numeric|min:0',
                'stock_quantity' => 'sometimes|required|integer|min:0',
                'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'is_available' => 'sometimes|nullable',
            ]);

            // Handle image upload if present
            if ($hasFile && $file) {
                try {
                    // Validate file is actually an image
                    if (!$file->isValid()) {
                        $errorMessages = [
                            UPLOAD_ERR_INI_SIZE => 'The file exceeds the upload_max_filesize directive (2MB)',
                            UPLOAD_ERR_FORM_SIZE => 'The file exceeds the MAX_FILE_SIZE directive',
                            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded',
                            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
                        ];
                        
                        $errorCode = $file->getError();
                        $errorMessage = $errorMessages[$errorCode] ?? 'Unknown upload error (code: ' . $errorCode . ')';
                        
                        return response()->json([
                            'message' => 'Invalid file uploaded',
                            'errors' => ['image' => [$errorMessage]]
                        ], 422);
                    }

                    // Delete old image if it exists
                    if ($product->image_filename) {
                        $oldPath = 'products/' . $product->image_filename;
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }

                    // Store new image with unique filename
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('products', $filename, 'public');
                    $validated['image_filename'] = basename($path);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image',
                        'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
                    ], 500);
                }
            }

            // Convert is_available from string '1'/'0' to boolean
            if (isset($validated['is_available'])) {
                $validated['is_available'] = filter_var($validated['is_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($validated['is_available'] === null) {
                    // If it's a string '1' or '0', convert it
                    $validated['is_available'] = in_array(strtolower($validated['is_available']), ['1', 'true', 'yes', 'on']);
                }
            }

            // Remove 'image' from validated array since we've already processed it
            if (isset($validated['image'])) {
                unset($validated['image']);
            }

            $product->update($validated);
            
            // Refresh to get updated data
            $product->refresh();
            $product->load('category');
            $product->loadAvg('ratings', 'rating');
            $product->loadCount('ratings');
            
            // Add calculated rating to the response
            $productData = $product->toArray();
            $productData['rating'] = $product->ratings_avg_rating ? round($product->ratings_avg_rating, 1) : 0;
            $productData['ratings_count'] = $product->ratings_count ?? 0;
            
            return response()->json($productData);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the product',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
