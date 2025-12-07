<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with(['user', 'orderItems.product'])->get();
        return response()->json($orders);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not needed for API
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|uuid|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.details' => 'required|array',
                'items.*.details.id' => 'required|uuid|exists:products,id',
                'items.*.details.price' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
                'payment_method' => 'nullable|string|max:255',
                'delivery_method' => 'nullable|string|max:255',
                'delivery_address' => 'nullable|string',
                'ordered_at' => 'nullable|date',
            ]);

            // Create the order
            $orderData = [
                'user_id' => $validated['user_id'],
                'total_amount' => $validated['total_amount'],
                'status' => $validated['status'] ?? 'pending',
                'payment_method' => $validated['payment_method'] ?? null,
                'delivery_method' => $validated['delivery_method'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'ordered_at' => $validated['ordered_at'] ?? now(),
            ];

            $order = Order::create($orderData);

            // Create order items from the items array
            // Structure: items[].quantity and items[].details.id (product_id), items[].details.price
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['details']['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['details']['price'],
                ]);
            }

            // Load relationships and return
            $order->load(['user', 'orderItems.product']);
            
            return response()->json($order, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the order',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product']);
        return response()->json($order);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Order $order)
    {
        // Not needed for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|uuid|exists:users,id',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
            'payment_method' => 'sometimes|nullable|string|max:255',
            'delivery_method' => 'sometimes|nullable|string|max:255',
            'delivery_address' => 'sometimes|nullable|string',
            'ordered_at' => 'sometimes|nullable|date',
        ]);

        $order->update($validated);
        $order->load(['user', 'orderItems.product']);
        
        return response()->json($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
