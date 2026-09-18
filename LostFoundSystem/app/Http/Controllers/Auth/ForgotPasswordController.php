<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function __construct(protected AuthService $auth) {}

    /**
     * Show the forgot password form.
     */
    public function showForm()
    {
        return view('auth.forgot_password');
    }

    /**
     * Handle the forgot password request.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $token = $this->auth->createPasswordResetToken($request->string('email')->toString());

        if ($token) {
            $link = route('password.reset', ['token' => $token]);

            try {
                Mail::raw("Use this link to reset your Lost & Found account password: {$link}\n\nThis link expires in one hour.", function ($mail) use ($request) {
                    $mail->to($request->input('email'))->subject('Reset your Lost & Found password');
                });
            } catch (\Throwable) {
                // Return the same response whether or not an account exists.
            }
        }

        return back()->with('status', 'If that email exists in our system, a password reset link has been sent.');
    }

    public function showResetForm(string $token)
    {
        return view('auth.reset_password', compact('token'));
    }

    public function resetPassword(Request $request, string $token)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! $this->auth->resetPassword($token, $validated['password'])) {
            return back()->withErrors(['password' => 'This password reset link is invalid or has expired.']);
        }

        return redirect()->route('login')->with('success', 'Your password has been reset. You can now sign in.');
    }
}
