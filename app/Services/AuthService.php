<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Password;

class AuthService
{
    // Register a new user
    public function register(array $data)
    {
        // Create the user with the provided data
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Generate API token
        $token = $user->createToken('api_token')->plainTextToken;

        // Return the created user and token
        return [
            'user' => $user,
            'token' => $token,
        ];
    }
    // Login a user
    public function login(array $credentials)
    {
        // Check user credentials and attempt login
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Create API token for the user
            $token = $user->createToken('api_token')->plainTextToken;

            return [
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ];
        }

        // If login fails, throw an exception
        throw new \Exception('Invalid email or password', 401);
    }
    // Logout
    public function logout($user)
    {
        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        // Delete the user's current access token
        $user->currentAccessToken()->delete();

        // Log the logout event (optional)
        Log::info('User logged out', ['user_id' => $user->id]);
    }

    // Forgot password
    public function forgotPassword(string $email)
    {
        $response = Password::sendResetLink(['email' => $email]);

        if ($response === Password::RESET_LINK_SENT) {
            return ['status' => true, 'message' => Lang::get($response)];
        }

        return ['status' => false, 'message' => Lang::get($response)];
    }

    // Reset password
    public function resetPassword(array $data): array
    {
        // Validate the password reset token and data
        $status = Password::reset($data, function ($user, $password) {
            $this->updatePassword($user, $password);
        });

        return $this->getResponse($status);
    }

    protected function updatePassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
        ])->save();

        // Optionally, revoke all tokens
        $user->tokens()->delete();

        Log::info('Password reset successfully for user', ['user_id' => $user->id]);
    }

    protected function getResponse(string $status): array
    {
        if ($status === Password::PASSWORD_RESET) {
            return [
                'status' => 200,
                'data' => ['message' => 'Password reset successfully'],
            ];
        }


        Log::warning('Password reset failed', ['status' => $status]);

        return [
            'status' => 500,
            'data' => ['error' => 'Invalid token or reset failed'],
        ];
    }
}
