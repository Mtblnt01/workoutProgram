<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------
    // 1. /users/me (GET)
    // -----------------------------------------------------------
    public function test_me_requires_authentication()
    {
        $response = $this->getJson('/api/users/me');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_me_returns_user_data()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users/me');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'email', 'name']
                 ])
                 ->assertJsonPath('user.email', 'test@example.com');
    }

    // -----------------------------------------------------------
    // 2. /users/me (PUT)
    // -----------------------------------------------------------
    public function test_user_can_update_their_own_profile()
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'name' => 'Old Name'
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/users/me', [
            'email' => 'new@example.com',
            'name'  => 'New Name'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Profile updated successfully'])
                 ->assertJsonPath('user.email', 'new@example.com')
                 ->assertJsonPath('user.name', 'New Name');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'email' => 'new@example.com',
            'name'  => 'New Name',
        ]);
    }

    // -----------------------------------------------------------
    // 3. /users (GET) – minden user listázása
    // -----------------------------------------------------------
    public function test_any_logged_in_user_can_get_user_list()
    {
        $user = User::factory()->create();

        User::factory(3)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'users' => [
                         '*' => ['id', 'email', 'name']
                     ]
                 ]);

        $response->assertJsonCount(4, 'users'); // 1 bejelentkezett + 3 másik
    }

    // -----------------------------------------------------------
    // 4. /users/{id} (GET)
    // -----------------------------------------------------------
    public function test_user_can_view_specific_user()
    {
        $current = User::factory()->create();
        $target = User::factory()->create([
            'name' => 'Target User'
        ]);

        Sanctum::actingAs($current);

        $response = $this->getJson("/api/users/{$target->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('user.name', 'Target User');
    }

    // -----------------------------------------------------------
    // 5. /users/{id} (DELETE)
    // -----------------------------------------------------------
    public function test_user_can_delete_another_user()
    {
        $current = User::factory()->create();
        $userToDelete = User::factory()->create();

        Sanctum::actingAs($current);

        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully']);

        // Soft delete miatt az user még jelen van az adatbázisban, de deleted_at kitöltött
        $this->assertDatabaseHas('users', [
            'id' => $userToDelete->id,
        ]);
        
        // Ellenőrzés, hogy a user valóban töröltnek van-e jelölve
        $this->assertTrue(User::withTrashed()->find($userToDelete->id)->trashed());
    }
}

