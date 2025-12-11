# Edzésprogram REST API megvalósítása Laravel környezetben

**base_url:** `http://127.0.0.1:8000/api`

Az API-t olyan funkciókkal kell ellátni, amelyek lehetővé teszik annak nyilvános elérhetőségét.  Ennek a backendnek a fő célja, hogy kiszolgálja a frontendet, amelyet a felhasználók edzésprogramokra való feliratkozásra és az edzéseik nyomon követésére használnak.

**Funkciók:**
- Authentikáció (login, token kezelés) - jelszó nélküli bejelentkezés email alapján
- Felhasználó beiratkozhat egy edzésprogramra
- Edzésprogram teljesítési státuszának (progress) követése
- Admin felhasználók kezelhetik a többi felhasználót
- A teszteléshez készíts: 
  - 1 admin felhasználót (admin@example.com)
  - 5 student felhasználót (különböző korosztályok)
  - 3 releváns edzésprogramot (különböző nehézségi szintekkel)
  - Néhány beiratkozást különböző progress értékekkel

Az adatbázis neve: `workout_program`

## Végpontok:
A `Content-Type` és az `Accept` headerkulcsok mindig `application/json` formátumúak legyenek. 

Érvénytelen vagy hiányzó token esetén a backendnek `401 Unauthorized` választ kell visszaadnia: 
```json
Response:  401 Unauthorized
{
  "message": "Unauthenticated."
}
```

### Nem védett végpontok:
- **GET** `/ping` - teszteléshez
- **POST** `/register` - regisztrációhoz
- **POST** `/login` - belépéshez (jelszó nélkül, csak email)

### Hibák: 
- 400 Bad Request:  A kérés hibás formátumú.  Ezt a hibát akkor kell visszaadni, ha a kérés hibásan van formázva, vagy ha hiányoznak a szükséges mezők. 
- 401 Unauthorized: A felhasználó nem jogosult a kérés végrehajtására.  Ezt a hibát akkor kell visszaadni, ha érvénytelen a token. 
- 403 Forbidden: A felhasználó nem jogosult a kérés végrehajtására. Ezt a hibát akkor kell visszaadni, ha a felhasználó nem admin, vagy nincs beiratkozva az edzésprogramra. 
- 404 Not Found: A kért erőforrás nem található. Ezt a hibát akkor kell visszaadni, ha a kért edzésprogram vagy felhasználó nem található. 
- 422 Unprocessable Entity:  Validációs hiba. Ezt a hibát akkor kell visszaadni, ha a kérés adatai nem felelnek meg a validációs szabályoknak.

---

## Felhasználókezelés

**POST** `/register`

Új felhasználó regisztrálása.  Jelszó megadása nem szükséges.  Az email címnek egyedinek kell lennie.  Alapértelmezett role: `student`.

Kérés Törzse:
```json
{
    "name": "Kiss János",
    "email": "janos@example.com",
    "age": 25
}
```

Válasz (sikeres regisztráció esetén): `201 Created`
```json
{
    "message": "User created successfully",
    "user":  {
        "id": 1,
        "name": "Kiss János",
        "email": "janos@example.com",
        "age": 25,
        "role": "student"
    }
}
```

