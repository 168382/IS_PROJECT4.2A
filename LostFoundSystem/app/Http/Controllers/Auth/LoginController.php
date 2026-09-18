<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request)
    {
        // If already logged in, redirect to dashboard
        if ($this->auth->check($request)) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle a login request with custom authentication.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        // Rate limiting: check for too many attempts
        $key = 'login_attempts_' . md5($request->ip());
        $attempts = (int) cache()->get($key, 0);

        if ($attempts >= 5) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Too many login attempts. Please try again in 1 minute.']);
        }

        // Attempt authentication
        $user = $this->auth->attempt(
            $request->input('email'),
            $request->input('password')
        );

        if (!$user) {
            // Increment failed attempts
            cache()->put($key, $attempts + 1, now()->addMinute());

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Invalid email or password.']);
        }

        // Clear failed attempts on success
        cache()->forget($key);

        // Log the user in
        $this->auth->login($request, $user);

        return redirect('/dashboard')->with('success', 'Welcome back, ' . $user['name'] . '!');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        $this->auth->logout($request);

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }
}
