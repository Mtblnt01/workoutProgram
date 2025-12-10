# Workout Program REST API megvalósítása Laravel környezetben

**base_url:** `http://127.0.0.1/workoutProgram/public/api` vagy `http://127.0.0.1:8000/api`

Az API-t olyan funkciókkal kell ellátni, amelyek lehetővé teszik annak nyilvános elérhetőségét.  Ennek a backendnek a fő célja, hogy kiszolgálja a frontendet, amelyet a felhasználók edzéstervek követésére és edzésnapló vezetésére használnak.

**Funkciók:**
- Authentikáció (login, token kezelés).
- Felhasználó csatlakozhat egy edzésprogramhoz.
- Edzésen belül a練習gyakorlatok teljesítését jelöljük.
- A teszteléshez készíts
  - 1 admin (admin / admin)
  - 9 user (jelszó:  Jelszo_2025)
  - 3 releváns edzésprogram (pl.  Kezdő, Haladó, Erőnléti)
  - két véletlen user programjai (köztük 1-1 befejezett edzés)

Az adatbázis neve: `workout_program.`

## Végpontok: 
A `Content-Type` és az `Accept` headerkulcsok mindig `application/json` formátumúak legyenek. 

Érvénytelen vagy hiányzó token esetén a backendnek `401 Unauthorized` választ kell visszaadnia: 
```json
Response:  401 Unauthorized
{
  "message": "Invalid token"
}
```

### Nem védett végpontok:
- **GET** `/ping` - teszteléshez
- **POST** `/register` - regisztrációhoz
- **POST** `/login` - belépéshez

### Hibák:
- 400 Bad Request:  A kérés hibás formátumú.  Ezt a hibát akkor kell visszaadni, ha a kérés hibásan van formázva, vagy ha hiányoznak a szükséges mezők.
- 401 Unauthorized: A felhasználó nem jogosult a kérés végrehajtására. Ezt a hibát akkor kell visszaadni, ha az érvénytelen a token. 
- 403 Forbidden: A felhasználó nem jogosult a kérés végrehajtására. Ezt a hibát akkor kell visszaadni, ha nem megfelelő szerepkörrel rendelkezik. 
- 404 Not Found: A kért erőforrás nem található. Ezt a hibát akkor kell visszaadni, ha a kért edzésprogram, gyakorlat vagy bejegyzés nem található. 
- 409 Conflict:  Konfliktus az erőforrás állapotával. Például már csatlakozott programhoz vagy már teljesített gyakorlat.

---

## Felhasználókezelés


**POST** `/register`

Új felhasználó regisztrálása.  Az új felhasználók alapértelmezetten `user` szerepkörrel rendelkeznek.  Az e-mail címnek egyedinek kell lennie. 

Kérés Törzse:
```JSON
{
    "name": "Kiss János",
    "email": "janos@example.hu",
    "password" : "Jelszo_2025",
    "password_confirmation" : "Jelszo_2025"
}
```
Válasz (sikeres regisztráció esetén): `201 Created`
```JSON
{
    "message": "User created successfully",
    "user": {
        "id": 13,
        "name": "Kiss János",
        "email": "janos@example.hu",
        "role": "user"
    }
}
```

