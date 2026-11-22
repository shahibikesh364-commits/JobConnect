<x-guest-layout>
    @push('styles')
        @vite('resources/css/registration.css')
    @endpush

    <form class="registration-form" method="POST" action="{{ route('register') }}">
        @csrf

        <fieldset class="registration-role-section" aria-describedby="registration-role-help">
            <legend class="registration-title">Create your account</legend>
            <p class="registration-subtitle" id="registration-role-help">Choose how you want to use JobConnect.</p>

            <div class="registration-role-options">
                <label class="registration-role-option">
                    <input type="radio" name="role" value="job_seeker" @checked(old('role') === 'job_seeker') required>
                    <span class="registration-role-card">
                        <span class="registration-role-icon seeker" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.2 4.2"/></svg>
                        </span>
                        <span class="registration-role-copy">
                            <strong>Job Seeker</strong>
                            <small>Find jobs, apply for opportunities and manage a professional profile.</small>
                        </span>
                        <span class="registration-role-check" aria-hidden="true">✓</span>
                    </span>
                </label>

                <label class="registration-role-option">
                    <input type="radio" name="role" value="employer" @checked(old('role') === 'employer') required>
                    <span class="registration-role-card">
                        <span class="registration-role-icon employer" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="7" width="17" height="13" rx="2"/><path d="M8.5 7V5.5A1.5 1.5 0 0 1 10 4h4a1.5 1.5 0 0 1 1.5 1.5V7M3.5 12h17M10 12v2h4v-2"/></svg>
                        </span>
                        <span class="registration-role-copy">
                            <strong>Employer</strong>
                            <small>Post job vacancies, manage listings and review applicants.</small>
                        </span>
                        <span class="registration-role-check" aria-hidden="true">✓</span>
                    </span>
                </label>
            </div>

            <x-input-error :messages="$errors->get('role')" class="registration-role-error" />
            <p class="registration-selection-status">
                <span class="registration-unselected">Select an account type to continue.</span>
                <span class="registration-selected">Great choice. Add your details below to finish setting up your account.</span>
            </p>
        </fieldset>

        <div class="registration-fields">
            <div class="registration-fields-heading">
                <span>YOUR DETAILS</span>
                <p>All set? Your account will be ready in just a moment.</p>
            </div>

            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mt-4">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <div class="password-field">
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" />
                    <button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Show password">Show</button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                <div class="password-field">
                    <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" />
                    <button type="button" class="password-toggle" data-password-toggle aria-controls="password_confirmation" aria-pressed="false" aria-label="Show confirm password">Show</button>
                </div>

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="registration-actions">
                <a class="registration-login-link" href="{{ route('login') }}">
                    {{ __('Already registered?') }} <span>Sign in</span>
                </a>

                <x-primary-button class="registration-submit">
                    {{ __('Create account') }}
                    <span aria-hidden="true">→</span>
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>
