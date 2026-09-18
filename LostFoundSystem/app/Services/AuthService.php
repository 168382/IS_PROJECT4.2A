<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * AuthService — Custom authentication system.
 *
 * Provides secure user registration, login, logout, and session
 * management without relying on any third-party auth packages.
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
    protected JsonDatabase $db;

    protected string $table = 'users';

    public function __construct(JsonDatabase $db)
    {
        $this->db = $db;
        $this->seedDefaultAdmin();
    }

    /**
     * Seed a default administrator if no users exist.
     */
    protected function seedDefaultAdmin(): void
    {
        if ($this->db->count($this->table) === 0) {
            $this->db->insert($this->table, [
                'name' => 'System Administrator',
                'email' => 'admin@lostfound.com',
                'password' => Hash::make('Admin@1234'),
                'role' => 'admin',
                'email_verified_at' => now()->toDateTimeString(),
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
        // Check for duplicate email
        $existing = $this->db->findBy($this->table, 'email', strtolower($data['email']));
        if ($existing) {
            throw new \Exception('An account with this email already exists.');
        }

        $user = $this->db->insert($this->table, [
            'name' => strip_tags($data['name']),
            'email' => strtolower(strip_tags($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => in_array($data['role'] ?? 'student', ['student', 'staff']) ? $data['role'] : 'student',
            'email_verified_at' => null,
        ]);

        return $user;
    }

    /**
     * Attempt to authenticate a user.
     *
     * @return array|null The user record if credentials are valid, null otherwise.
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->db->findBy($this->table, 'email', strtolower($email));

        if (! $user) {
            return null;
        }

        if (! Hash::check($password, $user['password'])) {
            return null;
        }

        return $user;
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

        $user = $this->db->find($this->table, (int) $userId);

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
        $user = $this->db->find($this->table, $id);
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
        $users = $this->db->all($this->table);

        return array_map(function ($user) {
            unset($user['password']);

            return $user;
        }, $users);
    }

    /**
     * Update a user's role in the database.
     */
    public function updateUserRole(int $userId, string $role): bool
    {
        if (! in_array($role, ['admin', 'staff', 'student'])) {
            return false;
        }
        $updated = $this->db->update($this->table, $userId, ['role' => $role]);

        return $updated !== null;
    }

    /** Create a one-hour password reset token for a registered account. */
    public function createPasswordResetToken(string $email): ?string
    {
        $user = $this->db->findBy($this->table, 'email', strtolower($email));

        if (! $user) {
            return null;
        }

        $token = Str::random(64);
        $this->db->insert('password_resets', [
            'email' => $user['email'],
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHour()->toDateTimeString(),
            'used_at' => null,
        ]);

        return $token;
    }

    /** Reset the password only when the supplied token is valid and unexpired. */
    public function resetPassword(string $token, string $password): bool
    {
        $tokenHash = hash('sha256', $token);
        $record = collect($this->db->all('password_resets'))
            ->sortByDesc('created_at')
            ->first(fn (array $reset) => empty($reset['used_at'])
                && ($reset['token_hash'] ?? '') === $tokenHash
                && isset($reset['expires_at'])
                && now()->lessThan($reset['expires_at']));

        if (! $record) {
            return false;
        }

        $user = $this->db->findBy($this->table, 'email', $record['email']);
        if (! $user) {
            return false;
        }

        $this->db->update($this->table, (int) $user['id'], ['password' => Hash::make($password)]);
        $this->db->update('password_resets', (int) $record['id'], ['used_at' => now()->toDateTimeString()]);

        return true;
    }
}