Automatikus válasz felüldefiniálása (ha az e-mail cím már foglalt): `422 Unprocessable Entity`
```JSON
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

Bejelentkezés e-mail címmel és jelszóval. 

Kérés Törzse:
```JSON
{
  "email": "janos@example.hu",
  "password":  "Jelszo_2025"
}
```
Válasz (sikeres bejelentkezés esetén): `200 OK`
```JSON
{
    "message": "Login successful",
    "user": {
        "id":  13,
        "name":  "Kiss János",
        "email": "janos@example.hu",
        "role": "user"
    },
    "access":  {
        "token": "2|7Fbr79b5zn8RxMfOqfdzZ31SnGWvgDidjahbdRfL2a98cfd8",
        "token_type": "Bearer"
    }
}
```
Válasz (sikertelen bejelentkezés esetén): 401 Unauthorized
```JSON
{
  "message": "Invalid email or password"
}
```

---
> Az innen következő végpontok autentikáltak, tehát a kérés headerjében meg kell adni a tokent is

> Authorization: "Bearer 2|7Fbr79b5zn8RxMfOqfdzZ31SnGWvgDidjahbdRfL2a98cfd8"                     


**POST** `/logout`

A jelenlegi autentikált felhasználó kijelentkeztetése, a felhasználó tokenjének törlése.  Ha a token érvénytelen, a fent meghatározott általános `401 Unauthorized` hibát kell visszaadnia. 

Válasz (sikeres kijelentkezés esetén): `200 OK`
```JSON
{
  "message": "Logout successful"
}
```
---
**GET** `/users/me`

Saját felhasználói profil, statisztikák lekérése.

Válasz:  `200 OK`
```JSON
{
    "user": {
        "id": 1,
        "name": "admin",
        "email": "admin@example.com",
        "role": "admin"
    },
    "stats": {
        "activePrograms": 2,
        "completedWorkouts": 15
    }
}
```
---
**PUT** `/users/me`

Saját felhasználói adatok frissítése.  Az aktuális felhasználó módosíthatja a nevét, e-mail címét és/vagy jelszavát.

Kérés törzse:
```JSON
{
  "name": "Új Név",
  "email": "ujemail@example.com",
  "password": "ÚjJelszo_2025",
  "password_confirmation": "ÚjJelszo_2025"
}
```
Válasz (sikeres frissítés, `200 OK`):
```JSON
{
  "message": "Profile updated successfully",
  "user":  {
    "id": 5,
    "name": "Új Név",
    "email": "ujemail@example. com",
    "role": "user"
  }
}
```
*Hibák: *
`422 Unprocessable Entity` – érvénytelen vagy hiányzó mezők, pl. nem egyezik a password_confirmation, vagy az e-mail már foglalt

`401 Unauthorized` – ha a token érvénytelen vagy hiányzik

---
**GET** `/users`

A felhasználói profilok, statisztikák lekérése az admin számára.

Válasz: `200 OK`
```JSON
{
    "data": [
        {
            "user": {
                "id": 1,
                "name": "admin",
                "email": "admin@example.com",
                "role": "admin"
            },
            "stats": {
                "activePrograms": 2,
                "completedWorkouts": 15
            }
        },
        {
            "user": {
                "id": 2,
                "name": "Kovács Anna",
                "email": "anna@example.com",
                "role": "user"
            },
            "stats": {
                "activePrograms": 1,
                "completedWorkouts":  8
            }
        }
    ]
}
```
Ha nem admin próbálja elérni a végpontot: 

Válasz: `403 Forbidden`
```JSON
{
  "message": "Admin access required"
}
```
---
**GET** `/users/:id`

A felhasználói profil, statisztikák lekérése az admin számára.

Válasz: `200 OK`
```JSON
{
  "user": {
    "id": 5,
    "name": "Nagy Péter",
    "email": "peter@example.com",
    "role": "user"
  },
  "stats": {
    "activePrograms": 3,
    "completedWorkouts": 22
  }
}
```
Ha nem admin próbálja elérni a végpontot: 

Válasz: `403 Forbidden`
```JSON
{
  "message": "Admin access required"
}
```
Ha törölt (softdeleted) felhasználót próbáltunk megnézni:

Válasz: `404 Not Found`
```JSON
{
  "message": "User not found"
}
```
---
**DELETE** `/users/:id`

Egy felhasználó törlése (Soft Delete) az admin számára.

Ha a felhasználó már törlésre került, vagy nem létezik, a megfelelő hibaüzenetet adja vissza.

Válasz (sikeres törlés esetén): `200 OK`
```JSON
{
  "message": "User deleted successfully"
}
```
Válasz (ha a felhasználó nem található): `404 Not Found`
```JSON
{
  "message": "User not found"
}
```
Válasz (ha a token érvénytelen vagy hiányzik): `401 Unauthorized`
```JSON
{
  "message": "Invalid token"
}
```
---
## Edzésprogramok kezelése: 


**GET** `/programs`

Az összes elérhető edzésprogram listájának lekérése.

Válasz: `200 OK`
```JSON
{
  "programs": [
    {
      "title": "Kezdő Full Body",
      "description": "3 napos teljes test edzésprogram kezdőknek."
    },
    {
      "title": "Haladó Split",
      "description": "5 napos split program haladóknak."
    },
    {
      "title": "Erőnléti Training",
      "description": "Funkcionális erőnléti program mindenkinek."
    }
  ]
}
```
---
**GET** `/programs/:id`

Információk lekérése egy adott edzésprogramról.

Válasz: `200 OK`
```JSON
{
    "program": {
        "title": "Kezdő Full Body",
        "description": "3 napos teljes test edzésprogram kezdőknek."
    },
    "participants": [
        {
            "name": "Kovács Anna",
            "email":  "anna@example.com",
            "completed": false
        },
        {
            "name": "Nagy Péter",
            "email": "peter@example.com",
            "completed": true
        }
    ]
}
```
Automatikus válasz (ha a program nem található): `404 Not Found`

---

**POST** `/programs/:id/join`

A jelenlegi felhasználó csatlakozása egy edzésprogramhoz. 

Válasz (sikeres csatlakozás esetén): `200 OK`
```JSON
{
  "message": "Successfully joined program"
}
```
Válasz (ha már csatlakozott): `409 Conflict`
```JSON
{
  "message": "Already joined this program"
}
```
Automatikus válasz (ha a program nem található): `404 Not Found`

---
**PATCH** `/programs/:id/completed`

Jelenlegi felhasználó egy edzésprogramjának befejezettként való megjelölése.

Válasz (sikeres befejezés esetén): `200 OK`
```JSON
{
  "message": "Program completed"
}
```
Válasz (ha nincs csatlakozva): `403 Forbidden`
```JSON
{
  "message": "Not joined in this program"
}
```
Válasz (ha már befejezett): `409 Conflict`
```JSON
{
  "message": "Program already completed"
}
```
---
## Összefoglalva

|HTTP metódus|	Útvonal	             |Jogosultság	| Státuszkódok	                                        | Rövid leírás                                 |
|------------|-----------------------|--------------|-------------------------------------------------------|----------------------------------------------|
|GET	     | /ping	             | Nyilvános	| 200 OK	                                            | API teszteléshez                             |
|POST	     | /register	         | Nyilvános	| 201 Created, 422 Unprocessable Entity	            | Új felhasználó regisztrációja                |
|POST	     | /login	             | Nyilvános	| 200 OK, 401 Unauthorized	                            | Bejelentkezés e-maillel és jelszóval         |
|POST	     | /logout	             | Hitelesített | 200 OK, 401 Unauthorized	                            | Kijelentkezés                                |
|GET	     | /users/me	         | Hitelesített | 200 OK, 401 Unauthorized	                            | Saját profil és statisztikák lekérése        |
|PUT         | /users/me	         | Hitelesített | 200 OK, 422 Unprocessable Entity, 401 Unauthorized    | Saját profil adatainak módosítása            |
|GET	     | /users  	         | Admin	    | 200 OK, 403 Forbidden                               	| Összes felhasználó profiljának lekérése      |
|GET	     | /users/:id	         | Admin	    | 200 OK, 403 Forbidden, 404 Not Found, 401 Unauthorized| Bármely felhasználó profiljának lekérése     |
|DELETE	     | /users/:id	         | Admin	    | 200 OK, 404 Not Found, 401 Unauthorized	            | Felhasználó törlése (Soft Delete)            |
|GET	     | /programs	         | Hitelesített | 200 OK, 401 Unauthorized	                            | Edzésprogramok listázása                     | 
|GET	     | /programs/:id	     | Hitelesített | 200 OK, 404 Not Found, 401 Unauthorized	            | Egy edzésprogram részletei                   |
|POST	     | /programs/:id/join	 | Hitelesített | 200 OK, 409 Conflict, 404 Not Found, 401 Unauthorized	| Csatlakozás edzésprogramhoz                  |
|PATCH	     | /programs/:id/completed| Hitelesített| 200 OK, 403 Forbidden, 409 Conflict, 401 Unauthorized	| Program befejezettként jelölése              |


## Adatbázis terv: 
```
+---------------------+     +---------------------+       +----------------------+        +------------------+
|personal_access_tokens|    |        users        |       |  user_programs       |        |  programs        |
+---------------------+     +---------------------+       +----------------------+        +------------------+
| id (PK)             |   _1| id (PK)             |1__    | id (PK)              |     __1| id (PK)          |
| tokenable_id (FK)   |K_/  | name                |   \__N| user_id (FK)         |    /   | title            |
| tokenable_type      |     | email (unique)      |       | program_id (FK)      |M__/    | description      |
| name                |     | password            |       | joined_at            |        | difficulty_level |
| token (unique)      |     | role (user/admin)   |       | completed_at         |        | created_at       |
| abilities           |     | deleted_at          |       +----------------------+        | updated_at       |
| last_used_at        |     +---------------------+                                       +------------------+
+---------------------+
```


# I.  Modul struktúra kialakítása 




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

`workoutProgram>php artisan install: api`

*api. php: *
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
    $table->string('name');
    $table->string('token', 64)->unique();
    $table->text('abilities')->nullable();
    $table->timestamp('last_used_at')->nullable();
    $table->timestamps();
});
```
*Ezt módosítani kell: *

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    //ezt bele kell írni
    $table->enum('role', ['user', 'admin'])->default('user');
    //ezt bele kell írni
    $table->softDeletes(); // ez adja hozzá a deleted_at mezőt
    $table->timestamps();
});
```

*app/Models/User.php (módosítani kell)*
```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    //amikor a modellt JSON formátumban adod vissza ne jelenjenek meg a következő mezők: 
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function userPrograms()
    {
        return $this->hasMany(UserProgram::class);
    }

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'user_programs')
                    ->withPivot('joined_at', 'completed_at');
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
```


`workoutProgram>php artisan make:model Program -m`

*database/migrations/? _create_programs_table. php (módosítani kell)*
```php
Schema::create('programs', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('difficulty_level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
    $table->timestamps();
});
```

*app/Models/Program.php (módosítani kell)*
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'difficulty_level',
    ];

    public function userPrograms()
    {
        return $this->hasMany(UserProgram:: class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_programs')
                    ->withPivot('joined_at', 'completed_at');
    }
}
```

