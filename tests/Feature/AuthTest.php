<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_ping_endpoint_returns_ok()
    {
        $response = $this->getJson('/api/ping');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'API works!']);
    }

    public function test_register_creates_user()
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'teszt@example.com',
            'age' => 25
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'email', 'name', 'age']
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'teszt@example.com'
        ]);
    }

    public function test_login_with_valid_email()
    {
        // Arrange – hozzunk létre egy usert email-lel
        $user = User::factory()->create([
            'email' => 'valid@example.com',
        ]);

        // Act – Bejelentkezés
        $response = $this->postJson('/api/login', [
            'email' => 'valid@example.com'
        ]);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'user' => ['id', 'email'],
                     'access' => ['token', 'token_type']
                 ]);

        // Token létrejött?
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    public function test_login_with_invalid_email()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nemletezik@example.com'
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid email']);
    }

    public function test_logout_works()
    {
        // Hozzunk létre usert és tokent
        $user = User::factory()->create([
            'email' => 'logout@test.com'
        ]);

        $token = $user->createToken('test')->plainTextToken;

        // Kérés Authorization headerrel
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logout successful']);

        // token törlődött?
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }
}

