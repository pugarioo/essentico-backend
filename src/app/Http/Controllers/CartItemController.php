<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource (authenticated user's cart).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product')
            ->get();
        
        return response()->json($cartItems);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not needed for API
    }

    /**
     * Store a newly created resource in storage (add to authenticated user's cart).
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'product_id' => 'required|uuid|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            // Check if item already exists in cart
            $existingItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($existingItem) {
                // Update quantity if item already exists
                $existingItem->quantity += $validated['quantity'];
                $existingItem->save();
                $existingItem->load('product');
                return response()->json($existingItem, 200);
            }

            // Create new cart item with authenticated user's ID
            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
            $cartItem->load('product');
            
            return response()->json($cartItem, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, CartItem $cartItem)
    {
        // Ensure the cart item belongs to the authenticated user
        $user = $request->user();
        if ($cartItem->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $cartItem->load('product');
        return response()->json($cartItem);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CartItem $cartItem)
    {
        // Not needed for API
    }

    /**
     * Update the specified resource in storage (update quantity).
     */
    public function update(Request $request, CartItem $cartItem)
    {
        try {
            // Ensure the cart item belongs to the authenticated user
            $user = $request->user();
            if ($cartItem->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $cartItem->update($validated);
            $cartItem->load('product');
            
            return response()->json($cartItem);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CartItem $cartItem)
    {
        // Ensure the cart item belongs to the authenticated user
        $user = $request->user();
        if ($cartItem->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $cartItem->delete();
        return response()->json(['message' => 'Cart item deleted successfully'], 200);
    }
}
