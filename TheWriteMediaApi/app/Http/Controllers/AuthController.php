<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\Book;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{


    // Get the authenticated user's profile, including the author data
    public function getProfile(Request $request)
    {
        // Get the currently authenticated user
        $user = $request->user();

    
        // Update the author's data if the user is an author
    if ($user->user_type === 'author') {
            // Load the associated author data (if any)
            $user->load('author');
    }

        // Return the user and author data
        return response()->json([
            'user' => $user,
        ]);
    }
    // Method to update the user's profile and password
    public function updateProfile(Request $request)
    {
        $user = $request->user();
    
        // Ensure that the user cannot directly update the password via the 'user_password' field
        if ($request->has('user_password') && !$request->filled('current_password')) {
            return response()->json(['message' => 'Password change is only allowed through the current password, new password, and confirm password fields.'], 400);
        }
    
        // Define validation rules based on user role
        if ($user->user_type === 'web_admin') {
            // Validation rules for admin
            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'user_email' => 'required|string|email|max:255|unique:users,user_email,' . $user->id,
                'current_password' => 'nullable|string',
                'new_password' => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);
        } elseif ($user->user_type === 'author') {
            // Validation rules for author
            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'user_email' => 'required|string|email|max:255|unique:users,user_email,' . $user->id,
                'author_country' => 'required|string|max:255',
                'author_age' => 'required|integer',
                'author_sex' => 'required|string|max:10',
                'current_password' => 'nullable|string',
                'new_password' => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            ]);
        }
    
        // If the user is changing the password, validate the current password
        if ($request->filled('current_password')) {
            if (!Hash::check($validated['current_password'], $user->user_password)) {
                return response()->json(['message' => 'Current password is incorrect.'], 400);
            }
    
            // Update the password if provided and valid
            $user->update([
                'user_password' => Hash::make($validated['new_password']),
            ]);
        }
    
        // Update the user data (name, email)
        $user->update([
            'user_name' => $validated['user_name'],
            'user_email' => $validated['user_email'],
        ]);
    
        // Update the author's data if the user is an author
        if ($user->user_type === 'author') {
            // Retrieve the updated user with author data (if available)
            $user->load('author');
            $user->author()->update([
                'author_name' => $validated['user_name'],
                'author_country' => $validated['author_country'],
                'author_age' => $validated['author_age'],
                'author_sex' => $validated['author_sex'],
            ]);
        }

         // Update all books associated with the author
        Book::where('user_id', $user->id)->update(['author_name' => $validated['user_name']]);
    
        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user, // The user includes the author data
        ]);
    }
    public function register(Request $request)
    {
        try {
            // Validate request
            $validatedData = $request->validate([
                'user_name' => 'required|string|max:255',
                'user_email' => 'required|email|unique:users,user_email',
                'user_password' => 'required|string|confirmed|min:8',
                'user_type' => 'required|string|in:web_admin,author',
            ]);
    
            // Create user with hashed password
            $user = User::create([
                'user_name' => $validatedData['user_name'],
                'user_email' => $validatedData['user_email'],
                'user_password' => bcrypt($validatedData['user_password']), // Manually hash password
                'user_type' => $validatedData['user_type'],
                'status' => User::STATUS_ACTIVE, // Default status
            ]);
    
            // Generate API token
            $token = $user->createToken($user->user_name . ' AuthToken')->plainTextToken;
    
            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while registering the user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function login(Request $request)
    {
        $request->validate([
            'user_email' => 'required|email',
            'user_password' => 'required'
        ]);
    
        $user = User::where('user_email', $request->user_email)->first();
    
        if (!$user || !Hash::check($request->user_password, $user->user_password)) {
            return response()->json([
                'errors' => [
                    'user_email' => ['The provided credentials are incorrect.']
                ]
            ], 401); // Return HTTP 401 Unauthorized
        }
    
        $token = $user->createToken($user->user_name . ' Auth-Token')->plainTextToken;
    
        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
    public function logout(Request $request){
       $request->user()->tokens()->delete();

       return[
        'message' => 'You are logged out.'
        ];
    }
    public function forgotPassword(Request $request): JsonResponse
{
    // Validate the incoming request data
    $request->validate(['user_email' => 'required|email']);

    // Find the user by email
    $user = User::where('user_email', $request->user_email)->first();

    if (!$user) {
        return response()->json(['message' => 'No user found with this email address.'], 404);
    }

    // Create a token
    $token = Str::random(60);

    // Store the token in the password_resets collection
    PasswordReset::updateOrCreate(
        ['user_email' => $request->user_email],
        ['token' => $token, 'created_at' => now()]
    );

    // Send email with only the reset token
    Mail::to($user->user_email)->send(new PasswordResetMail($token)); // Ensure this mail class is set up

    return response()->json(['message' => 'Password reset token has been sent to your email.'], 200);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Validate the incoming request data
        $request->validate([
            'user_email' => 'required|email',
            'token' => 'required|string',
            'user_password' => 'required|string|min:8|confirmed', // Include password confirmation
        ]);

        // Check the token in the password_resets collection
        $passwordReset = PasswordReset::where('user_email', $request->user_email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token or email.'], 400);
        }

        // Update the user's password
        $user = User::where('user_email', $request->user_email)->first();
        $user->user_password = bcrypt($request->user_password); // Ensure you hash the password
        $user->save();

        // Optionally, delete the token after it has been used
        $passwordReset->delete();

        return response()->json(['message' => 'Password has been successfully reset.'], 200);
    }
    
}
