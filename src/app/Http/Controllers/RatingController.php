<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RatingController extends Controller
{
    /**
     * Get ratings for a specific product (including breakdown)
     * GET /api/products/{id}/ratings
     */
    public function getProductRatings(Product $product)
    {
        $ratings = Rating::where('product_id', $product->id)->get();
        
        // Calculate average rating
        $averageRating = $ratings->avg('rating');
        
        // Get rating breakdown (count per star)
        $breakdown = [
            5 => $ratings->where('rating', 5)->count(),
            4 => $ratings->where('rating', 4)->count(),
            3 => $ratings->where('rating', 3)->count(),
            2 => $ratings->where('rating', 2)->count(),
            1 => $ratings->where('rating', 1)->count(),
        ];
        
        return response()->json([
            'product_id' => $product->id,
            'average_rating' => $averageRating ? round($averageRating, 2) : 0,
            'ratings_count' => $ratings->count(),
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Create a new rating
     * POST /api/ratings
     * Body: { product_id, order_id, rating (1-5) }
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $validated = $request->validate([
                'product_id' => 'required|uuid|exists:products,id',
                'order_id' => 'required|exists:orders,id',
                'rating' => 'required|integer|min:1|max:5',
            ]);

            // Verify the order belongs to the authenticated user
            $order = Order::findOrFail($validated['order_id']);
            
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized. This order does not belong to you.',
                ], 403);
            }

            // Verify the order status is "delivered"
            if ($order->status !== 'delivered') {
                return response()->json([
                    'message' => 'You can only rate products from delivered orders.',
                    'errors' => ['order_id' => ['Order must be delivered before rating.']]
                ], 422);
            }

            // Verify the product was purchased in this order
            $orderItem = OrderItem::where('order_id', $validated['order_id'])
                ->where('product_id', $validated['product_id'])
                ->first();

            if (!$orderItem) {
                return response()->json([
                    'message' => 'This product was not purchased in this order.',
                    'errors' => ['product_id' => ['Product not found in order.']]
                ], 422);
            }

            // Check if user already rated this product from this order
            $existingRating = Rating::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->where('order_id', $validated['order_id'])
                ->first();

            if ($existingRating) {
                return response()->json([
                    'message' => 'You have already rated this product from this order.',
                    'errors' => ['rating' => ['Duplicate rating not allowed.']]
                ], 422);
            }

            // Create the rating
            $rating = Rating::create([
                'user_id' => $user->id,
                'product_id' => $validated['product_id'],
                'order_id' => $validated['order_id'],
                'rating' => $validated['rating'],
            ]);

            $rating->load(['user', 'product', 'order']);

            return response()->json($rating, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the rating',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Check if a product can be rated from an order
     * GET /api/ratings/check?order_id={id}&product_id={id}
     */
    public function checkRating(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'can_rate' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|uuid|exists:products,id',
            ]);

            // Verify the order belongs to the authenticated user
            $order = Order::findOrFail($validated['order_id']);
            
            if ($order->user_id !== $user->id) {
                return response()->json([
                    'can_rate' => false,
                    'message' => 'This order does not belong to you.',
                ]);
            }

            // Check if order is delivered
            if ($order->status !== 'delivered') {
                return response()->json([
                    'can_rate' => false,
                    'message' => 'Order must be delivered before rating.',
                ]);
            }

            // Verify the product was purchased in this order
            $orderItem = OrderItem::where('order_id', $validated['order_id'])
                ->where('product_id', $validated['product_id'])
                ->first();

            if (!$orderItem) {
                return response()->json([
                    'can_rate' => false,
                    'message' => 'Product not found in order.',
                ]);
            }

            // Check if already rated
            $existingRating = Rating::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->where('order_id', $validated['order_id'])
                ->first();

            if ($existingRating) {
                return response()->json([
                    'can_rate' => false,
                    'already_rated' => true,
                    'rating' => $existingRating->rating,
                    'message' => 'You have already rated this product from this order.',
                ]);
            }

            return response()->json([
                'can_rate' => true,
                'message' => 'Product can be rated.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'can_rate' => false,
                'message' => 'Invalid request.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'can_rate' => false,
                'message' => 'An error occurred while checking rating status',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Get all ratings (Admin only)
     * GET /api/admin/ratings
     */
    public function adminIndex(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            $ratings = Rating::with(['user', 'product', 'order'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($ratings);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching ratings',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Delete a rating (Admin only)
     * DELETE /api/admin/ratings/{id}
     */
    public function adminDestroy(Request $request, Rating $rating)
    {
        try {
            $user = $request->user();
            
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            $rating->delete();

            return response()->json([
                'message' => 'Rating deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the rating',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }
}
