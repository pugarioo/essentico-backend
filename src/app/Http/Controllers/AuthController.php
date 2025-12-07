<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Admin login endpoint
     */
    public function adminLogin(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Find the user
            $user = User::where('email', $validated['email'])->first();

            // Check credentials
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid email or password',
                    'errors' => [
                        'email' => ['These credentials do not match our records.']
                    ]
                ], 401);
            }

            // Check if user is admin
            if ($user->role !== 'admin') {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.',
                    'errors' => [
                        'email' => ['This account does not have admin privileges.']
                    ]
                ], 403);
            }

            // Create token
            $token = $user->createToken('admin-auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'tokenType' => 'Bearer',
                'user' => $user,
            ], 200);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'message' => 'An error occurred during login',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    /**
     * User login endpoint
     */
    public function userLogin(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            // Find the user
            $user = User::where('email', $validated['email'])->first();

            // Check credentials
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid email or password',
                    'errors' => [
                        'email' => ['These credentials do not match our records.']
                    ]
                ], 401);
            }

            // Check if user is customer (not admin)
            if ($user->role === 'admin') {
                return response()->json([
                    'message' => 'Unauthorized. Please use admin login endpoint.',
                    'errors' => [
                        'email' => ['Admin accounts must use the admin login endpoint.']
                    ]
                ], 403);
            }

            // Create token
            $token = $user->createToken('user-auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'tokenType' => 'Bearer',
                'user' => $user,
            ], 200);

        } catch (ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'message' => 'An error occurred during login',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during logout',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            return response()->json([
                'user' => $request->user()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Please try again later'
            ], 500);
        }
    }
}