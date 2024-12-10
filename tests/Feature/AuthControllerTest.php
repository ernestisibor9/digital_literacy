<?php
namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration with valid data.
     *
     * @return void
     */
    public function test_user_can_register()
    {
        // Define valid registration data
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Call the register method of the AuthController (actually hitting the API)
        $response = $this->json('POST', '/api/v1/register', $data);

        // Check if the response has a 201 status code (Created)
        $response->assertStatus(Response::HTTP_CREATED);

        // Assert that the response contains 'message', 'user', and 'token'
        $response->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

        // Ensure the user was added to the database
        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
        ]);

        // Assert that the password was hashed
        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    // Test registration fails if the email is missing.
    public function test_registration_fails_if_email_is_missing()
    {
        // Define invalid registration data (email missing)
        $data = [
            'name' => 'John Doe',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Call the register method of the AuthController
        $response = $this->json('POST', '/api/v1/register', $data);

        // Assert that the response has a 422 status code (Unprocessable Entity)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Assert that the response contains validation errors for 'email'
        $response->assertJsonValidationErrors('email');
    }

    // Test registration fails if passwords do not match.
    public function test_registration_fails_if_passwords_do_not_match()
    {
        // Define invalid registration data (passwords do not match)
        $data = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ];

        // Call the register method of the AuthController
        $response = $this->json('POST', '/api/v1/register', $data);

        // Assert that the response has a 422 status code (Unprocessable Entity)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Assert that the response contains validation errors for 'password_confirmation'
        $response->assertJsonValidationErrors('password');
    }

    // Test registration fails if the user already exists.
    public function test_registration_fails_if_user_already_exists()
    {
        // Create a user in the database
        $existingUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Define registration data with an email that already exists
        $data = [
            'name' => 'John Doe',
            'email' => 'jane.doe@example.com',  // Duplicate email
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Call the register method of the AuthController
        $response = $this->json('POST', '/api/v1/register', $data);

        // Assert that the response has a 422 status code (Unprocessable Entity)
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // Assert that the response contains validation errors for 'email'
        $response->assertJsonValidationErrors('email');
    }



    // Test Login Succssfully
    public function test_user_can_login_with_valid_credentials()
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'), // Password will be hashed
        ]);

        // Prepare valid login data
        $data = [
            'email' => 'john.doe@example.com',
            'password' => 'password123', // Match password with the created user's password
        ];

        // Call the login endpoint
        $response = $this->json('POST', '/api/v1/login', $data);

        // Assert the status code is 200 (OK)
        $response->assertStatus(200);

        // Assert that the response contains the message, user, and token
        $response->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'email'],
            'token',
        ]);

        // Optionally, verify that the token is returned
        $this->assertNotEmpty($response->json('token'));
    }

    // Test login with invalid credentials (wrong password).
    public function test_user_cannot_login_with_invalid_password()
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'password' => Hash::make('password123'), // Password will be hashed
        ]);

        // Prepare invalid login data (wrong password)
        $data = [
            'email' => 'john.doe@example.com',
            'password' => 'wrongpassword', // Wrong password
        ];

        // Call the login endpoint
        $response = $this->json('POST', '/api/v1/login', $data);

        // Assert the status code is 401 (Unauthorized)
        $response->assertStatus(401);

        // Assert that the response contains the correct error message
        $response->assertJson([
            'error' => 'Invalid email or password',
        ]);
    }

    // Test successful logout.
    public function test_user_can_logout()
    {
        // Create a user
        $user = User::factory()->create();

        // Generate an API token for the user
        $token = $user->createToken('api_token')->plainTextToken;

        // Act as the user with the generated token
        $this->actingAs($user, 'sanctum');

        // Perform the logout request
        $response = $this->postJson('/api/v1/logout');

        // Assert that the response is successful
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);

        // Assert that the user's current token is deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'token' => hash('sha256', $token), // Sanctum stores hashed tokens
        ]);
    }

    // Test logout without authentication.
    public function test_user_cannot_logout_without_authentication()
    {
        // Perform the logout request without acting as a user
        $response = $this->postJson('/api/v1/logout');

        // Assert that the response is unauthorized
        $response->assertStatus(401);
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    // Test forgotPassword success response.
    public function testForgotPasswordSuccess()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        // Call the service method
        $authService = new AuthService();
        $result = $authService->forgotPassword($user->email);

        // Assertions
        $this->assertTrue($result['status']);
        $this->assertEquals('We have emailed your password reset link.', $result['message']);
    }
    // Test forgotPassword failure response with invalid email.
    public function testForgotPasswordFailure()
    {
        // Call the service method with an email that doesn't exist
        $authService = new AuthService();
        $result = $authService->forgotPassword('nonexistent@example.com');

        // Assertions
        $this->assertFalse($result['status']);
        $this->assertEquals('We can\'t find a user with that email address.', $result['message']);
    }
}