Automatikus válasz felüldefiniálása (ha az e-mail cím már foglalt): `422 Unprocessable Entity`
```json
{
  "message": "Failed to register user",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

---

**POST** `/login`

Bejelentkezés csak e-mail címmel (jelszó nélkül).

Kérés Törzse:
```json
{
  "email": "janos@example.com"
}
```

Válasz (sikeres bejelentkezés esetén): `200 OK`
```json
{
    "message": "Login successful",
    "user": {
        "id":  1,
        "name":  "Kiss János",
        "email": "janos@example.com",
        "age": 25,
        "role": "student"
    },
    "access":  {
        "token": "2|7Fbr79b5zn8RxMfOqfdzZ31SnGWvgDidjahbdRfL2a98cfd8",
        "token_type": "Bearer"
    }
}
```

Válasz (sikertelen bejelentkezés esetén): `401 Unauthorized`
```json
{
  "message": "Invalid email"
}
```

---

> Az innen következő végpontok autentikáltak, tehát a kérés headerjében meg kell adni a tokent is

> Authorization: "Bearer 2|7Fbr79b5zn8RxMfOqfdzZ31SnGWvgDidjahbdRfL2a98cfd8"

**POST** `/logout`

A jelenlegi autentikált felhasználó kijelentkeztetése, a felhasználó tokenjének törlése.  Ha a token érvénytelen, a fent meghatározott általános `401 Unauthorized` hibát kell visszaadnia. 

Válasz (sikeres kijelentkezés esetén): `200 OK`
```json
{
  "message": "Logout successful"
}
```

---

**GET** `/users/me`

Saját felhasználói profil és edzésstatisztikák lekérése.

Válasz:  `200 OK`
```json
{
    "user": {
        "id": 1,
        "name": "Kiss János",
        "email": "janos@example.com"
    },
    "stats": {
        "enrolledCourses": 3,
        "completedCourses": 1
    }
}
```

---

**PUT** `/users/me`

Saját felhasználói adatok frissítése.  Az aktuális felhasználó módosíthatja a nevét és/vagy e-mail címét.

Kérés törzse:
```json
{
  "name": "Kiss János Péter",
  "email": "ujmail@example.com"
}
```

Válasz (sikeres frissítés): `200 OK`
```json
{
  "message": "Profile updated successfully",
  "user": {
    "id": 1,
    "name": "Kiss János Péter",
    "email": "ujmail@example.com"
  }
}
```

*Hibák: *
- `422 Unprocessable Entity` – érvénytelen vagy hiányzó mezők, vagy az e-mail már foglalt
- `401 Unauthorized` – ha a token érvénytelen vagy hiányzik

---

**GET** `/users`

Az összes felhasználó listájának lekérése.

Válasz: `200 OK`
```json
{
    "users": [
        {
            "id": 1,
            "name": "Kiss János",
            "email": "janos@example.com"
        },
        {
            "id": 2,
            "name": "Nagy Anna",
            "email": "anna@example.com"
        },
        {
            "id":  3,
            "name":  "Kovács Péter",
            "email": "peter@example.com"
        }
    ]
}
```

---

**GET** `/users/:id`

Egy felhasználó profiljának és statisztikáinak lekérése.

Válasz: `200 OK`
```json
{
  "user": {
    "id": 2,
    "name": "Nagy Anna",
    "email": "anna@example.com"
  },
  "stats":  {
    "enrolledCourses": 2,
    "completedCourses": 1
  }
}
```

Ha törölt (soft deleted) felhasználót próbáltunk megnézni: 

Válasz: `404 Not Found`
```json
{
  "message": "User is deleted"
}
```

Ha nem létező felhasználót próbáltunk megnézni:

Válasz: `404 Not Found`
```json
{
  "message": "User not found"
}
```

---

**DELETE** `/users/:id`

Egy felhasználó törlése (Soft Delete).

Ha a felhasználó már törlésre került, vagy nem létezik, a megfelelő hibaüzenetet adja vissza.

Válasz (sikeres törlés esetén): `200 OK`
```json
{
  "message": "User deleted successfully"
}
```

Válasz (ha a felhasználó nem található): `404 Not Found`
```json
{
  "message": "User not found"
}
```

---

## Edzésprogram kezelés

**GET** `/workouts`

Az összes elérhető edzésprogram listájának lekérése.

Válasz: `200 OK`
```json
{
  "workouts": [
    {
      "id": 1,
      "title": "Kezdő Full Body",
      "description": "Teljes test edzés kezdőknek",
      "difficulty": "easy"
    },
    {
      "id": 2,
      "title": "Haladó erősítő",
      "description": "Intenzív erősítő edzés haladóknak",
      "difficulty": "hard"
    },
    {
      "id":  3,
      "title":  "Cardio mix",
      "description": "Vegyes kardió edzés",
      "difficulty": "medium"
    }
  ]
}
```

---

**GET** `/workouts/:id`

Információk lekérése egy adott edzésprogramról és a hozzá csatlakozott felhasználókról.

Válasz: `200 OK`
```json
{
    "workout": {
        "title": "Kezdő Full Body",
        "description": "Teljes test edzés kezdőknek",
        "difficulty": "easy"
    },
    "students": [
        {
            "name": "Kiss János",
            "email": "janos@example. com",
            "progress": 75,
            "last_done": "2025-12-10"
        },
        {
            "name": "Nagy Anna",
            "email": "anna@example.com",
            "progress": 25,
            "last_done":  "2025-12-08"
        }
    ]
}
```

Automatikus válasz (ha az edzésprogram nem található): `404 Not Found`

---

**POST** `/workouts/: id/enroll`

A jelenlegi felhasználó beiratkozása egy edzésprogramra. 

Válasz (sikeres beiratkozás esetén): `201 Created`
```json
{
  "message": "Enrolled successfully"
}
```

Válasz (ha már beiratkozott): `422 Unprocessable Entity`
```json
{
  "message": "Already enrolled"
}
```

Automatikus válasz (ha az edzésprogram nem található): `404 Not Found`

---

**POST** `/workouts/:id/complete`

A jelenlegi felhasználó edzésprogramjának teljesítése.  Ez a progress-t 100%-ra állítja és kitölti a `completed_at` mezőt.

Válasz (sikeres teljesítés esetén): `200 OK`
```json
{
  "message": "Workout marked as completed"
}
```

Válasz (ha nincs beiratkozva): `404 Not Found`
```json
{
  "message": "Not enrolled"
}
```

---

## Összefoglalva

| HTTP metódus | Útvonal                    | Jogosultság  | Státuszkódok                                          | Rövid leírás                                      |
|--------------|----------------------------|--------------|-------------------------------------------------------|---------------------------------------------------|
| GET          | /ping                      | Nyilvános    | 200 OK                                                | API teszteléshez                                  |
| POST         | /register                  | Nyilvános    | 201 Created, 422 Unprocessable Entity                 | Új felhasználó regisztrációja                     |
| POST         | /login                     | Nyilvános    | 200 OK, 401 Unauthorized                              | Bejelentkezés e-maillel (jelszó nélkül)          |
| POST         | /logout                    | Hitelesített | 200 OK, 401 Unauthorized                              | Kijelentkezés                                     |
| GET          | /users/me                  | Hitelesített | 200 OK, 401 Unauthorized                              | Saját profil és statisztikák lekérése             |
| PUT          | /users/me                  | Hitelesített | 200 OK, 422 Unprocessable Entity, 401 Unauthorized    | Saját profil adatainak módosítása                 |
| GET          | /users                     | Hitelesített | 200 OK, 401 Unauthorized                              | Összes felhasználó listázása                      |
| GET          | /users/:id                 | Hitelesített | 200 OK, 404 Not Found, 401 Unauthorized               | Bármely felhasználó profiljának lekérése          |
| DELETE       | /users/:id                 | Hitelesített | 200 OK, 404 Not Found, 401 Unauthorized               | Felhasználó törlése (Soft Delete)                 |
| GET          | /workouts                  | Hitelesített | 200 OK, 401 Unauthorized                              | Edzésprogramok listázása                          |
| GET          | /workouts/:id              | Hitelesített | 200 OK, 404 Not Found, 401 Unauthorized               | Egy edzésprogram részletei                        |
| POST         | /workouts/:id/enroll       | Hitelesített | 201 Created, 422 Unprocessable Entity, 404 Not Found  | Beiratkozás edzésprogramra                        |
| POST         | /workouts/:id/complete     | Hitelesített | 200 OK, 404 Not Found, 401 Unauthorized               | Edzésprogram teljesítése (100% + completed_at)    |

---

## Adatbázis terv: 

```
+---------------------+     +---------------------+       +------------------+        +-------------+
|personal_access_tokens|    |        users        |       |  user_workouts   |        |  workouts   |
+---------------------+     +---------------------+       +------------------+        +-------------+
| id (PK)             |   _1| id (PK)             |1__    | id (PK)          |     __1| id (PK)     |
| tokenable_id (FK)   |K_/  | name                |   \__N| user_id (FK)     |    /   | title       |
| tokenable_type      |     | email (unique)      |       | workout_id (FK)  |M__/    | description |
| name                |     | role (student/admin)|       | progress         |        | difficulty  |
| token (unique)      |     | age                 |       | last_done        |        | created_at  |
| abilities           |     | deleted_at          |       | completed_at     |        | updated_at  |
| last_used_at        |     | created_at          |       | created_at       |        +-------------+
| expires_at          |     | updated_at          |       | updated_at       |
| created_at          |     +---------------------+       +------------------+
| updated_at          |
+---------------------+
```

---

# I. Modul:  Struktúra kialakítása

## 1. Telepítés (projekt létrehozása, . env konfiguráció, sanctum telepítése, tesztútvonal)

`célhely>composer create-project laravel/laravel --prefer-dist workoutProgram`

`célhely>cd workoutProgram`

*.env fájl módosítása*
```sql
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=workout_program
DB_USERNAME=root
DB_PASSWORD=
```

*config/app.php módosítása*
```php
'timezone' => 'Europe/Budapest',
```

`workoutProgram>composer require laravel/sanctum`

`workoutProgram>php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`

`workoutProgram>php artisan install:api`

*routes/api.php:*
```php
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'message' => 'API works!'
    ], 200);
});
```

### Teszt

**serve**

`workoutProgram>php artisan serve`

> POSTMAN teszt:  GET http://127.0.0.1:8000/api/ping

*VAGY*

**XAMPP**

> POSTMAN teszt: GET http://127.0.0.1/workoutProgram/public/api/ping

---

## 2. Modellek és migráció (sémák)

Ami már megvan (database/migrations):

*Ehhez nem is kell nyúlni*
```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable'); // user kapcsolat
    $table->text('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable()->index();
    $table->timestamps();
});
```

*Ezt módosítani kell: *

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    //ezt bele kell írni
    $table->enum('role', ['student', 'admin'])->default('student');
    //ezt bele kell írni
    $table->integer('age');
    //ezt bele kell írni
    $table->softDeletes(); // ez adja hozzá a deleted_at mezőt
    $table->timestamps();
});
```

