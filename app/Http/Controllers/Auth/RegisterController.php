<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.signup');
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'name'     => ['required', 'string', 'min:2', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'name.required'      => 'សូមបញ្ចូលឈ្មោះ។',
            'name.min'           => 'ឈ្មោះត្រូវមានយ៉ាងតិច ២ តួអក្សរ។',
            'email.required'     => 'សូមបញ្ចូលអ៊ីមែល។',
            'email.email'        => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។',
            'email.unique'       => 'អ៊ីមែលនេះត្រូវបានគេប្រើរួចហើយ។',
            'phone.regex'        => 'លេខទូរស័ព្ទមិនត្រឹមត្រូវ។',
            'password.required'  => 'សូមបញ្ចូលពាក្យសម្ងាត់។',
            'password.confirmed' => 'ការបញ្ជាក់ពាក្យសម្ងាត់មិនត្រូវគ្នា។',
            'password.min'       => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៨ តួអក្សរ។',
            'password.letters'   => 'ពាក្យសម្ងាត់ត្រូវមានអក្សរយ៉ាងតិចមួយ។',
            'password.numbers'   => 'ពាក្យសម្ងាត់ត្រូវមានលេខយ៉ាងតិចមួយ។',
        ]);

        // គណនីថ្មីមិនមានសិទ្ធិអ្វីទាំងអស់ រហូតដល់ Admin អនុម័ត
        $user = new User();
        $user->name      = $d['name'];
        $user->email     = $d['email'];
        $user->phone     = $d['phone'] ?? null;
        $user->role      = 'pending';
        $user->is_active = false;
        $user->password  = Hash::make($d['password']);
        $user->save();

        return redirect()->route('signup')->with('registered', $user->name);
    }
}
