@php($settingsApi = $settingsApi ?? false)

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ $settingsApi ? __('Update the contact details and preferences for this employer account.') : __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6"
        @if ($settingsApi) data-settings-form data-endpoint="/api/employer/settings/account" data-method="PUT" data-form-key="account" @endif>
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="$settingsApi ? __('Work email') : __('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" @if ($settingsApi) data-account-email @endif />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                </div>
            @endif
        </div>

        @if ($settingsApi)
            <div class="settings-account-extra">
                <label for="settings-current-password">Current password <small>Required only when changing your email</small></label>
                <input id="settings-current-password" name="current_password" type="password" autocomplete="current-password" data-email-password>
            </div>
            <div class="settings-account-extra">
                <label for="settings-contact-person">Contact person</label>
                <input id="settings-contact-person" name="contact_person" type="text" maxlength="255" autocomplete="name" data-account-field="contact_person">
            </div>
            <div class="settings-account-extra">
                <label for="settings-contact-phone">Work phone</label>
                <input id="settings-contact-phone" name="contact_phone" type="tel" maxlength="40" autocomplete="tel" data-account-field="contact_phone">
            </div>
            <div class="settings-account-extra">
                <label for="settings-language">Language</label>
                <select id="settings-language" name="language" data-account-field="language"><option value="en">English</option><option value="es">Español</option><option value="fr">Français</option><option value="de">Deutsch</option></select>
            </div>
            <div class="settings-account-extra">
                <label for="settings-timezone">Timezone</label>
                <select id="settings-timezone" name="timezone" data-account-field="timezone">
                    @foreach (timezone_identifiers_list() as $timezone)
                        <option value="{{ $timezone }}">{{ $timezone }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ $settingsApi ? __('Save account settings') : __('Save') }}</x-primary-button>

        </div>
    </form>
</section>
