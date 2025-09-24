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
            return back()->withErrors(['forgot' => 'No user found with that username and email.'])->withInput();
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

        return back()->with('forgot_success', 'A reset code was sent to your email.');
    }
}


