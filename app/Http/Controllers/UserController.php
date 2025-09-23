<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    // Display all users
    public function index()
    {

        // // ADD THIS SECURITY CHECK:
        // if (Auth::user()->role !== 'admin') {
        //     abort(403, 'Unauthorized. Admin access required.');
        // }

        $users = User::all();
        return view('manage-account', compact('users'));
    }

    // Store new user
    public function store(Request $request)
    {

        // // ADD THIS SECURITY CHECK:
        // if (Auth::user()->role !== 'admin') {
        //     abort(403, 'Unauthorized. Admin access required.');
        // }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,staff',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active',
        ]);

        return redirect()->route('manage.account')->with('success', 'User created successfully!');
    }

    // Update user
    public function update(Request $request, User $user)
    {

        // // SECURITY CHECK:
        // if (Auth::user()->role !== 'admin') {
        //     abort(403, 'Unauthorized. Admin access required.');
        // }


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,staff',
            'status' => 'required|in:active,inactive',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('manage.account')->with('success', 'User updated successfully!');
    }

    // Delete user
    public function destroy(User $user)
    {
        // // SECURITY CHECK:
        // if (Auth::user()->role !== 'admin') {
        //     abort(403, 'Unauthorized. Admin access required.');
        // }

        // Prevent users from deleting themselves - using Auth facade
        if ($user->id === Auth::id()) {
            return redirect()->route('manage.account')->with('error', 'You cannot delete your own account!');
        }

        $user->delete();
        return redirect()->route('manage.account')->with('success', 'User deleted successfully!');
    }

    public function login(Request $request)
    {
        $inputtedFields = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if(Auth::attempt(['name' => $inputtedFields['username'], 'password' => $inputtedFields['password']])) {
            $request->session()->regenerate();
            return redirect('/main');
        }

        return back()->withErrors([
            'login' => 'Invalid Credentials',
        ])->withInput();
    }

    // Your existing logout method (keep this)
    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}