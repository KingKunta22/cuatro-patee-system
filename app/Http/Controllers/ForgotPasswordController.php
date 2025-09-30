<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendCode(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('name', $validated['username'])
            ->where('email', $validated['email'])
            ->first();

        if (!$user) {
            return redirect('/')->withErrors(['forgot' => 'No user found with that username and email.'])->withInput();
        }

        // Generate a 6-digit code
        $token = (string) random_int(100000, 999999);

        // Store/refresh in password_reset_tokens (Laravel table present)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );

        // Email the code (simple text email)
        Mail::raw("Your Cuatro Patee password reset code is: {$token}\n\nThis code expires in 60 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your Password Reset Code');
        });

        return redirect('/')->with('forgot_success', 'A reset code was sent to your email.');
    }

    public function validateCode(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('name', $validated['username'])
            ->where('email', $validated['email'])
            ->first();

        if (!$user) {
            return redirect('/')->withErrors(['code_validation' => 'Invalid user information.'])->withInput();
        }

        // Check if code exists and is valid
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('created_at', '>', now()->subHours(1)) // Code expires in 1 hour
            ->first();

        if (!$resetRecord) {
            return redirect('/')->withErrors(['code_validation' => 'Code has expired or does not exist.'])->withInput();
        }

        // Verify the code
        if (!hash_equals($resetRecord->token, hash('sha256', $validated['code']))) {
            return redirect('/')->withErrors(['code_validation' => 'Invalid code. Please try again.'])->withInput();
        }

        // Store validated user in session for password reset
        session([
            'reset_user_id' => $user->id,
            'reset_email' => $user->email,
            'code_validated' => true
        ]);

        return redirect('/')->with('code_success', 'Code validated successfully! You can now reset your password.');
    }

    public function resetPassword(Request $request)
    {
        // Check if code was validated
        if (!session('code_validated') || !session('reset_user_id')) {
            return redirect('/')->withErrors(['forgot' => 'Please validate your code first.']);
        }

        // Manual validation
        $request->validate([
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string',
        ]);

        // Check if passwords match
        if ($request->new_password !== $request->new_password_confirmation) {
            return redirect('/')->withErrors(['forgot' => 'Passwords do not match. Please try again.'])->withInput();
        }

        $user = User::find(session('reset_user_id'));
        
        if (!$user) {
            return redirect('/')->withErrors(['forgot' => 'User not found.']);
        }

        // Update password
        $user->update([
            'password' => bcrypt($request->new_password)
        ]);

        // Clear the password reset token
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->delete();

        // Clear session
        session()->forget(['reset_user_id', 'reset_email', 'code_validated']);

        return redirect('/')->with('reset_success', 'Password reset successfully! You can now login with your new password.');
    }
}