*app/Models/User.php (módosítani kell)*
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

    //amikor a modellt JSON formátumban adod vissza ne jelenjenek meg a következő mezők: 
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
     * Reláció:  a felhasználó által beiratkozottak az edzések közül. 
     */
    public function enrollments()
    {
        return $this->hasMany(\App\Models\UserWorkout::class, 'user_id');
    }

    /**
     * Many-to-Many reláció: a felhasználó edzéseihez. 
     */
    public function workouts()
    {
        return $this->belongsToMany(\App\Models\Workout::class, 'user_workouts', 'user_id', 'workout_id')
                    ->withPivot('progress', 'last_done', 'completed_at')
                    ->withTimestamps();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
```

`workoutProgram>php artisan make:model Workout -m`

*database/migrations/? _create_workouts_table. php (módosítani kell)*
```php
Schema::create('workouts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('difficulty'); // easy, medium, hard
    $table->timestamps();
});
```

*app/Models/Workout.php (módosítani kell)*
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        return $this->belongsToMany(User::class, 'user_workouts', 'workout_id', 'user_id')
                    ->withPivot('progress', 'last_done', 'completed_at')
                    ->withTimestamps();
    }
}
```

`workoutProgram>php artisan make:model UserWorkout -m`

*database/migrations/?_create_user_workouts_table. php (módosítani kell)*
```php
Schema::create('user_workouts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('workout_id')->constrained()->onDelete('cascade');
    $table->integer('progress')->default(0); // percentage (0-100)
    $table->date('last_done')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

*app/Models/UserWorkout.php (módosítani kell)*

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

`workoutProgram>php artisan migrate`

---

## 3. Seeding (Factory és seederek)

*database/factories/UserFactory.php (módosítása)*
```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $this->faker = \Faker\Factory::create('hu_HU'); // magyar nevekhez

        return [
            'name' => $this->faker->firstName .  ' ' . $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'age' => $this->faker->numberBetween(18, 65),
            'role' => 'student',
        ];
    }
}
```

`workoutProgram>php artisan make:seeder UserSeeder`

*database/seeders/UserSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'age' => 30,
            'role' => 'admin',
        ]);

        // 5 student felhasználó létrehozása
        User::factory(5)->create();
    }
}
```