`workoutProgram>php artisan make:model UserProgram -m`

*database/migrations/?_create_user_programs_table.php (módosítani kell)*
```php
Schema::create('user_programs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    //a user_id mező a users tábla id oszlopára fog hivatkozni
    $table->foreignId('program_id')->constrained()->cascadeOnDelete();
    $table->timestamp('joined_at')->useCurrent();
    $table->timestamp('completed_at')->nullable(); // jelzi, hogy a program befejeződött
});
```

*app/Models/UserProgram.php (módosítani kell)*

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProgram extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'program_id',
        'joined_at',
        'completed_at',
    ];

    protected $dates = [
        'joined_at',
        'completed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
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
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $this->faker = \Faker\Factory::create('hu_HU'); // magyar nevekhez

        return [
            'name' => $this->faker->firstName .  ' ' . $this->faker->lastName, // magyaros teljes név
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash:: make('Jelszo_2025'), // minden user jelszava:  Jelszo_2025
            'role' => 'user',
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
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 admin
        User::create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
        ]);

        // 9 user
        User::factory(9)->create();
    }
}
```

`workoutProgram>php artisan make:seeder ProgramSeeder`

*database/seeders/ProgramSeeder. php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        Program::create([
            'title' => 'Kezdő Full Body',
            'description' => '3 napos teljes test edzésprogram kezdőknek.',
            'difficulty_level' => 'beginner',
        ]);

        Program::create([
            'title' => 'Haladó Split',
            'description' => '5 napos split program haladóknak.',
            'difficulty_level' => 'advanced',
        ]);

        Program::create([
            'title' => 'Erőnléti Training',
            'description' => 'Funkcionális erőnléti program mindenkinek.',
            'difficulty_level' => 'intermediate',
        ]);
    }
}
```

