<x-guest-layout>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=4">
    <style>
        .title{
            margin: 0 auto;
            color: #fffff;
        }
    </style>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />
   <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-white dark:text-white tracking-tight">Pawn Shop System Login</h2>
        </div>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
         <div class="mt-4 text-left">
            <a href="{{ route('signup') }}" class="underline text-sm text-gray-400 hover:text-white">
                <span class="Khmer-font">មិនទាន់មានគណនី? ចុះឈ្មោះ</span>/ Register
            </a>
        </div>
    </form>
</x-guest-layout>