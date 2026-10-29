<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'សូមបញ្ចូលអ៊ីមែល។',
            'email.email' => 'អ៊ីមែលមិនត្រឹមត្រូវ។',
            'password.required' => 'សូមបញ្ចូលពាក្យសម្ងាត់។',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            // អ៊ីមែលរកមិនឃើញ → កំហុសលើអ៊ីមែល, បើមិនដូច្នោះ → កំហុសលើពាក្យសម្ងាត់
            $field = User::where('email', $credentials['email'])->exists() ? 'password' : 'email';
            $msg = $field === 'password' ? 'ពាក្យសម្ងាត់មិនត្រឹមត្រូវ។' : 'អ៊ីមែលមិនត្រឹមត្រូវ។';

            return back()
                ->withInput($request->only('email'))
                ->withErrors([$field => $msg]);
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