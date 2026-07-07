<x-guest-layout>
    <div class="container justify-center w-25 mt-5">
        <x-auth-session-status class="mb-4" :status="session('status')" />
        <x-form class="" method="POST" action="{{ route('login') }}">
            <x-form-input type="email" name="email" :default="old('email')" label="Email" required autofocus autocomplete="username" />
            <br>
            <x-form-input type="password" name="password" label="Passwort" required autocomplete="current-password" />
            <br>
            <x-form-checkbox name="remember" label="{{ __('Remember me') }}" />
            <div class="mt-2">
                @if (Route::has('password.request'))
                    <a class="text-primary text-sm" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
                <br>
                <x-form-submit class="mt-2">{{ __('Log in') }}</x-form-submit>
            </div>
        </x-form>
    </div>
</x-guest-layout>
