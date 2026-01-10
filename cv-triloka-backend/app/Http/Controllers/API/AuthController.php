<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;

class AuthController extends Controller
{
    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,klien',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'company_name' => $request->company_name,
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::log('register', "User {$user->name} registered as {$user->role}", $user, $user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah. Silakan coba lagi.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan. Hubungi admin.'],
            ]);
        }

        // Revoke old tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // ActivityLog::log('login', "User {$user->name} logged in", $user, $user); // Temporary disabled for performance


        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        ActivityLog::log('logout', "User {$user->name} logged out", $user, $user);

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        $user->update($request->only(['name', 'phone', 'address', 'company_name']));

        ActivityLog::log('update_profile', "User {$user->name} updated profile", $user, $user);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' =>$user,
        ]);
    }

    /**
     * Firebase-based authentication (Register)
     */
    public function firebaseRegister(Request $request, FirebaseAuth $firebaseAuth)
    {
        $request->validate([
            'idToken' => 'required|string',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        try {
            // Verify Firebase ID Token with 120s leeway
            $verifiedIdToken = $firebaseAuth->verifyIdToken($request->idToken, false, 120);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');
            $email = $verifiedIdToken->claims()->get('email');

            // Check if user already exists
            $existingUser = User::where('firebase_uid', $firebaseUid)->first();
            if ($existingUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already registered',
                ], 400);
            }

            // Create new user
            $user = User::create([
                'firebase_uid' => $firebaseUid,
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make(uniqid()), // Random password (not used)
                'role' => 'klien',
                'phone' => $request->phone,
                'address' => $request->address,
                'company_name' => $request->company_name,
                'is_active' => true,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            ActivityLog::log('firebase_register', "User {$user->name} registered via Firebase", $user, $user);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token: ' . $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Firebase-based authentication (Login)
     */
    public function firebaseLogin(Request $request, FirebaseAuth $firebaseAuth)
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        try {
            // Verify Firebase ID Token with 120s leeway
            $verifiedIdToken = $firebaseAuth->verifyIdToken($request->idToken, false, 120);
            $firebaseUid = $verifiedIdToken->claims()->get('sub');

            // Find user by Firebase UID
            $user = User::where('firebase_uid', $firebaseUid)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found. Please register first.',
                ], 404);
            }

            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Hubungi admin.',
                ], 403);
            }

            // Revoke old tokens
            $user->tokens()->delete();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Firebase token: ' . $e->getMessage(),
            ], 401);
        }
    }
}
