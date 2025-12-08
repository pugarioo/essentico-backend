<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DiscountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $discounts = Discount::all();
        return response()->json($discounts);
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
                'discount_code' => 'required|string|max:255|unique:discounts,discount_code',
                'value' => 'required|numeric|min:0|max:100',
                'expiration_date' => 'required|date|after_or_equal:today',
                'is_active' => 'sometimes|nullable',
            ]);

            // Convert is_active from string '1'/'0' to boolean
            if (isset($validated['is_active'])) {
                $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($validated['is_active'] === null) {
                    // If it's a string '1' or '0', convert it
                    $validated['is_active'] = in_array(strtolower($validated['is_active']), ['1', 'true', 'yes', 'on']);
                }
            }

            $discount = Discount::create($validated);

            return response()->json($discount, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the discount',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Discount $discount)
    {
        return response()->json($discount);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Discount $discount)
    {
        // Not needed for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Discount $discount)
    {
        try {
            $validated = $request->validate([
                'discount_code' => 'sometimes|required|string|max:255|unique:discounts,discount_code,' . $discount->id,
                'value' => 'sometimes|required|numeric|min:0|max:100',
                'expiration_date' => 'sometimes|required|date|after_or_equal:today',
                'is_active' => 'sometimes|nullable',
            ]);

            // Convert is_active from string '1'/'0' to boolean
            if (isset($validated['is_active'])) {
                $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($validated['is_active'] === null) {
                    // If it's a string '1' or '0', convert it
                    $validated['is_active'] = in_array(strtolower($validated['is_active']), ['1', 'true', 'yes', 'on']);
                }
            }

            $discount->update($validated);

            return response()->json($discount);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the discount',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Discount $discount)
    {
        try {
            $discount->delete();

            return response()->json(['message' => 'Discount deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting the discount',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }
}
