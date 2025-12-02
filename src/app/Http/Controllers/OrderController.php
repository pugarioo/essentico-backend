<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'sometimes|string|in:pending,processing,completed,cancelled',
            'payment_method' => 'nullable|string|max:255',
            'delivery_method' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string',
            'ordered_at' => 'nullable|date',
        ]);

        $order = Order::create($validated);
        $order->load(['user', 'orderItems.product']);
        
        return response()->json($order, 201);
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
            'user_id' => 'sometimes|required|exists:users,id',
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
