<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * AuthService — Custom authentication system.
 *
 * Provides secure user registration, login, logout, and session
 * management without relying on any third-party auth packages.
 *
 * Users are persisted through the App\Models\User Eloquent model,
 * which stores them in the real `users` database table (MySQL/whichever
 * connection is configured via DB_CONNECTION) rather than in a flat file.
 *
 * Security features:
 * - Bcrypt password hashing (12 rounds)
 * - CSRF protection via Laravel middleware
 * - Session regeneration on login/logout to prevent fixation
 * - Rate limiting on login attempts
 * - Input sanitization and validation
 * - Secure session-based authentication
 */
class AuthService
{
    public function __construct()
    {
        $this->seedDefaultAdmin();
    }

    /**
     * Seed a default administrator if no users exist.
     */
    protected function seedDefaultAdmin(): void
    {
        if (User::count() === 0) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@lostfound.com',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }
    }

    /**
     * Register a new user.
     *
     * @throws \Exception if email already exists
     */
    public function register(array $data): array
    {
        $email = strtolower(strip_tags($data['email']));

        // Check for duplicate email
        $existing = User::where('email', $email)->first();
        if ($existing) {
            throw new \Exception('An account with this email already exists.');
        }

        $user = User::create([
            'name' => strip_tags($data['name']),
            'email' => $email,
            'password' => Hash::make($data['password']),
            'role' => in_array($data['role'] ?? 'student', ['student', 'staff']) ? $data['role'] : 'student',
            'email_verified_at' => null,
        ]);

        return $user->toArray();
    }

    /**
     * Attempt to authenticate a user.
     *
     * @return array|null The user record if credentials are valid, null otherwise.
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = User::where('email', strtolower($email))->first();

        if (! $user) {
            return null;
        }

        if (! Hash::check($password, $user->password)) {
            return null;
        }

        return $user->toArray();
    }

    /**
     * Log a user in by storing their ID in the session.
     * Regenerates session ID to prevent session fixation attacks.
     */
    public function login(Request $request, array $user): void
    {
        $request->session()->regenerate();
        $request->session()->put('auth_user_id', $user['id']);
        $request->session()->put('auth_user_role', $user['role']);
        $request->session()->put('auth_login_at', now()->toDateTimeString());
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Get the currently authenticated user, or null.
     */
    public function user(Request $request): ?array
    {
        $userId = $request->session()->get('auth_user_id');

        if (! $userId) {
            return null;
        }

        $user = User::find((int) $userId)?->toArray();

        if ($user) {
            // Never expose password hash to views
            unset($user['password']);
        }

        return $user;
    }

    /**
     * Check if a user is currently logged in.
     */
    public function check(Request $request): bool
    {
        return $request->session()->has('auth_user_id');
    }

    /**
     * Check if the current user has a specific role.
     */
    public function hasRole(Request $request, string $role): bool
    {
        return $request->session()->get('auth_user_role') === $role;
    }

    /**
     * Get a user by ID (without password).
     */
    public function findUser(int $id): ?array
    {
        $user = User::find($id)?->toArray();

        if ($user) {
            unset($user['password']);
        }

        return $user;
    }

    /**
     * Get all users (without passwords).
     */
    public function allUsers(): array
    {
        return User::all()->map(function (User $user) {
            $record = $user->toArray();
            unset($record['password']);

            return $record;
        })->all();
    }

    /**
     * Update a user's role in the database.
     */
    public function updateUserRole(int $userId, string $role): bool
    {
        if (! in_array($role, ['admin', 'staff', 'student'])) {
            return false;
        }

        $user = User::find($userId);
        if (! $user) {
            return false;
        }

        $user->role = $role;

        return $user->save();
    }

    /** Create a one-hour password reset token for a registered account. */
    public function createPasswordResetToken(string $email): ?string
    {
        $user = User::where('email', strtolower($email))->first();

        if (! $user) {
            return null;
        }

        $token = Str::random(64);

        // Only one active reset token per email; a new request invalidates any previous one.
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        return $token;
    }

    /** Reset the password only when the supplied token is valid and unexpired. */
    public function resetPassword(string $token, string $password): bool
    {
        $tokenHash = hash('sha256', $token);

        $record = DB::table('password_reset_tokens')
            ->where('token', $tokenHash)
            ->where('created_at', '>=', now()->subHour())
            ->first();

        if (! $record) {
            return false;
        }

        $user = User::where('email', $record->email)->first();
        if (! $user) {
            return false;
        }

        $user->password = Hash::make($password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $record->email)->delete();

        return true;
    }
}
