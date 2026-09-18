<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    protected AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm(Request $request)
    {
        if ($this->auth->check($request)) {
            return redirect('/dashboard');
        }

        return view('auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:student,staff',
        ]);

        try {
            $user = $this->auth->register($request->only('name', 'email', 'password', 'role'));
        } catch (\Exception $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => $e->getMessage()]);
        }

        // Log the user in automatically
        $this->auth->login($request, $user);

        return redirect('/dashboard')->with('success', 'Welcome, ' . $user['name'] . '! Your account has been created.');
    }
}