`workoutProgram>php artisan make:seeder UserProgramSeeder`

*database/seeders/UserProgramSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Program;
use App\Models\UserProgram;
use Carbon\Carbon;

class UserProgramSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'user')->take(2)->get();
        $programs = Program::all();

        // User 1: első két program
        UserProgram::create([
            'user_id' => $users[0]->id,
            'program_id' => $programs[0]->id,
            'joined_at' => now(),
            'completed_at' => now(),  // completed
        ]);

        UserProgram::create([
            'user_id' => $users[0]->id,
            'program_id' => $programs[1]->id,
            'joined_at' => now(),
            'completed_at' => null,
        ]);

        // User 2: első két program
        UserProgram::create([
            'user_id' => $users[1]->id,
            'program_id' => $programs[0]->id,
            'joined_at' => now(),
            'completed_at' => now(), // completed
        ]);

        UserProgram::create([
            'user_id' => $users[1]->id,
            'program_id' => $programs[2]->id,
            'joined_at' => now(),
            'completed_at' => null,
        ]);
    }
}
```

*DatabaseSeeder.php (módosítása)*
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProgramSeeder::class,
            UserProgramSeeder::class,
        ]);
    }
}
```

`workoutProgram>php artisan db:seed`

---

# II. Modul Controller-ek és endpoint-ok


