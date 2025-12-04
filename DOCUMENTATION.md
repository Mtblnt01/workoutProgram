# 🏋️ Edzéstervező Alkalmazás - Teljes Dokumentáció

## 📋 Tartalomjegyzék
1. [Projekt Áttekintése](#projekt-áttekintése)
2. [Adatbázis Séma](#adatbázis-séma)
3. [Telepítés és Beállítás](#telepítés-és-beállítás)
4. [API Végpontok](#api-végpontok)
5. [Modellek](#modellek)
6. [Controllerek](#controllerek)
7. [Tesztek](#tesztek)
8. [Fejlesztés](#fejlesztés)

---

## Projekt Áttekintése

**Edzéstervező alkalmazás** egy Laravel-alapú REST API, amely lehetővé teszi a felhasználók számára:
- Regisztráció és bejelentkezés (tokenek alapján)
- Edzések megtekintése és beiratkozás
- Beiratkozások nyomon követése
- Felhasználó profil kezelése

### Technológia Stack
- **Backend**: Laravel 11
- **Adatbázis**: SQLite / MySQL
- **API Autentikáció**: Laravel Sanctum
- **Tesztelés**: PHPUnit

---

## Adatbázis Séma

### Users (Felhasználók)
```sql
CREATE TABLE users (
    id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
    name                VARCHAR(255) NOT NULL,
    email               VARCHAR(255) UNIQUE NOT NULL,
    password            VARCHAR(255) NULLABLE,
    role                ENUM('student', 'admin') DEFAULT 'student',
    age                 INT NULLABLE,
    deleted_at          TIMESTAMP NULLABLE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Mezők magyarázata:**
- `id`: Egyedi felhasználó azonosító
- `name`: Felhasználó teljes neve
- `email`: Egyedi email cím
- `password`: Jelszó (jelen esetben NULL, nem kötelező)
- `role`: Szerepkör (hallgató vagy admin)
- `age`: Életkor (opcionális)
- `deleted_at`: Soft delete időpontja (NULL ha aktív)

### Workouts (Edzések)
```sql
CREATE TABLE workouts (
    id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
    title               VARCHAR(255) NOT NULL,
    description         TEXT NOT NULL,
    difficulty          VARCHAR(50) NOT NULL,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### User Workouts (Felhasználó-Edzés Kapcsolat)
```sql
CREATE TABLE user_workouts (
    id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id             BIGINT NOT NULL,
    workout_id          BIGINT NOT NULL,
    progress            INT DEFAULT 0,
    last_done           TIMESTAMP NULLABLE,
    completed_at        TIMESTAMP NULLABLE,
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (workout_id) REFERENCES workouts(id)
);
```

---

## Telepítés és Beállítás

### 1. Projekt Klónozása
```bash
git clone <repository-url>
cd workoutProgram
```

### 2. Függőségek Telepítése
```bash
composer install
```

### 3. Environment Beállítása
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Adatbázis Migrációk Futtatása
```bash
php artisan migrate
```

**Vagy az adatbázis nullázása (fejlesztéskor):**
```bash
php artisan migrate:fresh
```

### 5. Adatbázis Seedelése (Teszt Adatok)
```bash
php artisan db:seed
```

### 6. Szerver Indítása
```bash
php artisan serve
```

Az API ekkor elérhető lesz: `http://localhost:8000/api`

---

## API Végpontok

### Autentikáció

#### Regisztráció

<img width="381" height="247" alt="image" src="https://github.com/user-attachments/assets/d44d0744-4491-423d-bce9-796b179b8e5c" />
<img width="342" height="148" alt="image" src="https://github.com/user-attachments/assets/6dd274b7-6999-4497-b1de-1420d8c26017" />


**Válasz (201 Created):**

<img width="463" height="278" alt="image" src="https://github.com/user-attachments/assets/824280e0-c8dc-4da6-be54-b3463f0d4f93" />


#### Bejelentkezés
<img width="415" height="258" alt="image" src="https://github.com/user-attachments/assets/ab5d1f32-872d-46af-b41f-a95f36f3749b" />


**Válasz (200 OK):**

<img width="630" height="330" alt="image" src="https://github.com/user-attachments/assets/2627b515-5d84-41fd-a03c-dab12e3341e9" />


#### Kijelentkezés

<img width="455" height="341" alt="image" src="https://github.com/user-attachments/assets/985fab85-092a-4c08-87c8-8c600545182d" />



**Válasz (200 OK):**

<img width="403" height="178" alt="image" src="https://github.com/user-attachments/assets/522374f9-1b5a-47f0-9ba9-2214030d1334" />


### Felhasználó Kezelés

#### Saját Profil Lekérése
```http
GET /api/users/me
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "user": {
        "id": 1,
        "name": "Balint",
        "email": "balint@gmail.com"
    },
    "stats": {
        "enrolledCourses": 3,
        "completedCourses": 1
    }
}
```

#### Profil Frissítése
```http
PUT /api/users/me
Authorization: Bearer <token>
Content-Type: application/json

{
    "name": "Balint Nagy",
    "email": "balint.nagy@gmail.com"
}
```

**Válasz:**
```json
{
    "message": "Profile updated successfully",
    "user": {
        "id": 1,
        "name": "Balint Nagy",
        "email": "balint.nagy@gmail.com"
    }
}
```

#### Összes Felhasználó Listázása
```http
GET /api/users
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "users": [
        {
            "id": 1,
            "name": "Balint",
            "email": "balint@gmail.com"
        },
        {
            "id": 2,
            "name": "János",
            "email": "janos@gmail.com"
        }
    ]
}
```

#### Konkrét Felhasználó Lekérése
```http
GET /api/users/{id}
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "user": {
        "id": 2,
        "name": "János",
        "email": "janos@gmail.com"
    },
    "stats": {
        "enrolledCourses": 2,
        "completedCourses": 0
    }
}
```

#### Felhasználó Törlése (Soft Delete)
```http
DELETE /api/users/{id}
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "message": "User deleted successfully"
}
```

---

### Edzések

#### Összes Edzés Listázása
```http
GET /api/workouts
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "workouts": [
        {
            "id": 1,
            "title": "Kezdő Cardio",
            "description": "20 perces intenzív futás",
            "difficulty": "easy"
        }
    ]
}
```

#### Konkrét Edzés Megtekintése
```http
GET /api/workouts/{id}
Authorization: Bearer <token>
```

#### Beiratkozás Edzésre
```http
POST /api/workouts/{id}/enroll
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "message": "Enrolled successfully"
}
```

#### Edzés Befejezésének Jelölése
```http
PATCH /api/workouts/{id}/complete
Authorization: Bearer <token>
```

**Válasz:**
```json
{
    "message": "Workout marked as completed"
}
```

---

## Modellek

### User Model
**Fájl:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'role',
        'age'
    ];

    protected $hidden = [
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Reláció: a felhasználó által beiratkozottak az edzések közül.
     */
    public function enrollments()
    {
        return $this->hasMany(\App\Models\UserWorkout::class, 'user_id');
    }
}
```

**Vonások:**
- `HasFactory`: Model factory támogatás
- `HasApiTokens`: Laravel Sanctum token kezelés
- `SoftDeletes`: Soft delete támogatás (logikai törlés)
- `Notifiable`: Notifikáció támogatás

---

### UserWorkout Model
**Fájl:** `app/Models/UserWorkout.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWorkout extends Model
{
    use HasFactory;

    protected $table = 'user_workouts';

    protected $fillable = [
        'user_id',
        'workout_id',
        'progress',
        'last_done',
        'completed_at'
    ];

    protected $casts = [
        'last_done' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
}
```

---

### Workout Model
**Fájl:** `app/Models/Workout.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'difficulty'
    ];

    public function enrollments()
    {
        return $this->hasMany(UserWorkout::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_workouts');
    }
}
```

---

## Controllerek

### AuthController
**Fájl:** `app/Http/Controllers/AuthController.php`

**Létrehozási parancs:**
```bash
php artisan make:controller AuthController
```

**Végpontok:**
- `POST /register` - Regisztráció
- `POST /login` - Bejelentkezés
- `POST /logout` - Kijelentkezés

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|unique:users,email',
                'name' => 'required|string|max:255',
                'age' => 'required|integer|max:80'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'age' => $request->age
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age
            ],
            'access' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
```

---

### UserController
**Fájl:** `app/Http/Controllers/UserController.php`

**Létrehozási parancs:**
```bash
php artisan make:controller UserController
```

**Végpontok:**
- `GET /users/me` - Saját profil
- `PUT /users/me` - Profil frissítés
- `GET /users` - Összes felhasználó
- `GET /users/{id}` - Konkrét felhasználó
- `DELETE /users/{id}` - Felhasználó törlés

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'enrolledCourses'  => $user->enrollments()->count(),
                'completedCourses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            ]
        ], 200);
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        if ($request->name) {
            $user->name = $request->name;
        }
        if ($request->email) {
            $user->email = $request->email;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function index()
    {
        $users = User::all()->map(function ($user) {
            return [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ];
        });

        return response()->json([
            'users' => $users
        ]);
    }

    public function show($id)
    {
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->trashed()) {
            return response()->json(['message' => 'User is deleted'], 404);
        }

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'stats' => [
                'enrolledCourses'  => $user->enrollments()->count(),
                'completedCourses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            ]
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete(); // Soft delete

        return response()->json(['message' => 'User deleted successfully']);
    }
}
```

---

### WorkoutController
**Fájl:** `app/Http/Controllers/WorkoutController.php`

**Létrehozási parancs:**
```bash
php artisan make:controller WorkoutController
```

**Végpontok:**
- `GET /workouts` - Összes edzés
- `GET /workouts/{id}` - Konkrét edzés
- `POST /workouts/{id}/enroll` - Beiratkozás
- `PATCH /workouts/{id}/complete` - Befejezés jelölése

```php
<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\UserWorkout;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index()
    {
        $workouts = Workout::all();

        return response()->json([
            'workouts' => $workouts
        ]);
    }

    public function show(Workout $workout)
    {
        return response()->json([
            'workout' => $workout
        ]);
    }

    public function enroll(Request $request, Workout $workout)
    {
        $user = $request->user();

        // Már beiratkozott-e?
        $existing = UserWorkout::where('user_id', $user->id)
                                ->where('workout_id', $workout->id)
                                ->first();

        if ($existing) {
            return response()->json(['message' => 'Already enrolled'], 422);
        }

        UserWorkout::create([
            'user_id' => $user->id,
            'workout_id' => $workout->id,
            'progress' => 0
        ]);

        return response()->json(['message' => 'Enrolled successfully'], 201);
    }

    public function complete(Request $request, Workout $workout)
    {
        $user = $request->user();

        $enrollment = UserWorkout::where('user_id', $user->id)
                                  ->where('workout_id', $workout->id)
                                  ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Not enrolled'], 404);
        }

        $enrollment->update([
            'completed_at' => now(),
            'last_done' => now()
        ]);

        return response()->json(['message' => 'Workout marked as completed']);
    }
}
```

---

## Tesztek

### Test Fájlok

<img width="518" height="549" alt="image" src="https://github.com/user-attachments/assets/e4ad0be5-448c-4317-9151-e5b872b9568d" />


---

## Fejlesztés

### Hasznos Parancsok

#### Model Létrehozása
```bash
php artisan make:model ModelName
# Migration-nel
php artisan make:model ModelName -m
```

#### Controller Létrehozása
```bash
php artisan make:controller ControllerName
```

#### Migration Létrehozása
```bash
php artisan make:migration create_table_name
```

#### Teszt Létrehozása
```bash
php artisan make:test TestName
# Feature test
php artisan make:test TestName --unit
```

#### Seeding
```bash
# Adatbázis feltöltése teszt adatokkal
php artisan db:seed

# Fresh + Seed
php artisan migrate:fresh --seed
```

#### Tesztek Futtatása
```bash
# Összes teszt
php artisan test

# Konkrét fájl
php artisan test tests/Feature/UserTest.php
```

---

### Environment Beállítások

**.env fájl:**
```env
APP_NAME=WorkoutProgram
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# vagy MySQL-hez:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=workout_program
# DB_USERNAME=root
# DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
SANCTUM_GUARD=web
```

---

### Rúterink Konfiguráció

**Fájl:** `routes/api.php`

```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkoutController;

// -------------------------
// PUBLIC ROUTES
// -------------------------
Route::get('/ping', function () {
    return response()->json(['message' => 'API works!']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// -------------------------
// AUTHENTICATED ROUTES
// -------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // User
    Route::get('/users/me', [UserController::class, 'me']);
    Route::put('/users/me', [UserController::class, 'updateMe']);

    // User listing
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Workouts
    Route::get('/workouts', [WorkoutController::class, 'index']);
    Route::get('/workouts/{workout}', [WorkoutController::class, 'show']);
    Route::post('/workouts/{workout}/enroll', [WorkoutController::class, 'enroll']);
    Route::patch('/workouts/{workout}/complete', [WorkoutController::class, 'complete']);
});
```

---
