# Workout API Documentation

## Table of Contents
- [Overview](#overview)
- [Authentication](#authentication)
- [User Management](#user-management)
- [Program Management](#program-management)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Models and Migrations](#models-and-migrations)
- [Seeding](#seeding)
- [Controllers and Endpoints](#controllers-and-endpoints)
- [Testing](#testing)

## Overview

The Workout API is a RESTful API designed to manage workout programs, exercises, and user progress tracking. Built with modern web technologies, it provides a comprehensive solution for fitness application backends.

### Key Features
- User authentication and authorization
- Workout program management
- Exercise library
- Progress tracking
- RESTful API design
- Comprehensive data validation

### Base URL
```
http://localhost:3000/api/v1
```

---

## Authentication

The API uses token-based authentication to secure endpoints.

### Register a New User

**Endpoint:** `POST /auth/register`

**Request Body:**
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "SecurePassword123!",
  "firstName": "John",
  "lastName": "Doe"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "john@example.com",
      "firstName": "John",
      "lastName": "Doe",
      "createdAt": "2025-12-10T22:27:29Z"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

### Login

**Endpoint:** `POST /auth/login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePassword123!"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "john_doe",
      "email": "john@example.com"
    },
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

### Authentication Headers

For protected endpoints, include the JWT token in the Authorization header:

```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

---

## User Management

### Get Current User Profile

**Endpoint:** `GET /users/me`

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "firstName": "John",
    "lastName": "Doe",
    "createdAt": "2025-12-10T22:27:29Z",
    "updatedAt": "2025-12-10T22:27:29Z"
  }
}
```

### Update User Profile

**Endpoint:** `PUT /users/me`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "firstName": "Johnny",
  "lastName": "Doe",
  "bio": "Fitness enthusiast"
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "firstName": "Johnny",
    "lastName": "Doe",
    "bio": "Fitness enthusiast",
    "updatedAt": "2025-12-10T22:27:29Z"
  }
}
```

### Get User Stats

**Endpoint:** `GET /users/me/stats`

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "totalWorkouts": 45,
    "totalPrograms": 3,
    "currentStreak": 7,
    "longestStreak": 21,
    "lastWorkoutDate": "2025-12-09T18:30:00Z"
  }
}
```

---

## Program Management

### Get All Programs

**Endpoint:** `GET /programs`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10)
- `difficulty` (optional): Filter by difficulty (beginner, intermediate, advanced)
- `category` (optional): Filter by category

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "programs": [
      {
        "id": 1,
        "name": "Beginner Strength Training",
        "description": "A comprehensive program for beginners",
        "difficulty": "beginner",
        "duration": 8,
        "durationUnit": "weeks",
        "category": "strength",
        "createdBy": 1,
        "createdAt": "2025-12-01T10:00:00Z"
      }
    ],
    "pagination": {
      "currentPage": 1,
      "totalPages": 3,
      "totalItems": 25,
      "itemsPerPage": 10
    }
  }
}
```

### Get Program by ID

**Endpoint:** `GET /programs/:id`

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Beginner Strength Training",
    "description": "A comprehensive program for beginners",
    "difficulty": "beginner",
    "duration": 8,
    "durationUnit": "weeks",
    "category": "strength",
    "workouts": [
      {
        "id": 1,
        "name": "Upper Body Day",
        "dayOfWeek": 1,
        "exercises": [
          {
            "id": 1,
            "name": "Bench Press",
            "sets": 3,
            "reps": 10,
            "restTime": 90,
            "notes": "Focus on form"
          }
        ]
      }
    ],
    "createdBy": 1,
    "createdAt": "2025-12-01T10:00:00Z"
  }
}
```

### Create a New Program

**Endpoint:** `POST /programs`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Advanced HIIT Program",
  "description": "High-intensity interval training for experienced athletes",
  "difficulty": "advanced",
  "duration": 6,
  "durationUnit": "weeks",
  "category": "cardio"
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Advanced HIIT Program",
    "description": "High-intensity interval training for experienced athletes",
    "difficulty": "advanced",
    "duration": 6,
    "durationUnit": "weeks",
    "category": "cardio",
    "createdBy": 1,
    "createdAt": "2025-12-10T22:27:29Z"
  }
}
```

### Update a Program

**Endpoint:** `PUT /programs/:id`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Advanced HIIT Program v2",
  "description": "Updated description",
  "duration": 8
}
```

**Response:** `200 OK`
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Advanced HIIT Program v2",
    "description": "Updated description",
    "difficulty": "advanced",
    "duration": 8,
    "durationUnit": "weeks",
    "category": "cardio",
    "updatedAt": "2025-12-10T22:27:29Z"
  }
}
```