`workoutProgram>php artisan make:controller AuthController`

*app\Http\Controllers\AuthController.php szerkesztése*

```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|confirmed|min:8',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Failed to register user',
                'errors' => $e->errors() // visszaadja, mely mezők hibásak
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'access' => [
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete(); //minden token törlése
        //$request->user()->currentAccessToken()->delete(); //aktuális token törlése, más eszközökön marad a bejelentkezés 
        return response()->json(['message' => 'Logout successful']);
    }
}
```
`workoutProgram>php artisan make:controller UserController`

*app\Http\Controllers\UserController.php szerkesztése*
```php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /users/me
     * A bejelentkezett felhasználó adatainak lekérése. 
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'stats' => [
                'activePrograms'  => $user->userPrograms()->count(),
                'completedWorkouts' => $user->userPrograms()->whereNotNull('completed_at')->count(),
            ]
        ], 200);
    }

    /**
     * PUT /users/me
     * A bejelentkezett felhasználó adatainak frissítése.
     */
    public function updateMe(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|confirmed|min:8',
        ]);

        if ($request->name) {
            $user->name = $request->name;
        }
        if ($request->email) {
            $user->email = $request->email;
        }
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * ADMIN ONLY
     * GET /users
     * Összes felhasználó listázása.
     */
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        
        $users = User::all()->map(function ($user) {
        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => [
                'activePrograms'  => $user->userPrograms()->count(),
                'completedWorkouts' => $user->userPrograms()->whereNotNull('completed_at')->count(),
            ]
        ];
    });

    return response()->json([
        'data' => $users
    ]);

    }

    /**
     * ADMIN ONLY
     * GET /users/{id}
     * Felhasználó lekérése ID alapján.
     */
    public function show(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Admin access required'], 403);
        }

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
                'role'  => $user->role,
            ],
            'stats' => [
                'activePrograms'  => $user->userPrograms()->count(),
                'completedWorkouts' => $user->userPrograms()->whereNotNull('completed_at')->count(),
            ]
        ]);
    }

    /**
     * ADMIN ONLY
     * DELETE /users/{id}
     * Soft delete felhasználó.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Admin access required'], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
```

`workoutProgram>php artisan make:controller ProgramController`

*app\Http\Controllers\ProgramController.php szerkesztése*
```php
namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\UserProgram;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        
        $programs = Program::select('title', 'description')->get();

        return response()->json([
            'programs' => $programs
        ]);

    }

    public function show(Program $program)
    {
        // Csak a szükséges mezők a kapcsolt usereknél, valamint a teljesítési státusz
        $participants = $program->users()->select('name', 'email')->withPivot('completed_at')->get()->map(function ($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'completed' => ! is_null($user->pivot->completed_at)
            ];
        });

        return response()->json([
            'program' => [
                'title' => $program->title,
                'description' => $program->description
            ],
            'participants' => $participants
        ]);
    }

    public function join(Program $program, Request $request)
    {
        $user = $request->user();

        if ($user->programs()->where('program_id', $program->id)->exists()) {
            return response()->json(['message' => 'Already joined this program'], 409);
        }

        $user->programs()->attach($program->id, ['joined_at' => now()]);

        return response()->json(['message' => 'Successfully joined program']);
    }

    public function complete(Program $program, Request $request)
    {
        $user = $request->user();
        $userProgram = UserProgram::where('user_id', $user->id)
            ->where('program_id', $program->id)
            ->first();

        if (!  $userProgram) {
            return response()->json(['message' => 'Not joined in this program'], 403);
        }

        if ($userProgram->completed_at) {
            return response()->json(['message' => 'Program already completed'], 409);
        }

        $userProgram->update(['completed_at' => now()]);

        return response()->json(['message' => 'Program completed']);
    }
}
```

