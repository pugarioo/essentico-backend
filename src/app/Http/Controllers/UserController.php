<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|string|in:customer,admin',
            'phone' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),   
            'role' => 'customer', 
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);
        return response()->json($user, 201);
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
    // comiit test
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
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Build update data array
            $updateData = [];

            // Handle name
            if ($request->has('name') && $request->input('name')) {
                $request->validate(['name' => 'required|string|max:255']);
                $updateData['name'] = $request->input('name');
            }

            // Handle email
            if ($request->has('email') && $request->input('email')) {
                $request->validate(['email' => 'required|string|email|max:255|unique:users,email,' . $user->id]);
                $updateData['email'] = $request->input('email');
            }

            // Handle password
            if ($request->has('password') && $request->input('password')) {
                $request->validate(['password' => 'required|string|min:8']);
                $updateData['password'] = Hash::make($request->input('password'));
            }

            // Handle phone
            if ($request->has('phone')) {
                $request->validate(['phone' => 'nullable|string|max:255']);
                $updateData['phone'] = $request->input('phone') ?: null;
            }

            // Handle address
            if ($request->has('address')) {
                $request->validate(['address' => 'nullable|string|max:255']);
                $updateData['address'] = $request->input('address') ?: null;
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Validate image
                $request->validate([
                    'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                // Ensure directory exists
                $usersPath = storage_path('app/public/users');
                if (!is_dir($usersPath)) {
                    mkdir($usersPath, 0755, true);
                }

                // Delete old image if exists
                if ($user->image_filename) {
                    $oldImagePath = storage_path('app/public/users/' . $user->image_filename);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                // Store new image
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($usersPath, $filename);
                
                $updateData['image_filename'] = $filename;
            }

            // Only update if there's data to update
            if (!empty($updateData)) {
                $user->update($updateData);
                $user->refresh();
            }

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the user',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
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