### Delete a Program

**Endpoint:** `DELETE /programs/:id`

**Headers:** `Authorization: Bearer {token}`

**Response:** `200 OK`
```json
{
  "success": true,
  "message": "Program deleted successfully"
}
```

### Add Workout to Program

**Endpoint:** `POST /programs/:id/workouts`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Leg Day",
  "dayOfWeek": 3,
  "description": "Lower body focused workout",
  "exercises": [
    {
      "exerciseId": 5,
      "sets": 4,
      "reps": 12,
      "restTime": 120,
      "notes": "Squat deep"
    }
  ]
}
```

**Response:** `201 Created`
```json
{
  "success": true,
  "data": {
    "id": 3,
    "programId": 1,
    "name": "Leg Day",
    "dayOfWeek": 3,
    "description": "Lower body focused workout",
    "createdAt": "2025-12-10T22:27:29Z"
  }
}
```

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(50),
  last_name VARCHAR(50),
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Programs Table
```sql
CREATE TABLE programs (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  difficulty VARCHAR(20) CHECK (difficulty IN ('beginner', 'intermediate', 'advanced')),
  duration INTEGER,
  duration_unit VARCHAR(20) DEFAULT 'weeks',
  category VARCHAR(50),
  created_by INTEGER REFERENCES users(id),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Workouts Table
```sql
CREATE TABLE workouts (
  id SERIAL PRIMARY KEY,
  program_id INTEGER REFERENCES programs(id) ON DELETE CASCADE,
  name VARCHAR(100) NOT NULL,
  day_of_week INTEGER CHECK (day_of_week BETWEEN 0 AND 6),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Exercises Table
```sql
CREATE TABLE exercises (
  id SERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  muscle_group VARCHAR(50),
  equipment VARCHAR(100),
  difficulty VARCHAR(20),
  video_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Workout_Exercises Table
```sql
CREATE TABLE workout_exercises (
  id SERIAL PRIMARY KEY,
  workout_id INTEGER REFERENCES workouts(id) ON DELETE CASCADE,
  exercise_id INTEGER REFERENCES exercises(id),
  sets INTEGER,
  reps INTEGER,
  rest_time INTEGER,
  order_index INTEGER,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### User_Progress Table
```sql
CREATE TABLE user_progress (
  id SERIAL PRIMARY KEY,
  user_id INTEGER REFERENCES users(id),
  workout_exercise_id INTEGER REFERENCES workout_exercises(id),
  weight DECIMAL(5, 2),
  reps_completed INTEGER,
  date_completed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes TEXT
);
```

---

## Installation

### Prerequisites
- Node.js (v16 or higher)
- PostgreSQL (v12 or higher)
- npm or yarn

### Steps

1. **Clone the repository**
```bash
git clone https://github.com/Mtblnt01/workoutProgram.git
cd workoutProgram
```

2. **Install dependencies**
```bash
npm install
```

3. **Set up environment variables**

Create a `.env` file in the root directory:
```env
# Database
DATABASE_URL=postgresql://username:password@localhost:5432/workout_db
DB_HOST=localhost
DB_PORT=5432
DB_NAME=workout_db
DB_USER=your_username
DB_PASSWORD=your_password

# JWT
JWT_SECRET=your_secret_key_here
JWT_EXPIRES_IN=7d

# Server
PORT=3000
NODE_ENV=development
```

4. **Create the database**
```bash
createdb workout_db
```

5. **Run migrations**
```bash
npm run migrate
```

6. **Seed the database (optional)**
```bash
npm run seed
```

7. **Start the server**
```bash
# Development
npm run dev

# Production
npm start
```

---

## Models and Migrations

### Running Migrations

**Create a new migration:**
```bash
npm run migrate:create -- migration_name
```

**Run all pending migrations:**
```bash
npm run migrate:up
```

**Rollback the last migration:**
```bash
npm run migrate:down
```

### Example Migration File

```javascript
// migrations/20251210_create_users_table.js

exports.up = function(knex) {
  return knex.schema.createTable('users', function(table) {
    table.increments('id').primary();
    table.string('username', 50).notNullable().unique();
    table.string('email', 100).notNullable().unique();
    table.string('password_hash', 255).notNullable();
    table.string('first_name', 50);
    table.string('last_name', 50);
    table.text('bio');
    table.timestamps(true, true);
  });
};

exports.down = function(knex) {
  return knex.schema.dropTable('users');
};
```

---

## Seeding

### Running Seeds

**Run all seed files:**
```bash
npm run seed
```

**Create a new seed file:**
```bash
npm run seed:create -- seed_name
```

### Example Seed File

```javascript
// seeds/01_users.js

exports.seed = async function(knex) {
  // Deletes ALL existing entries
  await knex('users').del();
  
  // Inserts seed entries
  await knex('users').insert([
    {
      username: 'admin',
      email: 'admin@workout.com',
      password_hash: '$2b$10$hashedpassword',
      first_name: 'Admin',
      last_name: 'User'
    },
    {
      username: 'john_doe',
      email: 'john@example.com',
      password_hash: '$2b$10$hashedpassword',
      first_name: 'John',
      last_name: 'Doe'
    }
  ]);
};
```

---

## Controllers and Endpoints

### Authentication Controller

**File:** `controllers/authController.js`

```javascript
const register = async (req, res) => {
  // Register new user
};

const login = async (req, res) => {
  // Authenticate user
};

const logout = async (req, res) => {
  // Logout user
};

module.exports = { register, login, logout };
```

### User Controller

**File:** `controllers/userController.js`

```javascript
const getProfile = async (req, res) => {
  // Get user profile
};

const updateProfile = async (req, res) => {
  // Update user profile
};

const getUserStats = async (req, res) => {
  // Get user statistics
};

module.exports = { getProfile, updateProfile, getUserStats };
```

### Program Controller

**File:** `controllers/programController.js`

```javascript
const getAllPrograms = async (req, res) => {
  // Get all programs with pagination
};

const getProgramById = async (req, res) => {
  // Get program by ID with workouts
};

const createProgram = async (req, res) => {
  // Create new program
};

const updateProgram = async (req, res) => {
  // Update existing program
};

const deleteProgram = async (req, res) => {
  // Delete program
};

module.exports = {
  getAllPrograms,
  getProgramById,
  createProgram,
  updateProgram,
  deleteProgram
};
```

### Complete API Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/auth/register` | Register new user | No |
| POST | `/api/v1/auth/login` | Login user | No |
| POST | `/api/v1/auth/logout` | Logout user | Yes |
| GET | `/api/v1/users/me` | Get current user | Yes |
| PUT | `/api/v1/users/me` | Update profile | Yes |
| GET | `/api/v1/users/me/stats` | Get user stats | Yes |
| GET | `/api/v1/programs` | Get all programs | Yes |
| GET | `/api/v1/programs/:id` | Get program by ID | Yes |
| POST | `/api/v1/programs` | Create program | Yes |
| PUT | `/api/v1/programs/:id` | Update program | Yes |
| DELETE | `/api/v1/programs/:id` | Delete program | Yes |
| POST | `/api/v1/programs/:id/workouts` | Add workout to program | Yes |
| GET | `/api/v1/exercises` | Get all exercises | Yes |
| POST | `/api/v1/exercises` | Create exercise | Yes |
| GET | `/api/v1/progress` | Get user progress | Yes |
| POST | `/api/v1/progress` | Log workout progress | Yes |

---

## Testing

### Setup Testing Environment

1. **Install testing dependencies:**
```bash
npm install --save-dev jest supertest
```

2. **Create test database:**
```bash
createdb workout_db_test
```

3. **Update test configuration:**

Create `.env.test` file:
```env
DATABASE_URL=postgresql://username:password@localhost:5432/workout_db_test
NODE_ENV=test
JWT_SECRET=test_secret_key
```

### Running Tests

```bash
# Run all tests
npm test

# Run tests in watch mode
npm run test:watch

# Run tests with coverage
npm run test:coverage
```

### Example Test File

**File:** `tests/auth.test.js`

```javascript
const request = require('supertest');
const app = require('../app');

describe('Authentication', () => {
  describe('POST /api/v1/auth/register', () => {
    it('should register a new user', async () => {
      const response = await request(app)
        .post('/api/v1/auth/register')
        .send({
          username: 'testuser',
          email: 'test@example.com',
          password: 'Password123!',
          firstName: 'Test',
          lastName: 'User'
        });

      expect(response.status).toBe(201);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toHaveProperty('token');
      expect(response.body.data.user).toHaveProperty('id');
    });

    it('should not register user with duplicate email', async () => {
      const response = await request(app)
        .post('/api/v1/auth/register')
        .send({
          username: 'testuser2',
          email: 'test@example.com', // Duplicate email
          password: 'Password123!'
        });

      expect(response.status).toBe(400);
      expect(response.body.success).toBe(false);
    });
  });

  describe('POST /api/v1/auth/login', () => {
    it('should login existing user', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'test@example.com',
          password: 'Password123!'
        });

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toHaveProperty('token');
    });

    it('should not login with wrong password', async () => {
      const response = await request(app)
        .post('/api/v1/auth/login')
        .send({
          email: 'test@example.com',
          password: 'WrongPassword'
        });

      expect(response.status).toBe(401);
      expect(response.body.success).toBe(false);
    });
  });
});
```

### Example Integration Test

**File:** `tests/programs.test.js`

```javascript
const request = require('supertest');
const app = require('../app');

describe('Program Management', () => {
  let authToken;
  let programId;

  beforeAll(async () => {
    // Login and get auth token
    const loginResponse = await request(app)
      .post('/api/v1/auth/login')
      .send({
        email: 'test@example.com',
        password: 'Password123!'
      });

    authToken = loginResponse.body.data.token;
  });

  describe('POST /api/v1/programs', () => {
    it('should create a new program', async () => {
      const response = await request(app)
        .post('/api/v1/programs')
        .set('Authorization', `Bearer ${authToken}`)
        .send({
          name: 'Test Program',
          description: 'A test workout program',
          difficulty: 'intermediate',
          duration: 4,
          durationUnit: 'weeks',
          category: 'strength'
        });

      expect(response.status).toBe(201);
      expect(response.body.success).toBe(true);
      expect(response.body.data).toHaveProperty('id');
      programId = response.body.data.id;
    });
  });

  describe('GET /api/v1/programs/:id', () => {
    it('should get program by ID', async () => {
      const response = await request(app)
        .get(`/api/v1/programs/${programId}`)
        .set('Authorization', `Bearer ${authToken}`);

      expect(response.status).toBe(200);
      expect(response.body.success).toBe(true);
      expect(response.body.data.name).toBe('Test Program');
    });
  });
});
```

---

## Error Handling

All API endpoints return errors in a consistent format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message",
    "details": {}
  }
}
```

### Common Error Codes

| Status Code | Error Code | Description |
|-------------|------------|-------------|
| 400 | BAD_REQUEST | Invalid request parameters |
| 401 | UNAUTHORIZED | Missing or invalid authentication |
| 403 | FORBIDDEN | Insufficient permissions |
| 404 | NOT_FOUND | Resource not found |
| 409 | CONFLICT | Resource conflict (e.g., duplicate) |
| 422 | VALIDATION_ERROR | Request validation failed |
| 500 | INTERNAL_ERROR | Server error |

---

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests:** 100 requests per 15 minutes
- **Unauthenticated requests:** 20 requests per 15 minutes

Rate limit headers are included in all responses:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1702242449
```

---

## API Versioning

The API uses URL versioning. The current version is `v1`:

```
https://api.workout.com/api/v1/...
```

When breaking changes are introduced, a new version will be created while maintaining backward compatibility with previous versions.

---

## Support and Contact

For questions, issues, or feature requests:

- **GitHub Issues:** https://github.com/Mtblnt01/workoutProgram/issues
- **Email:** support@workout.com
- **Documentation:** https://github.com/Mtblnt01/workoutProgram/docs

---

## License

This project is licensed under the MIT License. See LICENSE file for details.

---

**Last Updated:** December 10, 2025
**API Version:** 1.0.0
