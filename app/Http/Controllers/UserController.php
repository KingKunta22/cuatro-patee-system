<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // This public function gets the inputted username and password
    // checks the database to see if it matches then proceeds to /main
    public function login(Request $request){
        $inputtedFields = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        
        // If it matches then starts a session and redirects to the main page
        if(Auth::attempt(['name' => $inputtedFields['username'], 'password' => $inputtedFields['password']])) {
            $request->session()->regenerate();
            return redirect('/main');
        }
        // If incorrect, shows an error but keeps the inputted values except the password
        return back()->withErrors([
            'login' => 'Invalid Credentials',
        ])->withInput();
    }

    // Removes the session and logs out the user
    public function logout(){
        Auth::logout();
        return redirect('/');
    }
}