*routes\api.php frissítése: *
```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProgramController;

// Public
Route::get('/ping', function () { return response()->json(['message'=>'API works! ']); });
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users/me', [UserController::class, 'me']);
    Route::put('/users/me', [UserController::class, 'updateMe']);

    // Admin
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    Route::get('/programs', [ProgramController::class, 'index']);
    Route::get('/programs/{program}', [ProgramController::class, 'show']);
    Route::post('/programs/{program}/join', [ProgramController::class, 'join']);
    Route::patch('/programs/{program}/completed', [ProgramController::class, 'complete']);
});
```


# III. Modul Tesztelés 

Feature teszt ideális az HTTP kérések szimulálására, mert több komponens (Controller, Middleware, Auth) együttműködését vizsgáljuk. 

`workoutProgram>php artisan make:test AuthTest`

```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            'password' => 'Jelszo_2025',
            'password_confirmation' => 'Jelszo_2025'
        ];

        $response = $this->postJson('/api/register', $payload);
        $response->assertStatus(201)
                ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'role']]);
        
        // Ellenőrizzük, hogy a felhasználó létrejött az adatbázisban
        $this->assertDatabaseHas('users', [
            'email' => 'teszt@example.com',
        ]);
    }

    public function test_login_with_valid_credentials()
    {
        $password = 'Jelszo_2025';
        $user = User::factory()->create([
            'email' => 'validuser@example.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'validuser@example.com',
            'password' => $password,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user' => ['id', 'name', 'email', 'role'], 'access' => ['token', 'token_type']]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => Hash::make('CorrectPassword'), 
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'existing@example. com',
            'password' => 'wrongpass'
        ]);

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Invalid email or password']);
    }
}
```

`workoutProgram>php artisan make:test UserTest`
```php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_endpoint_requires_authentication()
    {
        $response = $this->getJson('/api/users/me');
        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated. ']);
    }

    public function test_me_endpoint_returns_user_data()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        Sanctum::actingAs($user); 

        $response = $this->getJson('/api/users/me');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => ['id', 'name', 'email', 'role'],
                     'stats' => ['activePrograms', 'completedWorkouts']
                 ])
                 ->assertJsonPath('user.email', $user->email);
    }

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
    
    public function test_user_can_update_their_password()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $newPassword = 'New_Secure_Password_2025';

        $response = $this->putJson('/api/users/me', [
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(200);

        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check($newPassword, $updatedUser->password));
    }

    public function test_student_cannot_access_user_list()
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user); 

        $response = $this->getJson('/api/users');

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Admin access required']);
    }

    public function test_admin_can_access_user_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::factory(3)->create(['role' => 'user']);
        
        Sanctum::actingAs($admin); 

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data' => [
                     '*' => [
                         'user' => ['id', 'name', 'email', 'role'],
                         'stats' => ['activePrograms', 'completedWorkouts']
                     ]
                 ]])
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->has('data', 4)
                          ->etc()
                 );
    }

    public function test_admin_can_view_specific_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user', 'name' => 'Target User']);
        
        Sanctum::actingAs($admin); 

        $response = $this->getJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('user.name', 'Target User');
    }

    public function test_user_cannot_view_other_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);
        
        Sanctum::actingAs($user); 

        $response = $this->getJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Admin access required']);
    }

    public function test_admin_can_soft_delete_a_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userToDelete = User::factory()->create();
        
        Sanctum:: actingAs($admin); 

        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $userToDelete->id]);
    }

    public function test_user_cannot_delete_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        $userToDelete = User::factory()->create();
        
        Sanctum::actingAs($user); 

        $response = $this->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Admin access required']);

        $this->assertDatabaseHas('users', ['id' => $userToDelete->id]);
    }
}
```

