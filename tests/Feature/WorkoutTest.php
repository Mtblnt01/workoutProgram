<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workout;
use App\Models\UserWorkout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------
    // 1. /workouts (GET) - Összes edzés listázása
    // -----------------------------------------------------------
    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/workouts');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_student_can_view_workouts()
    {
        $user = User::factory()->create(['role' => 'student']);
        $workout = Workout::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/workouts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'workouts' => [
                         '*' => ['id', 'title', 'description', 'difficulty']
                     ]
                 ]);
    }

    public function test_admin_can_view_workouts()
    {
        $admin = User::factory()->admin()->create();
        $workout = Workout::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/workouts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'workouts' => [
                         '*' => ['id', 'title', 'description', 'difficulty']
                     ]
                 ]);
    }

    // -----------------------------------------------------------
    // 2. /workouts/{id} (GET) - Konkrét edzés megtekintése
    // -----------------------------------------------------------
    public function test_user_can_view_specific_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create([
            'title' => 'Test Workout'
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/workouts/{$workout->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('workout.title', 'Test Workout');
    }

    // -----------------------------------------------------------
    // 3. /workouts/{id}/enroll (POST) - Beiratkozás
    // -----------------------------------------------------------
    public function test_user_can_enroll_in_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/workouts/{$workout->id}/enroll");

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Enrolled successfully']);

        $this->assertDatabaseHas('user_workouts', [
            'user_id' => $user->id,
            'workout_id' => $workout->id
        ]);
    }

    public function test_user_cannot_enroll_twice()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();

        // Első beiratkozás
        UserWorkout::create([
            'user_id' => $user->id,
            'workout_id' => $workout->id,
            'progress' => 0
        ]);

        Sanctum::actingAs($user);

        // Második beiratkozás kísérlet
        $response = $this->postJson("/api/workouts/{$workout->id}/enroll");

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Already enrolled']);
    }

    // -----------------------------------------------------------
    // 4. /workouts/{id}/complete (PATCH) - Befejezés jelölése
    // -----------------------------------------------------------
    public function test_user_can_mark_workout_as_completed()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();

        // Beiratkozás
        UserWorkout::create([
            'user_id' => $user->id,
            'workout_id' => $workout->id,
            'progress' => 50
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/workouts/{$workout->id}/complete");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Workout marked as completed']);

        $this->assertDatabaseHas('user_workouts', [
            'user_id' => $user->id,
            'workout_id' => $workout->id
        ]);

        // Ellenőrzés, hogy completed_at kitöltődött
        $enrollment = UserWorkout::where('user_id', $user->id)
                                  ->where('workout_id', $workout->id)
                                  ->first();
        
        $this->assertNotNull($enrollment->completed_at);
    }

    public function test_user_cannot_complete_not_enrolled_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/workouts/{$workout->id}/complete");

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Not enrolled']);
    }

    // -----------------------------------------------------------
    // 5. Admin szerepkör tesztek
    // -----------------------------------------------------------
    public function test_admin_user_has_admin_role()
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals('admin', $admin->role);
    }

    public function test_student_user_has_student_role()
    {
        $student = User::factory()->create();

        $this->assertEquals('student', $student->role);
    }
}