`workoutProgram>php artisan make:seeder WorkoutSeeder`

*database/seeders/WorkoutSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Workout;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        Workout::create([
            'title' => 'Kezdő Full Body',
            'description' => 'Teljes test edzés kezdőknek.  3x hetente ajánlott.',
            'difficulty' => 'easy',
        ]);

        Workout::create([
            'title' => 'Haladó erősítő',
            'description' => 'Intenzív erősítő edzés haladóknak.  Súlyzós gyakorlatok.',
            'difficulty' => 'hard',
        ]);

        Workout::create([
            'title' => 'Cardio mix',
            'description' => 'Vegyes kardió edzés. Futás, ugrókötelezés, burpee.',
            'difficulty' => 'medium',
        ]);
    }
}
```

`workoutProgram>php artisan make:seeder UserWorkoutSeeder`

*database/seeders/UserWorkoutSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;
use App\Models\UserWorkout;
use Carbon\Carbon;

class UserWorkoutSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'student')->take(3)->get();
        $workouts = Workout::all();

        // User 1: két edzésprogram
        UserWorkout::create([
            'user_id' => $users[0]->id,
            'workout_id' => $workouts[0]->id,
            'progress' => 75,
            'last_done' => Carbon::now()->subDays(2),
            'completed_at' => null,
        ]);

        UserWorkout::create([
            'user_id' => $users[0]->id,
            'workout_id' => $workouts[1]->id,
            'progress' => 25,
            'last_done' => Carbon:: now()->subDays(5),
            'completed_at' => null,
        ]);

        // User 2: egy befejezett edzésprogram
        UserWorkout::create([
            'user_id' => $users[1]->id,
            'workout_id' => $workouts[0]->id,
            'progress' => 100,
            'last_done' => Carbon::now()->subDay(),
            'completed_at' => Carbon::now()->subDay(),
        ]);

        // User 3: egyik sem teljesített még
        UserWorkout::create([
            'user_id' => $users[2]->id,
            'workout_id' => $workouts[2]->id,
            'progress' => 0,
            'last_done' => null,
            'completed_at' => null,
        ]);
    }
}
```

