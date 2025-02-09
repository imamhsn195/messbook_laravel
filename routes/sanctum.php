<?php

// routes/api.php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// User Registration Route
Route::post('/register', function (Request $request) {
    // Validate the request
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Create the user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Create a new token
    $token = $user->createToken('auth_token')->plainTextToken;

    // Return the token and user in the response
    return response()->json([
        'token' => $token,
        'user' => $user,
    ], 201);
});

// User Login Route
Route::post('/login', function (Request $request) {
    try {
        // Validate the request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->firstOrFail();

        // Check if password is correct
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return token and user in response
        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'User not found.',
            'error' => $e->getMessage()
        ], 404);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Validation error.',
            'errors' => $e->errors()
        ], 422);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'An unexpected error occurred.',
            'error' => $e->getMessage()
        ], 500);
    }
});

// User Logout Route (Authenticated)
Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
    // Revoke all tokens for the authenticated user
    $request->user()->tokens->each(function ($token) {
        $token->delete();
    });

    // Return response
    return response()->json(['message' => 'Logged out successfully.']);
});

// Password Reset Link Route
Route::post('/forgot-password', function (Request $request) {
    // Validate the request
    $request->validate(['email' => 'required|email']);

    // Send the password reset link to the user's email
    return Password::sendResetLink($request->only('email'));
});

// Password Reset Route
Route::post('/reset-password', function (Request $request) {
    // Validate the request
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed|min:8',
    ]);

    // Reset password using Laravel's built-in functionality
    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            // Update user's password
            $user->password = bcrypt($password);
            $user->save();
        }
    );

    // Check if the password reset was successful
    if ($status == Password::PASSWORD_RESET) {
        return response()->json(['message' => 'Password has been reset successfully.']);
    }

    // Return error if password reset fails
    return response()->json(['message' => 'Error resetting password.'], 400);
});

// Refresh Token Route (Authenticated)
Route::middleware('auth:sanctum')->post('/refresh-token', function (Request $request) {
    // Revoke the current token and create a new one
    $user = $request->user();
    $newToken = $user->createToken('auth_token')->plainTextToken;

    // Return the new token and user info in response
    return response()->json([
        'token' => $newToken,
        'user' => $user
    ]);
});

// Get User Profile Route (Authenticated)
Route::get('/user', function (Request $request) {
    // Return the authenticated user's profile
    return response()->json($request->user());
})->middleware('auth:sanctum');
