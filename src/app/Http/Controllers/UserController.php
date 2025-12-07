<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users);
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
            // 1. Validate
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'role' => 'sometimes|string|in:customer,admin',
                'phone' => 'sometimes|string|max:255',
                'address' => 'sometimes|string|max:255',
                'image_filename' => 'sometimes|string|max:255',
            ]);

            // 2. Setup the data - EXPLICITLY get role from request
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $request->input('role', 'customer'), // Get directly from request
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'image_filename' => $validated['image_filename'] ?? null,
            ];

            // 3. Create
            $user = User::create($userData);

            return response()->json($user, 201);

        } catch (ValidationException $e) {
            // Handle validation errors (including duplicate email)
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            // Handle database errors (fallback for duplicate entries)
            $errorCode = $e->errorInfo[1] ?? null;
            if ($errorCode == 1062 || $errorCode == 19 || str_contains($e->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'message' => 'The email has already been taken.',
                    'errors' => ['email' => ['The email has already been taken.']]
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('orders');
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Not needed for API
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|string|in:customer,admin',
            'phone' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($user->image_filename) {
                Storage::disk('public')->delete('users/' . $user->image_filename);
            }

            // Store new image
            $path = $request->file('image')->store('users', 'public');
            $validated['image_filename'] = basename($path);
        }

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Remove 'image' from validated array since we've already processed it
        unset($validated['image']);

        $user->update($validated);
        
        return response()->json($user);
    }

    /**
     * Update the currently authenticated user.
     */
    public function updateCurrent(Request $request)
    {
        return $this->update($request, $request->user());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
