<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test unauthenticated users cannot view profiles.
     */
    public function test_unauthenticated_user_cannot_view_profile()
    {
        $response = $this->getJson('api/v1/profile');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    /**
     * Test authenticated users can view their profiles.
     */
    public function test_authenticated_user_can_view_their_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('api/v1/profile');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => $user->only([
                         'id', 'firstname', 'lastname', 'middlename', 'member_id', 'phone',
                         'whatsapp', 'date_of_birth', 'gender', 'next_of_kin', 'country',
                         'state', 'lga', 'occupation', 'email', 'role', 'created_at', 'updated_at'
                     ]),
                 ]);
    }

    /**
     * Test authenticated users can update their profiles.
     */
    public function test_authenticated_user_can_update_their_profile()
    {
        $user = User::factory()->create();

        $updateData = [
            'firstname' => 'UpdatedFirstname',
            'lastname' => 'UpdatedLastname',
            'phone' => '+1234567890',
            'email' => 'updatedemail@example.com',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("api/v1/update-profile/{$user->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'User updated successfully.',
                     'user' => array_merge(['id' => $user->id], $updateData),
                 ]);

        $this->assertDatabaseHas('users', array_merge(['id' => $user->id], $updateData));
    }

    /**
     * Test authenticated users can update their passwords.
     */
    public function test_authenticated_user_can_update_their_password()
    {
        $user = User::factory()->create(['password' => Hash::make('current-password')]);

        $passwordData = [
            'current_password' => 'current-password',
            'new_password' => 'new-secure-password',
            'new_password_confirmation' => 'new-secure-password',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("api/v1/update-password/{$user->id}", $passwordData);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Password updated successfully']);

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    /**
     * Test updating password fails with incorrect current password.
     */
    public function test_fails_to_update_password_with_incorrect_current_password()
    {
        $user = User::factory()->create(['password' => Hash::make('current-password')]);

        $passwordData = [
            'current_password' => 'wrongpassword',
            'new_password' => 'new-secure-password',
            'new_password_confirmation' => 'new-secure-password',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("api/v1/update-password/{$user->id}", $passwordData);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'Current password is incorrect']);

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }

    /**
     * Test users cannot update other users' profiles.
     */
    public function test_user_cannot_update_another_users_profile()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson("api/v1/update-profile/{$otherUser->id}", [
            'firstname' => 'HackerFirstname',
        ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized User or User Not Found']);

        $this->assertDatabaseMissing('users', [
            'id' => $otherUser->id,
            'firstname' => 'HackerFirstname',
        ]);
    }

    /**
     * Test users cannot update other users' passwords.
     */
    public function test_user_cannot_update_another_users_password()
    {
        $user = User::factory()->create(['password' => Hash::make('current-password')]);
        $otherUser = User::factory()->create(['password' => Hash::make('other-password')]);

        $passwordData = [
            'current_password' => 'current-password',
            'new_password' => 'new-secure-password',
            'new_password_confirmation' => 'new-secure-password',
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("api/v1/update-password/{$otherUser->id}", $passwordData);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Unauthorized User or User Not Found']);

        $this->assertTrue(Hash::check('other-password', $otherUser->fresh()->password));
    }
}