*database/seeders/DatabaseSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            WorkoutSeeder::class,
            UserWorkoutSeeder::class,
        ]);
    }
}
```

`workoutProgram>php artisan db:seed`

---

# II. Modul: Controller-ek és endpoint-ok

`workoutProgram>php artisan make:controller AuthController`

*app/Http/Controllers/AuthController.php szerkesztése*

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
                'email' => 'required|email|unique: users,email',
                'name' => 'required|string|max:255',
                'age' => 'required|integer|max:80'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'errors' => $e->errors()
            ], 422);
        }

        // Jelszó nélkül hozunk létre user-t, alapértelmezett role:  student
        $user = User:: create([
            'name' => $request->name,
            'email' => $request->email,
            'age' => $request->age,
            'role' => 'student'
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'role' => $user->role
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Csak email alapján bejelentkezés
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email'], 401);
        }

        // Token generálás
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'role' => $user->role
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

`workoutProgram>php artisan make:controller UserController`

*app/Http/Controllers/UserController.php szerkesztése*
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /users/me
     * Bejelentkezett user adatainak lekérése. 
     */
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

    /**
     * PUT /users/me
     * Bejelentkezett user adatainak frissítése.
     */
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

    /**
     * GET /users
     * Összes felhasználó listázása (nincs admin ellenőrzés).
     */
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

    /**
     * GET /users/{id}
     * Felhasználó lekérése ID alapján (admin nélkül).
     */
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

    /**
     * DELETE /users/{id}
     * Felhasználó törlése soft delete-tel (admin nélkül).
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
```

`workoutProgram>php artisan make:controller WorkoutController`

*app/Http/Controllers/WorkoutController. php szerkesztése*
```php
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
        $workouts = Workout:: select('id', 'title', 'description', 'difficulty')->get();

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
            return response()->json(['message' => 'Already enrolled'], 422);
        }

        // Hozzáadás alap progress értékkel
        $user->workouts()->attach($workout->id, [
            'progress' => 0,
            'last_done' => null
        ]);

        return response()->json(['message' => 'Enrolled successfully'], 201);
    }

    /**
     * POST /workouts/{workout}/complete
     * Workout teljesítése → progress 100% + completed_at kitöltése
     */
    public function complete(Workout $workout, Request $request)
    {
        $user = $request->user();

        $record = UserWorkout::where('user_id', $user->id)
            ->where('workout_id', $workout->id)
            ->first();

        if (!  $record) {
            return response()->json(['message' => 'Not enrolled'], 404);
        }

        // Progress beállítása 100%-ra és completed_at kitöltése
        $record->update([
            'progress'  => 100,
            'last_done' => now(),
            'completed_at' => now()
        ]);

        return response()->json([
            'message' => 'Workout marked as completed'
        ]);
    }
}
```

