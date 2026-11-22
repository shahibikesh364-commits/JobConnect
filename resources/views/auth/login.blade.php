<x-guest-layout>
    @push('styles')
        @vite('resources/css/login.css')
    @endpush

    <!-- Session Status -->

    <form class="login-form" method="POST" action="{{ route('login') }}">
        @csrf

        <div class="login-audience-panel">
            <section class="login-role-section" aria-labelledby="login-role-title">
                <div class="login-role-heading">
                    <span class="login-role-eyebrow">YOUR WORKSPACE</span>
                    <h1 id="login-role-title">Choose your account type</h1>
                    <p>Select the account you use to sign in.</p>
                </div>

                <div class="login-role-options">
                    <label class="login-role-option">
                        <input type="radio" name="role" value="job_seeker" @checked(old('role') === 'job_seeker')>
                        <span class="login-role-card">
                            <span class="login-role-icon" aria-hidden="true">⌕</span>
                            <span class="login-role-copy">
                                <strong>Job Seeker</strong>
                                <small>Find and apply for jobs.</small>
                            </span>
                            <span class="login-role-check" aria-hidden="true">✓</span>
                        </span>
                    </label>

                    <label class="login-role-option">
                        <input type="radio" name="role" value="employer" @checked(old('role') === 'employer')>
                        <span class="login-role-card">
                            <span class="login-role-icon employer" aria-hidden="true">▤</span>
                            <span class="login-role-copy">
                                <strong>Employer</strong>
                                <small>Post jobs and manage applicants.</small>
                            </span>
                            <span class="login-role-check" aria-hidden="true">✓</span>
                        </span>
                    </label>
                </div>
                <x-input-error :messages="$errors->get('role')" class="login-role-error" />
                <p class="login-admin-note">Administrator accounts can sign in without selecting a public role.</p>
            </section>
        </div>

        <div class="login-credentials-panel">
            <div class="login-credentials-heading">
                <span>ACCOUNT ACCESS</span>
                <h2>Sign in to JobConnect</h2>
                <p>Enter your account details to continue.</p>
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <div class="password-field">
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />
                    <button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Show password">
                        Show
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="login-remember block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="login-actions">
                @if (Route::has('password.request'))
                    <a class="login-forgot-link underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="login-submit">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>

            <div class="login-register-prompt">
                <span>New to JobConnect?</span>
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('register') }}">
                    {{ __('Create an account') }}
                </a>
            </div>
        </div>
    </form>

</x-guest-layout>
