<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;

class WorkoutSeeder extends Seeder
{
    public function run()
    {
        $users = User::factory(10)->create();
        $workouts = Workout::factory(10)->create();

        // Users
        $user1 = User::create([
            'name' => 'Bálint',
            'email' => 'balint@example.com',
            'age' => 22
        ]);

        $user2 = User::create([
            'name' => 'Krisztián',
            'email' => 'krisztian@example.com',
            'age' => 25
        ]);

        // Workouts
        $w1 = Workout::create([
            'title' => 'Chest Day',
            'description' => 'Bench, incline, flys',
            'difficulty' => 'hard'
        ]);

        $w2 = Workout::create([
            'title' => 'Leg Day',
            'description' => 'Squat, lunges, leg press',
            'difficulty' => 'medium'
        ]);

        // Attach with pivot
        $user1->workouts()->attach($w1->id, [
            'progress' => 40,
            'last_done' => '2025-01-28'
        ]);

        $user2->workouts()->attach($w2->id, [
            'progress' => 10,
            'last_done' => '2025-01-26'
        ]);
    }
}
