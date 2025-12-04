<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserWorkoutFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id'    => \App\Models\User::factory(),
            'workout_id' => \App\Models\Workout::factory(),
            'progress'   => $this->faker->numberBetween(0, 100),
            'last_done'  => $this->faker->date(),
        ];
    }
}