`workoutProgram>php artisan make:test ProgramTest`

```php
namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum; 
use Illuminate\Testing\Fluent\AssertableJson;

class ProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_index_requires_authentication()
    {
        $response = $this->getJson('/api/programs');

        $response->assertStatus(401)
                 ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_program_index_returns_list_of_programs()
    {
        $user = User::factory()->create();
        
        Program::create(['title' => 'Program A', 'description' => 'Leírás A', 'difficulty_level' => 'beginner']);
        Program::create(['title' => 'Program B', 'description' => 'Leírás B', 'difficulty_level' => 'intermediate']);
        Program::create(['title' => 'Program C', 'description' => 'Leírás C', 'difficulty_level' => 'advanced']);
        
        Sanctum::actingAs($user); 

        $response = $this->getJson('/api/programs');

        $response->assertStatus(200)
                 ->assertJsonStructure(['programs' => [
                     '*' => ['title', 'description']
                 ]])
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->has('programs', 3)
                          ->etc()
                 );
    }

    public function test_program_show_returns_details_and_participants()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $program = Program::create(['title' => 'Részletes Program', 'description' => 'Részletes Leírás', 'difficulty_level' => 'beginner']);
        $participant1 = User::factory()->create();
        $participant2 = User::factory()->create();

        $participant1->programs()->attach($program->id, ['joined_at' => now()]);
        $participant2->programs()->attach($program->id, ['joined_at' => now(), 'completed_at' => now()]);
        
        Sanctum::actingAs($user); 

        $response = $this->getJson("/api/programs/{$program->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('program.title', $program->title)
                 ->assertJson(fn (AssertableJson $json) =>
                     $json->has('participants', 2)
                          ->where('participants.0.completed', false)
                          ->where('participants.1.completed', true)
                          ->etc()
                 );
    }

    public function test_user_can_join_a_program()
    {
        $user = User::factory()->create();
        $program = Program::create(['title' => 'Csatlakozó Program', 'description' => 'Leírás', 'difficulty_level' => 'beginner']);
        Sanctum::actingAs($user); 

        $response = $this->postJson("/api/programs/{$program->id}/join");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Successfully joined program']);

        $this->assertDatabaseHas('user_programs', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'completed_at' => null,
        ]);
    }

    public function test_join_fails_if_already_joined()
    {
        $user = User::factory()->create();
        $program = Program:: create(['title' => 'Már Csatlakozott Program', 'description' => 'Leírás', 'difficulty_level' => 'beginner']);
        Sanctum::actingAs($user); 
        
        $user->programs()->attach($program->id, ['joined_at' => now()]);

        $response = $this->postJson("/api/programs/{$program->id}/join");

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Already joined this program']);
    }

    public function test_user_can_complete_a_joined_program()
    {
        $user = User::factory()->create();
        $program = Program:: create(['title' => 'Teljesíthető Program', 'description' => 'Leírás', 'difficulty_level' => 'beginner']);
        Sanctum::actingAs($user); 
        
        $user->programs()->attach($program->id, ['joined_at' => now()]);

        $response = $this->patchJson("/api/programs/{$program->id}/completed");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Program completed']);

        $this->assertDatabaseMissing('user_programs', [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'completed_at' => null,
        ]);
    }

    public function test_complete_fails_if_not_joined()
    {
        $user = User::factory()->create();
        $program = Program::create(['title' => 'Nem Csatlakozott Program', 'description' => 'Leírás', 'difficulty_level' => 'beginner']);
        Sanctum::actingAs($user); 
        
        $response = $this->patchJson("/api/programs/{$program->id}/completed");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Not joined in this program']);
    }
    
    public function test_complete_fails_if_already_completed()
    {
        $user = User::factory()->create();
        $program = Program:: create(['title' => 'Már Teljesített Program', 'description' => 'Leírás', 'difficulty_level' => 'beginner']);
        Sanctum::actingAs($user); 
        
        $user->programs()->attach($program->id, ['joined_at' => now(), 'completed_at' => now()]);

        $response = $this->patchJson("/api/programs/{$program->id}/completed");

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Program already completed']);
    }
}
```

`workoutProgram>php artisan test`
