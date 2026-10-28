<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'អ៊ីមែល ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវ។']);
        }

        $user = Auth::user();

        if ($user->isPending()) {
            return $this->deny($request, 'គណនីរបស់អ្នកកំពុងរង់ចាំ Admin អនុម័ត។ សូមព្យាយាមម្តងទៀតក្រោយពេលត្រូវបានអនុម័ត។');
        }

        if (!$user->isActive()) {
            return $this->deny($request, 'គណនីរបស់អ្នកត្រូវបានផ្អាក។ សូមទាក់ទង Admin។');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function deny(Request $request, string $message)
    {
        Auth::logout();
        $request->session()->regenerateToken();

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }
}