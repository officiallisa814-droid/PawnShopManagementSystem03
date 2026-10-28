<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'pending')
            ->orderByRaw("FIELD(role,'admin','manager','cashier','appraiser')")
            ->orderBy('name')
            ->get();

        $pending = User::where('role', 'pending')->latest()->get();

        return view('admin.users', compact('users', 'pending'));
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'role' => ['required', Rule::in($this->roleKeys())],
            'password' => ['required', Password::min(8)->letters()->numbers()],
        ], $this->messages());

        $user = new User();
        $user->name = $d['name'];
        $user->email = $d['email'];
        $user->phone = $d['phone'] ?? null;
        $user->role = $d['role'];
        $user->is_active = $request->boolean('is_active');
        $user->password = Hash::make($d['password']);
        $user->save();

        return back()->with('success', 'បានបង្កើតអ្នកប្រើ "' . $user->name . '" រួចរាល់ ✓');
    }

    public function update(Request $request, User $user)
    {
        $self = $user->is($request->user());

        $d = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'role' => [$self ? 'nullable' : 'required', Rule::in($this->roleKeys())],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
        ], $this->messages());

        // មិនអាចប្តូរ Role / ផ្អាកគណនីខ្លួនឯងបាន
        $role = $self ? $user->role : $d['role'];
        $active = $self ? true : $request->boolean('is_active');

        // ត្រូវមាន Admin សកម្មយ៉ាងតិចម្នាក់
        if (
            !$self && $user->roleKey() === 'admin' && ($role !== 'admin' || !$active)
            && $this->otherActiveAdmins($user) === 0
        ) {
            return back()->withInput()->withErrors([
                'role' => 'ត្រូវមាន Admin ដែលសកម្មយ៉ាងតិចម្នាក់ក្នុងប្រព័ន្ធ។',
            ]);
        }

        $user->name = $d['name'];
        $user->email = $d['email'];
        $user->phone = $d['phone'] ?? null;
        $user->role = $role;
        $user->is_active = $active;
        if (!empty($d['password'])) {
            $user->password = Hash::make($d['password']);
        }
        $user->save();

        return back()->with('success', 'បានកែអ្នកប្រើ "' . $user->name . '" រួចរាល់ ✓');
    }

    /** អនុម័តគណនីដែលទើបចុះឈ្មោះ ហើយកំណត់ Role */
    public function approve(Request $request, User $user)
    {
        abort_unless($user->isPending(), 404);

        $d = $request->validate([
            'role' => ['required', Rule::in($this->roleKeys())],
        ], $this->messages());

        $user->role = $d['role'];
        $user->is_active = true;
        $user->save();

        return back()->with('success', 'បានអនុម័ត "' . $user->name . '" ជា ' . config('roles.roles.' . $d['role'] . '.label') . ' ✓');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'អ្នកមិនអាចលុបគណនីរបស់ខ្លួនឯងបានទេ។');
        }

        if ($user->roleKey() === 'admin' && $this->otherActiveAdmins($user) === 0) {
            return back()->with('error', 'មិនអាចលុប Admin ចុងក្រោយបានទេ។');
        }

        try {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->delete();
        } catch (QueryException $e) {
            return back()->with('error', 'មិនអាចលុបបានទេ ព្រោះមានទិន្នន័យភ្ជាប់នឹងអ្នកប្រើនេះ។ សូមផ្អាកគណនីជំនួសវិញ។');
        }

        return back()->with('success', 'បានលុបអ្នកប្រើរួចរាល់។');
    }

    /* ---------------------------------------------------------- */

    private function roleKeys(): array
    {
        return array_keys(config('roles.roles'));
    }

    private function otherActiveAdmins(User $except): int
    {
        return User::where('role', 'admin')
            ->where('id', '!=', $except->id)
            ->where('is_active', true)
            ->count();
    }

    private function messages(): array
    {
        return [
            'name.required' => 'សូមបញ្ចូលឈ្មោះ។',
            'name.min' => 'ឈ្មោះត្រូវមានយ៉ាងតិច ២ តួអក្សរ។',
            'email.required' => 'សូមបញ្ចូលអ៊ីមែល។',
            'email.email' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។',
            'email.unique' => 'អ៊ីមែលនេះត្រូវបានគេប្រើរួចហើយ។',
            'phone.regex' => 'លេខទូរស័ព្ទមិនត្រឹមត្រូវ។',
            'role.required' => 'សូមជ្រើសរើស Role។',
            'role.in' => 'Role មិនត្រឹមត្រូវ។',
            'password.required' => 'សូមបញ្ចូលពាក្យសម្ងាត់។',
            'password.min' => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៨ តួអក្សរ។',
            'password.letters' => 'ពាក្យសម្ងាត់ត្រូវមានអក្សរយ៉ាងតិចមួយ។',
            'password.numbers' => 'ពាក្យសម្ងាត់ត្រូវមានលេខយ៉ាងតិចមួយ។',
        ];
    }
}