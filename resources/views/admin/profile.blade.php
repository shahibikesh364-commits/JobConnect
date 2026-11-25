@extends('admin.layout')

@section('title', 'Admin Account Settings')
@section('page_title', 'Account Settings')

@section('content')
    <div class="admin-account-page">
    <section class="admin-page-intro">
        <div>
            <p class="admin-eyebrow">ACCOUNT MANAGEMENT</p>
            <h2>Administrator Profile</h2>
            <p>Manage your administrator identity, account security, and personal preferences.</p>
        </div>
    </section>

    <section class="admin-account-hero" aria-label="Administrator profile summary">
        <div class="admin-account-avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
        <div class="admin-account-identity">
            <span class="admin-account-kicker">Administrator</span>
            <h2>{{ $user->name }}</h2>
            <p>{{ $user->email }}</p>
        </div>
        <div class="admin-account-hero-actions">
            <a class="admin-account-secondary-action" href="#personal-information">Edit profile</a>
        </div>
    </section>

    <section class="admin-account-overview" aria-label="Account overview">
        <article class="admin-account-overview-card">
            <span>Account role</span>
            <strong>Administrator</strong>
        </article>
        <article class="admin-account-overview-card">
            <span>Account status</span>
            <strong class="{{ $user->is_active ? 'is-positive' : 'is-negative' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </strong>
        </article>
        <article class="admin-account-overview-card">
            <span>Member since</span>
            <strong>{{ $user->created_at?->format('M j, Y') ?? 'Not available' }}</strong>
        </article>
        <article class="admin-account-overview-card">
            <span>Email verification</span>
            <strong class="{{ $user->email_verified_at ? 'is-positive' : 'is-warning' }}">
                {{ $user->email_verified_at ? 'Verified' : 'Not verified' }}
            </strong>
        </article>
    </section>

    @if (session('status') === 'profile-updated')
        <div class="admin-account-notice success" role="status">Profile information saved.</div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="admin-account-notice success" role="status">Password updated successfully.</div>
    @endif

    <div class="admin-account-grid">
        <section class="admin-account-card personal" id="personal-information">
            <div class="admin-account-card-heading">
                <div>
                    <h3>Personal information</h3>
                    <p>Update the name and email address used for your administrator account.</p>
                </div>
                <span class="admin-account-section-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                </span>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="admin-account-form personal-form">
                @csrf
                @method('PATCH')

                <label class="admin-account-field" for="admin-name">
                    <span>Full name</span>
                    <input id="admin-name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @error('name') <small class="admin-account-error">{{ $message }}</small> @enderror
                </label>

                <label class="admin-account-field" for="admin-email">
                    <span>Email address</span>
                    <input id="admin-email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                    @error('email') <small class="admin-account-error">{{ $message }}</small> @enderror
                </label>

                <div class="admin-account-form-actions">
                    <button class="admin-button" type="submit">Save changes</button>
                </div>
            </form>
        </section>

        <section class="admin-account-card security" id="account-security">
            <div class="admin-account-card-heading">
                <div>
                    <h3>Account security</h3>
                    <p>Update your password using your current credentials.</p>
                </div>
                <span class="admin-account-section-icon security" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3"/><path d="M12 14v3"/></svg>
                </span>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="admin-account-form password-form">
                @csrf
                @method('PUT')

                <label class="admin-account-field" for="admin-current-password">
                    <span>Current password</span>
                    <span class="admin-account-password-control">
                        <input id="admin-current-password" name="current_password" type="password" required autocomplete="current-password">
                        <button type="button" class="admin-account-password-toggle" data-password-toggle aria-controls="admin-current-password" aria-pressed="false" aria-label="Show current password">Show</button>
                    </span>
                    @error('current_password', 'updatePassword') <small class="admin-account-error">{{ $message }}</small> @enderror
                </label>

                <label class="admin-account-field" for="admin-new-password">
                    <span>New password</span>
                    <span class="admin-account-password-control">
                        <input id="admin-new-password" name="password" type="password" required autocomplete="new-password">
                        <button type="button" class="admin-account-password-toggle" data-password-toggle aria-controls="admin-new-password" aria-pressed="false" aria-label="Show new password">Show</button>
                    </span>
                    @error('password', 'updatePassword') <small class="admin-account-error">{{ $message }}</small> @enderror
                </label>

                <label class="admin-account-field" for="admin-confirm-password">
                    <span>Confirm new password</span>
                    <span class="admin-account-password-control">
                        <input id="admin-confirm-password" name="password_confirmation" type="password" required autocomplete="new-password">
                        <button type="button" class="admin-account-password-toggle" data-password-toggle aria-controls="admin-confirm-password" aria-pressed="false" aria-label="Show confirm password">Show</button>
                    </span>
                    @error('password_confirmation', 'updatePassword') <small class="admin-account-error">{{ $message }}</small> @enderror
                </label>

                <p class="admin-account-password-help">Use a unique password. Your current password is required to confirm this change.</p>

                <div class="admin-account-form-actions">
                    <button class="admin-button" type="submit">Update password</button>
                </div>
            </form>
        </section>

        <section class="admin-account-card permissions" id="administrator-permissions">
            <div class="admin-account-card-heading">
                <div>
                    <h3>Administrator permissions</h3>
                    <p>Your administrator role grants access to these platform areas.</p>
                </div>
                <span class="admin-account-badge role">Full admin access</span>
            </div>

            <ul class="admin-account-permissions">
                <li><span aria-hidden="true">✓</span><span><strong>Platform overview</strong><small>View platform metrics and status.</small></span></li>
                <li><span aria-hidden="true">✓</span><span><strong>User management</strong><small>Review accounts and roles.</small></span></li>
                <li><span aria-hidden="true">✓</span><span><strong>Job seeker management</strong><small>Review candidate accounts.</small></span></li>
                <li><span aria-hidden="true">✓</span><span><strong>Employer management</strong><small>Review employer accounts.</small></span></li>
                <li><span aria-hidden="true">✓</span><span><strong>Job listing moderation</strong><small>Manage listing status.</small></span></li>
                <li><span aria-hidden="true">✓</span><span><strong>Application management</strong><small>Review submitted applications.</small></span></li>
                <li><span aria-hidden="true">✓</span><span><strong>Notification center</strong><small>View in-app account notifications.</small></span></li>
            </ul>
        </section>

        <section class="admin-account-card activity" id="account-activity">
            <div class="admin-account-card-heading">
                <div>
                    <h3>Login and account activity</h3>
                    <p>Login and administrative action history for this account.</p>
                </div>
            </div>
            <div class="admin-account-activity-list" aria-label="Activity tracking status">
                <div><span>Last login</span><strong>Not tracked</strong></div>
                <div><span>Administrative activity</span><strong>Not tracked</strong></div>
            </div>
        </section>

        <section class="admin-account-card preferences" id="account-preferences">
            <div class="admin-account-card-heading">
                <div>
                    <h3>Account preferences</h3>
                    <p>Choose your appearance and open system notifications for this account.</p>
                </div>
            </div>
            <div class="admin-account-preference-actions">
                <div class="admin-account-preference-item">
                    <span><strong>Light or dark mode</strong><small>Choose the appearance for this browser.</small></span>
                    <x-theme-toggle />
                </div>
                <a class="admin-account-preference-item notifications" href="{{ route('notifications.index') }}">
                    <span><strong>System notifications</strong><small>View platform notifications for your account.</small></span>
                    <span class="admin-account-preference-link">Open notifications →</span>
                </a>
            </div>
        </section>
    </div>

    <div class="admin-account-logout">
        <span>Finished managing your account?</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="small-button danger">Log out</button>
        </form>
    </div>
    </div>
@endsection