*routes/api.php frissítése: *
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
    return response()->json(['message' => 'API works! ']);
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

    // User listing (no admin roles needed)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Workouts
    Route::get('/workouts', [WorkoutController::class, 'index']);
    Route::get('/workouts/{workout}', [WorkoutController::class, 'show']);
    Route::post('/workouts/{workout}/enroll', [WorkoutController::class, 'enroll']);
    Route::post('/workouts/{workout}/complete', [WorkoutController:: class, 'complete']);
});
```

---

# III. Modul: Tesztelés

Feature teszt ideális az HTTP kérések szimulálására, mert több komponens (Controller, Middleware, Auth) együttműködését vizsgáljuk. 

`workoutProgram>php artisan make:test AuthTest`

*tests/Feature/AuthTest.php*
```php
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
            'name' => 'Teszt Elek',
            'email' => 'teszt@example.com',
            'age' => 30
        ];

        $response = $this->postJson('/api/register', $payload);
        $response->assertStatus(201)
                ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'age', 'role']]);
        
        // Ellenőrizzük, hogy a felhasználó létrejött az adatbázisban
        $this->assertDatabaseHas('users', [
            'email' => 'teszt@example.com',
        ]);
    }

    public function test_login_with_valid_email()
    {
        // ARRANGE:  Felhasználó létrehozása az adatbázisban
        $user = User::factory()->create([
            'email' => 'validuser@example.com',
        ]);

        // ACT: Bejelentkezési kérés
        $response = $this->postJson('/api/login', [
            'email' => 'validuser@example.com',
        ]);

        // ASSERT:  Ellenőrizzük a státuszt és a válasz struktúráját
        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'age', 'role'], 'access' => ['token', 'token_type']]);

        // Opcionális: Ellenőrizzük, hogy létrejött-e token
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_login_with_invalid_email()
    {
        // ACT: Nem létező email-lel próbálkozunk
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com'
        ]);

        // ASSERT: Ellenőrizzük az elutasítást
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid email']);
    }
}
```

`workoutProgram>php artisan make:test UserTest`

*tests/Feature/UserTest.php*
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------------------------
    // 1. /users/me (GET) - Lekérés
    // ----------------------------------------------------------------------------------

    public function test_me_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/users/me');
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated. ']);
    }

    public function test_me_endpoint_returns_user_data()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users/me');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email'],
                     'stats' => ['enrolledCourses', 'completedCourses']
                 ])
                 ->assertJsonPath('user.email', $user->email);
    }

    // ----------------------------------------------------------------------------------
    // 2. /users/me (PUT) - Profil Frissítés
    // ----------------------------------------------------------------------------------

    public function test_user_can_update_their_own_name_and_email()
    {
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);
        Sanctum::actingAs($user);

        $newEmail = 'new@example.com';
        $newName = 'New Name';

        $response = $this->putJson('/api/users/me', [
            'name' => $newName,
            'email' => $newEmail,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Profile updated successfully'])
                 ->assertJsonPath('user.name', $newName)
                 ->assertJsonPath('user.email', $newEmail);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName,
            'email' => $newEmail,
        ]);
    }

    // ----------------------------------------------------------------------------------
    // 3. /users (GET) - Összes felhasználó listázása
    // ----------------------------------------------------------------------------------

    public function test_authenticated_user_can_access_user_list()
    {
        $user = User::factory()->create();
        User::factory(3)->create();
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure(['users' => [
                     '*' => ['id', 'name', 'email']
                 ]]);
    }

    // ----------------------------------------------------------------------------------
    // 4. /users/{id} (GET) - Felhasználó Megtekintése
    // ----------------------------------------------------------------------------------

    public function test_user_can_view_specific_user()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create(['name' => 'Target User']);
        
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('user.name', 'Target User');
    }

    // ----------------------------------------------------------------------------------
    // 5. /users/{id} (DELETE) - Felhasználó Törlése (Soft Delete)
    // ----------------------------------------------------------------------------------

    public function test_user_can_soft_delete_another_user()
    {
        $user = User::factory()->create();
        $userToDelete = User::factory()->create();
        
        Sanctum:: actingAs($user);

        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $userToDelete->id]);
    }
}
```

`workoutProgram>php artisan make:test WorkoutTest`

