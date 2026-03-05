<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('test123'),
        ]);

        $response = $this->post('/api/v1/login', [
            'email' => $user->email,
            'password' => 'test123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'access_token',
            'token_type',
            'user',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_it_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => bcrypt('password')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    public function test_it_cannot_login_validation_requires_fields()
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_it_can_view_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email
                ]
            ]);
    }

    public function test_it_cannot_access_profile()
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    public function test_it_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/logout');


        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
