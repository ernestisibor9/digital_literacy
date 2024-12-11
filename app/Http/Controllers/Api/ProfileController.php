<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // Profile
    public function profile()
    {
        // Get the authenticated user
        $user = auth()->user();

        // Return the user details as a JSON response
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'middlename' => $user->middlename,
                'member_id' => $user->member_id,
                'phone' => $user->phone,
                'whatsapp' => $user->whatsapp,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'next_of_kin' => $user->next_of_kin,
                'country' => $user->country,
                'state' => $user->state,
                'lga' => $user->lga,
                'occupation' => $user->occupation,
                'email' => $user->email,
                'role' => $user->role, // Ensure 'role' exists in your users table
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    // Update Profile
    public function updateProfile(Request $request, $id)
    {
        try {
            // Find user by ID
            $user = User::find($id);

            // Check if the user exists and if the authenticated user is the same
            if (!$user || $user->id !== auth()->user()->id) {
                return response()->json(['message' => 'Unauthorized User or User Not Found'], 403);
            }

            // Validate incoming fields (only validate fields provided)
            $request->validate([
                'firstname' => 'sometimes|string|max:100',
                'lastname' => 'sometimes|string|max:100',
                'middlename' => 'sometimes|string|max:100',
                'phone' => 'sometimes|string|regex:/^\+?[0-9\s\-]+$/|max:15',
                'whatsapp' => 'sometimes|string|regex:/^\+?[0-9\s\-]+$/|max:15',
                'date_of_birth' => 'sometimes|date',
                'gender' => 'sometimes|string|max:100',
                'next_of_kin' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'state' => 'sometimes|string|max:100',
                'lga' => 'sometimes|string|max:100',
                'occupation' => 'sometimes|string|max:100',
                'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($id)],
                'role' => 'sometimes|string|max:50',
            ]);

            // Update the user with the validated data
            $user->update($request->only([
                'firstname',
                'lastname',
                'middlename',
                'phone',
                'whatsapp',
                'date_of_birth',
                'gender',
                'next_of_kin',
                'country',
                'state',
                'lga',
                'occupation',
                'email'
            ]));

            // Return success response with the updated user
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => $user
            ], 200);
        } catch (Exception $e) {
            // Log any errors that occur
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the profile.'
            ], 500);
        }
    }

    // Update Password
    public function updatePassword(Request $request, $id)
    {
        // Validate the input
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Find the user by ID
            $user = User::find($id);

            // Check if the user exists and if the authenticated user is the same
            if (!$user || $user->id !== auth()->user()->id) {
                return response()->json(['message' => 'Unauthorized User or User Not Found'], 403);
            }

            // Check if the current password matches
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 400);
            }

            // Update the password
            $user->password = Hash::make($validated['new_password']);
            $user->save();

            return response()->json([
                'message' => 'Password updated successfully',
            ], 200);
        } catch (Exception $e) {
            // Log any errors that occur
            Log::error('Error updating password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the password.'
            ], 500);
        }
    }
}
