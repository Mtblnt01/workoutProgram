<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\UserWorkout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    /**
     * GET /workouts
     * Összes workout rövid listája.
     */
    public function index(Request $request)
    {
        $workouts = Workout::select('id', 'title', 'description', 'difficulty')->get();

        return response()->json([
            'workouts' => $workouts
        ]);
    }

    /**
     * GET /workouts/{workout}
     * Workout részletes adatai + csatlakozott felhasználók.
     */
    public function show(Workout $workout)
    {
        $students = $workout->users()
            ->select('name', 'email')
            ->withPivot('progress', 'last_done')
            ->get()
            ->map(function ($user) {
                return [
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'progress'  => $user->pivot->progress,
                    'last_done' => $user->pivot->last_done,
                ];
            });

        return response()->json([
            'workout' => [
                'title'       => $workout->title,
                'description' => $workout->description,
                'difficulty'  => $workout->difficulty,
            ],
            'students' => $students
        ]);
    }

    /**
     * POST /workouts/{workout}/enroll
     * Felhasználó hozzáadása egy workouthoz.
     */
    public function enroll(Workout $workout, Request $request)
    {
        $user = $request->user();

        // Ellenőrizni, hogy már hozzárendelték-e
        if ($user->workouts()->where('workout_id', $workout->id)->exists()) {
            return response()->json(['message' => 'Already enrolled in this workout'], 409);
        }

        // Hozzáadás alap progress értékkel
        $user->workouts()->attach($workout->id, [
            'progress' => 0,
            'last_done' => null
        ]);

        return response()->json(['message' => 'Successfully enrolled into workout']);
    }

    /**
     * POST /workouts/{workout}/complete
     * Workout teljesítése → növeli a progress-t + last_done dátum
     */
    public function complete(Workout $workout, Request $request)
    {
        $user = $request->user();

        $record = UserWorkout::where('user_id', $user->id)
            ->where('workout_id', $workout->id)
            ->first();

        if (! $record) {
            return response()->json(['message' => 'Not enrolled in this workout'], 403);
        }

        // Ha már teljesen kész (progress = 100)
        if ($record->progress >= 100) {
            return response()->json(['message' => 'Workout already completed'], 409);
        }

        // Progress növelése (pl. +25 minden teljesítés)
        $newProgress = min(100, $record->progress + 25);

        $record->update([
            'progress'  => $newProgress,
            'last_done' => now(),
        ]);

        return response()->json([
            'message' => 'Workout progress updated',
            'progress' => $newProgress
        ]);
    }
}
