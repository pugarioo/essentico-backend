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
        // Return full user details plus a total_orders count
        $users = User::withCount(['orders as total_orders'])->get();
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
     * Update the specified resource in storage (for admins updating any user by ID).
     * Used by PUT/PATCH /api/users/{id}
     */
    public function update(Request $request, User $user)
    {
        try {
            // Check for file BEFORE validation to ensure we catch it
            $hasFile = $request->hasFile('image');
            $file = $request->file('image');
            $allFiles = $request->allFiles();
            
            // If file exists in allFiles but not detected by hasFile, use it
            if (!$hasFile && isset($allFiles['image'])) {
                $file = $allFiles['image'];
                $hasFile = true;
            }
            
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8',
                'role' => 'sometimes|string|in:customer,admin',
                'phone' => 'sometimes|nullable|string|max:255',
                'address' => 'sometimes|nullable|string|max:255',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max (matches PHP upload_max_filesize)
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
                    if ($user->image_filename) {
                        $oldPath = 'users/' . $user->image_filename;
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }

                    // Store new image with unique filename
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('users', $filename, 'public');
                    $validated['image_filename'] = basename($path);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image',
                        'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
                    ], 500);
                }
            }

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            // Remove 'image' from validated array since we've already processed it (if it exists)
            if (isset($validated['image'])) {
                unset($validated['image']);
            }

            $user->update($validated);
            
            // Refresh to get updated data
            $user->refresh();
            
            return response()->json($user);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
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
     * Update the currently authenticated user (self-update).
     * Used by PUT/POST /api/user (uses bearer token to identify user)
     */
    public function updateCurrent(Request $request)
    {
        $user = $request->user(); // Get user from bearer token
        
        try {
            // Check for file BEFORE validation to ensure we catch it
            $hasFile = $request->hasFile('image');
            $file = $request->file('image');
            $allFiles = $request->allFiles();
            
            // If file exists in allFiles but not detected by hasFile, use it
            if (!$hasFile && isset($allFiles['image'])) {
                $file = $allFiles['image'];
                $hasFile = true;
            }
            
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|required|string|min:8',
                'role' => 'sometimes|string|in:customer,admin',
                'phone' => 'sometimes|nullable|string|max:255',
                'address' => 'sometimes|nullable|string|max:255',
                'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max (matches PHP upload_max_filesize)
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
                    if ($user->image_filename) {
                        $oldPath = 'users/' . $user->image_filename;
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }
                    }

                    // Store new image with unique filename
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('users', $filename, 'public');
                    $validated['image_filename'] = basename($path);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to upload image',
                        'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
                    ], 500);
                }
            }

            // Hash password if provided
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            // Remove 'image' from validated array since we've already processed it (if it exists)
            if (isset($validated['image'])) {
                unset($validated['image']);
            }

            $user->update($validated);
            
            // Refresh to get updated data
            $user->refresh();
            
            return response()->json($user);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
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
