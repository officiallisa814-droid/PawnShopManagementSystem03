<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /** ទំព័រ Profile */
    public function index(Request $request)
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    /** កែឈ្មោះ / អ៊ីមែល / ទូរស័ព្ទ */
    public function updateInfo(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,20}$/'],
        ], $this->messages());

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->save();

        return back()->with('success', 'បានរក្សាទុកព័ត៌មានរួចរាល់ ✓')->with('tab', 'info');
    }

    /** ប្តូរពាក្យសម្ងាត់ */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], $this->messages());

        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'បានប្តូរពាក្យសម្ងាត់រួចរាល់ ✓')->with('tab', 'security');
    }

    /** ផ្លាស់ប្តូររូបថត */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], $this->messages());

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $request->file('avatar')->store('avatars', 'public');
        $user->save();

        return back()->with('success', 'បានប្តូររូបថតរួចរាល់ ✓')->with('tab', 'info');
    }

    /** លុបរូបថត */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return back()->with('success', 'បានលុបរូបថតរួចរាល់។')->with('tab', 'info');
    }

    /** សារកំហុសជាភាសាខ្មែរ */
    private function messages(): array
    {
        return [
            'name.required' => 'សូមបញ្ចូលឈ្មោះរបស់អ្នក។',
            'name.min' => 'ឈ្មោះត្រូវមានយ៉ាងតិច ២ តួអក្សរ។',
            'name.max' => 'ឈ្មោះវែងពេក (អតិបរមា ១០០ តួអក្សរ)។',

            'email.required' => 'សូមបញ្ចូលអ៊ីមែល។',
            'email.email' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ (ឧ. name@example.com)។',
            'email.unique' => 'អ៊ីមែលនេះត្រូវបានគេប្រើរួចហើយ។',

            'phone.regex' => 'លេខទូរស័ព្ទមិនត្រឹមត្រូវ។',

            'current_password.required' => 'សូមបញ្ចូលពាក្យសម្ងាត់បច្ចុប្បន្ន។',
            'current_password.current_password' => 'ពាក្យសម្ងាត់បច្ចុប្បន្នមិនត្រឹមត្រូវ។',
            'password.required' => 'សូមបញ្ចូលពាក្យសម្ងាត់ថ្មី។',
            'password.confirmed' => 'ការបញ្ជាក់ពាក្យសម្ងាត់មិនត្រូវគ្នា។',
            'password.min' => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៨ តួអក្សរ។',
            'password.letters' => 'ពាក្យសម្ងាត់ត្រូវមានអក្សរយ៉ាងតិចមួយ។',
            'password.numbers' => 'ពាក្យសម្ងាត់ត្រូវមានលេខយ៉ាងតិចមួយ។',

            'avatar.required' => 'សូមជ្រើសរើសរូបថត។',
            'avatar.image' => 'ឯកសារត្រូវតែជារូបភាព។',
            'avatar.mimes' => 'អនុញ្ញាតតែ JPG, PNG ឬ WEBP ប៉ុណ្ណោះ។',
            'avatar.max' => 'រូបភាពធំពេក (អតិបរមា 2MB)។',
        ];
    }
}