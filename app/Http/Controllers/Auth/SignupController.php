<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SignupController extends Controller
{
    /**
     * Show the signup form page.
     */
    public function showSignupForm()
    {
        return view('auth.signup');
    }

    /**
     * Handle registration of a new user.
     */
    public function register(Request $request)
    {
        // 1. Check form inputs validation parameters
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // 2. Insert new user row record into the database
        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Encrypt password securely
            'role'     => 'customer', // Default new signups to customer role type
        ]);

        // 3. Send user straight to login screen layout with success text
        return redirect()->route('login')->with('success', 'Account created successfully! Please login.');
    }
}
