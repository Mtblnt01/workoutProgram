<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Normál felhasználók
        User::factory()->create([
            'name' => 'Balint Nagy',
            'email' => 'balint@example.com',
            'age' => 25,
            'role' => 'student'
        ]);

        User::factory()->create([
            'name' => 'János Kovács',
            'email' => 'janos@example.com',
            'age' => 28,
            'role' => 'student'
        ]);

        User::factory()->create([
            'name' => 'Péter Szabó',
            'email' => 'peter@example.com',
            'age' => 22,
            'role' => 'student'
        ]);

        // További random felhasználók
        User::factory(5)->create();

        // Workout seeder futtatása (amely az admint is létrehozza)
        $this->call(WorkoutSeeder::class);
    }
}
