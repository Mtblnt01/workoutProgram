<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;

class WorkoutSeeder extends Seeder
{
    public function run()
    {
        // Admin felhasználó
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'age' => 30,
            'role' => 'admin'
        ]);

        // Edzések
        $w1 = Workout::create([
            'title' => 'Chest Day',
            'description' => 'Bench press, incline press, flys',
            'difficulty' => 'hard'
        ]);

        $w2 = Workout::create([
            'title' => 'Leg Day',
            'description' => 'Squat, lunges, leg press',
            'difficulty' => 'medium'
        ]);

        $w3 = Workout::create([
            'title' => 'Back Day',
            'description' => 'Deadlift, pull-ups, rows',
            'difficulty' => 'hard'
        ]);

        $w4 = Workout::create([
            'title' => 'Cardio Session',
            'description' => 'Running, cycling, elliptical',
            'difficulty' => 'easy'
        ]);

        // Felhasználók lekérése (korábban létrehozottak)
        $user1 = User::where('email', 'balint@example.com')->first();
        $user2 = User::where('email', 'janos@example.com')->first();
        $user3 = User::where('email', 'peter@example.com')->first();

        // Beiratkozások
        if ($user1) {
            $user1->workouts()->attach($w1->id, [
                'progress' => 40,
                'last_done' => '2025-01-28'
            ]);
            $user1->workouts()->attach($w4->id, [
                'progress' => 50,
                'last_done' => '2025-01-25'
            ]);
        }

        if ($user2) {
            $user2->workouts()->attach($w2->id, [
                'progress' => 10,
                'last_done' => '2025-01-26'
            ]);
            $user2->workouts()->attach($w4->id, [
                'progress' => 30,
                'last_done' => '2025-01-24'
            ]);
        }

        if ($user3) {
            $user3->workouts()->attach($w3->id, [
                'progress' => 75,
                'last_done' => '2025-01-27'
            ]);
        }

        // Admin felhasználó összes edzéshez hozzáfér
        $admin->workouts()->attach([$w1->id, $w2->id, $w3->id, $w4->id], [
            'progress' => 100,
            'last_done' => now()
        ]);
    }
}