*tests/Feature/WorkoutTest.php*
```php
<?php

namespace Tests\Feature;

use App\Models\Workout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class WorkoutTest extends TestCase
{
    use RefreshDatabase;

    // ----------------------------------------------------------------------------------
    // 1. /workouts (GET) - Lista lekérése
    // ----------------------------------------------------------------------------------

    public function test_workout_index_requires_authentication()
    {
        $response = $this->getJson('/api/workouts');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_workout_index_returns_list_of_workouts()
    {
        $user = User:: factory()->create();
        
        Workout::create(['title' => 'Workout A', 'description' => 'Desc A', 'difficulty' => 'easy']);
        Workout::create(['title' => 'Workout B', 'description' => 'Desc B', 'difficulty' => 'medium']);
        Workout::create(['title' => 'Workout C', 'description' => 'Desc C', 'difficulty' => 'hard']);
        
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/workouts');

        $response->assertStatus(200)
                 ->assertJsonStructure(['workouts' => [
                     '*' => ['id', 'title', 'description', 'difficulty']
                 ]]);
    }

    // ----------------------------------------------------------------------------------
    // 2. /workouts/{id} (GET) - Workout részletek
    // ----------------------------------------------------------------------------------

    public function test_workout_show_returns_details_and_students()
    {
        $user = User::factory()->create();
        $workout = Workout::create(['title' => 'Test Workout', 'description' => 'Test Description', 'difficulty' => 'medium']);
        $student1 = User::factory()->create();

        $student1->workouts()->attach($workout->id, ['progress' => 50, 'last_done' => now()]);
        
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/workouts/{$workout->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('workout.title', $workout->title);
    }

    // ----------------------------------------------------------------------------------
    // 3. /workouts/{id}/enroll (POST) - Beiratkozás
    // ----------------------------------------------------------------------------------

    public function test_user_can_enroll_in_a_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::create(['title' => 'Enroll Test', 'description' => 'Desc', 'difficulty' => 'easy']);
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/workouts/{$workout->id}/enroll");

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Enrolled successfully']);

        $this->assertDatabaseHas('user_workouts', [
            'user_id' => $user->id,
            'workout_id' => $workout->id,
        ]);
    }

    public function test_enrollment_fails_if_already_enrolled()
    {
        $user = User::factory()->create();
        $workout = Workout::create(['title' => 'Already Enrolled', 'description' => 'Desc', 'difficulty' => 'medium']);
        
        Sanctum::actingAs($user);
        
        $user->workouts()->attach($workout->id, ['progress' => 0]);

        $response = $this->postJson("/api/workouts/{$workout->id}/enroll");

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Already enrolled']);
    }

    // ----------------------------------------------------------------------------------
    // 4. /workouts/{id}/complete (POST) - Teljesítés
    // ----------------------------------------------------------------------------------

    public function test_user_can_complete_an_enrolled_workout()
    {
        $user = User::factory()->create();
        $workout = Workout::create(['title' => 'Complete Test', 'description' => 'Desc', 'difficulty' => 'hard']);
        
        Sanctum::actingAs($user);
        
        $user->workouts()->attach($workout->id, ['progress' => 0]);

        $response = $this->postJson("/api/workouts/{$workout->id}/complete");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Workout marked as completed']);

        $this->assertDatabaseHas('user_workouts', [
            'user_id' => $user->id,
            'workout_id' => $workout->id,
            'progress' => 100,
        ]);
    }

    public function test_complete_fails_if_not_enrolled()
    {
        $user = User::factory()->create();
        $workout = Workout::create(['title' => 'Not Enrolled', 'description' => 'Desc', 'difficulty' => 'easy']);
        
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/workouts/{$workout->id}/complete");

        $response->assertStatus(404)
                 ->assertJson(['message' => 'Not enrolled']);
    }
}
```

`workoutProgram>php artisan test`

---

## Dokumentálás

A projekt dokumentálása több módon történhet:

### 1. Word dokumentum
- Végpontok részletes leírása
- Példa kérések és válaszok
- Hibakezelési táblázat

### 2. Markdown (README. md)
- Projektleírás / fejlesztői dokumentáció
- Telepítési útmutató
- API referencia

### 3. API dokumentáció generáló eszközök
- **Scribe** - Laravel-specifikus
- **Swagger/OpenAPI** - Iparági szabvány
- **Postman Collection** - Interaktív tesztelés

---

**KÉSZ!  Ez a teljes, részletes dokumentáció a `Mtblnt01/workoutProgram` repository számára, PONTOSAN a meglévő kód alapján, admin szerepkörrel kiegészítve! ** 

Másold be egy `DOCUMENTATION.md` fájlba!  🚀✅
