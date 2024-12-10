<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function showResetForm($token)
{
    return view('auth.passwords.reset', compact('token'));
}
    // Register a new user
    public function register(RegisterRequest $request, AuthService $authService)
    {
        // Get validated data from the request
        $validated = $request->validated();

        try {
            // Register the user and generate the token via the service
            $result = $authService->register($validated);

            // Return success response
            return response()->json([
                'message' => 'Registration successful',
                'user' => $result['user'],
                'token' => $result['token'],
            ], 201);
        } catch (\Exception $e) {
            // Handle any exceptions and return a structured error response
            return response()->json([
                'error' => 'Registration failed. ' . $e->getMessage(),
            ], 400);
        }
    }
    // Login an existing user
    public function login(LoginRequest $request)
    {
        try {
            // Call the AuthService to handle the login
            $response = $this->authService->login($request->validated());

            // Return success response
            return response()->json($response, 200);
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('Login failed: ' . $e->getMessage());

            // Return error response
            return response()->json([
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    // Logout an existing user
    public function logout(Request $request){
        try {
            // Delegate the logout logic to the service
            $this->authService->logout($request->user());

            // Return a success response
            return response()->json([
                'message' => 'Logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Logout failed: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'error' => 'Failed to log out. Please try again.',
            ], 500);
        }
    }

    // Forgot password
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $result = $this->authService->forgotPassword($request->email);

        if ($result['status']) {
            return response()->json(['message' => $result['message']], 200);
        }

        return response()->json(['message' => $result['message']], 400);
    }

    // Reset password
    public function resetPassword(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        $result = $this->authService->resetPassword($data);

        return response()->json($result['data'], $result['status']);
    }
}
