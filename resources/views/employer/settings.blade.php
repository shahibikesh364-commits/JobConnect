<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-favicon />
    <title>Employer Settings - JobConnect</title>
    @vite(['resources/css/jobs/management.css', 'resources/css/employer/sidebar.css', 'resources/css/employer/workspace.css', 'resources/css/employer/settings.css', 'resources/css/notifications.css', 'resources/js/app.js', 'resources/js/employer-settings.js'])
</head>

<body class="employer-workspace employer-settings-layout">
    <main class="employer-page-shell">
        <x-employer-sidebar />
        <div class="employer-page-content employer-settings-page">
            <x-employer-workspace-toolbar />

            <header class="settings-hub-heading">
                <div>
                    <p class="settings-eyebrow">EMPLOYER WORKSPACE</p>
                    <h1>Settings</h1>
                    <a class="settings-preview-profile" href="{{ route('employer.company-profile.preview') }}">Preview company profile <span aria-hidden="true">↗</span></a>
                </div>
            </header>

            <div class="settings-toast" role="status" aria-live="polite" hidden></div>
            <div class="settings-skeleton" aria-label="Loading settings" aria-busy="true">
                <div class="settings-skeleton-tabs"></div>
                <div class="settings-skeleton-panel"><i></i><i></i><i></i><i></i></div>
            </div>

            <div class="settings-app" hidden aria-busy="true">
                <div class="settings-workspace">
                    <nav class="settings-tabs" role="tablist" aria-label="Employer settings">
                        <button id="tab-company" class="settings-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="panel-company" data-tab="company"><span aria-hidden="true">▣</span>Company Profile</button>
                        <button id="tab-account" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-account" data-tab="account"><span aria-hidden="true">◎</span>Account Settings</button>
                        <button id="tab-security" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-security" data-tab="security"><span aria-hidden="true">⌑</span>Security</button>
                        <button id="tab-notifications" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-notifications" data-tab="notifications"><span aria-hidden="true">◉</span>Notifications</button>
                        <button id="tab-hiring" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-hiring" data-tab="hiring"><span aria-hidden="true">⌕</span>Hiring Preferences</button>
                        <button id="tab-team" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-team" data-tab="team"><span aria-hidden="true">♙</span>Team Members</button>
                        <button id="tab-billing" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-billing" data-tab="billing"><span aria-hidden="true">▤</span>Billing &amp; Plan</button>
                        <button id="tab-privacy" class="settings-tab" type="button" role="tab" aria-selected="false" aria-controls="panel-privacy" data-tab="privacy"><span aria-hidden="true">⌫</span>Privacy &amp; Danger Zone</button>
                    </nav>

                    <div class="settings-panels">
                        <section id="panel-company" class="settings-panel" role="tabpanel" aria-labelledby="tab-company" data-panel="company">
                            <header class="settings-panel-heading"><div><span class="settings-kicker">PUBLIC PRESENCE</span><h2>Company Profile</h2><p>Share the details candidates need to learn about your organization.</p></div></header>
                            <form class="settings-form" data-settings-form data-endpoint="/api/employer/settings/company" data-method="PUT" data-form-key="company">
                                <div class="settings-logo-row">
                                    <div class="settings-logo-preview" data-logo-preview aria-label="Company logo preview">JC</div>
                                    <div class="settings-logo-controls"><strong>Company logo</strong><small>JPG, PNG or WebP. Maximum 2 MB.</small><div class="settings-inline-actions"><label class="settings-button settings-button-secondary" for="company-logo">Upload logo</label><input id="company-logo" class="settings-visually-hidden" type="file" accept="image/jpeg,image/png,image/webp" data-logo-input><button class="settings-text-button" type="button" data-remove-logo>Remove</button></div></div>
                                </div>
                                <div class="settings-fields">
                                    <label class="settings-field"><span>Company name</span><input name="company_name" type="text" maxlength="255" required autocomplete="organization"></label>
                                    <label class="settings-field"><span>Tagline</span><input name="tagline" type="text" maxlength="180" placeholder="A short introduction to your company"></label>
                                    <label class="settings-field"><span>Industry</span><select name="industry"><option value="">Select an industry</option><option>Technology</option><option>Finance</option><option>Healthcare</option><option>Education</option><option>Manufacturing</option><option>Retail</option><option>Hospitality</option><option>Other</option></select></label>
                                    <label class="settings-field"><span>Company size</span><select name="company_size"><option value="">Select company size</option><option>1-10</option><option>11-50</option><option>51-200</option><option>201-500</option><option>500+</option></select></label>
                                    <label class="settings-field"><span>Founded year</span><input name="founded_year" type="number" min="1800" max="{{ now()->year }}" inputmode="numeric"></label>
                                    <label class="settings-field"><span>Company website</span><input name="website" type="url" placeholder="https://example.com" autocomplete="url"></label>
                                    <label class="settings-field"><span>Country</span><input name="country" type="text" maxlength="100" autocomplete="country-name"></label>
                                    <label class="settings-field"><span>City</span><input name="city" type="text" maxlength="100" autocomplete="address-level2"></label>
                                    <label class="settings-field settings-field-wide"><span>Full address</span><input name="full_address" type="text" maxlength="255" autocomplete="street-address"></label>
                                    <label class="settings-field settings-field-wide"><span>About the company <small data-about-count>0 / 1000</small></span><textarea name="about_company" rows="4" maxlength="1000" data-about-company></textarea></label>
                                    <label class="settings-field"><span>LinkedIn</span><input name="social_links[linkedin]" type="url" placeholder="https://linkedin.com/company/..."></label>
                                    <label class="settings-field"><span>Facebook</span><input name="social_links[facebook]" type="url" placeholder="https://facebook.com/..."></label>
                                    <label class="settings-field"><span>Twitter / X</span><input name="social_links[twitter]" type="url" placeholder="https://x.com/..."></label>
                                </div>
                                <div class="settings-form-footer"><span class="settings-save-feedback" aria-live="polite"></span><button class="settings-button settings-button-primary" type="submit">Save company profile</button></div>
                            </form>
                        </section>

                        <section id="panel-account" class="settings-panel" role="tabpanel" aria-labelledby="tab-account" data-panel="account" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">LOGIN &amp; CONTACT</span><h2>Account Settings</h2><p>Manage the contact details and preferences for this employer account.</p></div></header>
                            <div class="settings-form-wrap employer-settings-partial">
                                @include('profile.partials.update-profile-information-form', ['settingsApi' => true])
                            </div>
                        </section>

                        <section id="panel-security" class="settings-panel" role="tabpanel" aria-labelledby="tab-security" data-panel="security" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">PROTECT YOUR ACCOUNT</span><h2>Security</h2><p>Use a unique password and review where you are signed in.</p></div></header>
                            <form class="settings-form" data-settings-form data-endpoint="/api/employer/settings/password" data-method="PUT" data-form-key="password">
                                <div class="settings-fields settings-fields-security">
                                    <label class="settings-field"><span>Current password</span><span class="settings-password-input"><input name="current_password" type="password" autocomplete="current-password" required data-password><button type="button" data-show-password aria-label="Show current password">Show</button></span></label>
                                    <label class="settings-field"><span>New password</span><span class="settings-password-input"><input name="password" type="password" autocomplete="new-password" minlength="8" required data-password data-new-password><button type="button" data-show-password aria-label="Show new password">Show</button></span><span class="settings-strength"><i data-strength-bar></i></span><small data-strength-label>Use at least 8 characters.</small></label>
                                    <label class="settings-field"><span>Confirm new password</span><span class="settings-password-input"><input name="password_confirmation" type="password" autocomplete="new-password" required data-password><button type="button" data-show-password aria-label="Show password confirmation">Show</button></span></label>
                                </div>
                                <div class="settings-form-footer"><span class="settings-save-feedback" aria-live="polite"></span><button class="settings-button settings-button-primary" type="submit">Update password</button></div>
                            </form>
                            <div class="settings-subsection"><div class="settings-subsection-heading"><div><h3>Active sessions</h3><p>Sign out of other devices that are currently using your account.</p></div><button type="button" class="settings-button settings-button-secondary" data-logout-sessions>Log out other devices</button></div><div class="settings-session-list" data-session-list><p class="settings-muted">Loading sessions…</p></div></div>
                        </section>

                        <section id="panel-notifications" class="settings-panel" role="tabpanel" aria-labelledby="tab-notifications" data-panel="notifications" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">STAY INFORMED</span><h2>Notification Preferences</h2><p>Choose which updates JobConnect sends by email and in the app.</p></div></header>
                            <form class="settings-form" data-settings-form data-endpoint="/api/employer/settings/notifications" data-method="PUT" data-form-key="notifications">
                                <div class="settings-notification-head"><span>Activity</span><span>Email</span><span>In-app</span></div>
                                <div class="settings-notification-rows">
                                    <div class="settings-notification-row"><span><strong>New application received</strong><small>When a candidate applies to your job</small></span><label><span class="sr-only">Email for new applications</span><input type="checkbox" name="preferences[new_application][email]"></label><label><span class="sr-only">In-app for new applications</span><input type="checkbox" name="preferences[new_application][in_app]"></label></div>
                                    <div class="settings-notification-row"><span><strong>Candidate message</strong><small>When a candidate sends you a message</small></span><label><span class="sr-only">Email for candidate messages</span><input type="checkbox" name="preferences[candidate_message][email]"></label><label><span class="sr-only">In-app for candidate messages</span><input type="checkbox" name="preferences[candidate_message][in_app]"></label></div>
                                    <div class="settings-notification-row"><span><strong>Job post expiring</strong><small>Before an application deadline closes</small></span><label><span class="sr-only">Email for expiring job posts</span><input type="checkbox" name="preferences[job_expiring][email]"></label><label><span class="sr-only">In-app for expiring job posts</span><input type="checkbox" name="preferences[job_expiring][in_app]"></label></div>
                                    <div class="settings-notification-row"><span><strong>Job moderation</strong><small>When your job is approved or rejected</small></span><label><span class="sr-only">Email for job moderation</span><input type="checkbox" name="preferences[job_moderation][email]"></label><label><span class="sr-only">In-app for job moderation</span><input type="checkbox" name="preferences[job_moderation][in_app]"></label></div>
                                    <div class="settings-notification-row"><span><strong>Weekly recruitment summary</strong><small>A weekly overview of applications and activity</small></span><label><span class="sr-only">Email weekly summary</span><input type="checkbox" name="preferences[weekly_summary][email]"></label><label><span class="sr-only">In-app weekly summary</span><input type="checkbox" name="preferences[weekly_summary][in_app]"></label></div>
                                    <div class="settings-notification-row"><span><strong>Product updates</strong><small>News about JobConnect features and improvements</small></span><label><span class="sr-only">Email product updates</span><input type="checkbox" name="preferences[product_updates][email]"></label><label><span class="sr-only">In-app product updates</span><input type="checkbox" name="preferences[product_updates][in_app]"></label></div>
                                </div>
                                <div class="settings-form-footer"><span class="settings-save-feedback" aria-live="polite"></span><button class="settings-button settings-button-primary" type="submit">Save preferences</button></div>
                            </form>
                        </section>

                        <section id="panel-hiring" class="settings-panel" role="tabpanel" aria-labelledby="tab-hiring" data-panel="hiring" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">DEFAULTS FOR NEW LISTINGS</span><h2>Hiring Preferences</h2><p>Set default job settings and screening questions for future listings.</p></div></header>
                            <form class="settings-form" data-settings-form data-endpoint="/api/employer/settings/hiring-preferences" data-method="PUT" data-form-key="hiring">
                                <div class="settings-fields">
                                    <label class="settings-field"><span>Default job type</span><select name="default_job_type"><option>Full-time</option><option>Part-time</option><option>Contract</option><option>Internship</option><option>Remote</option></select></label>
                                    <label class="settings-field"><span>Default application deadline (days)</span><input name="application_deadline_days" type="number" min="1" max="365" required></label>
                                    <label class="settings-field"><span>Alert me after this many applications</span><input name="application_alert_threshold" type="number" min="1" max="10000" placeholder="Off"></label>
                                    <label class="settings-toggle-field"><span><strong>Auto-close jobs after the deadline</strong><small>Close applications when the deadline passes.</small></span><input name="auto_close_jobs" type="checkbox"></label>
                                    <label class="settings-field settings-field-wide"><span>Screening questions template <small>One question per line, up to 10.</small></span><textarea name="screening_questions_text" rows="5" data-screening-questions placeholder="What interests you about this role?"></textarea></label>
                                </div>
                                <div class="settings-form-footer"><span class="settings-save-feedback" aria-live="polite"></span><button class="settings-button settings-button-primary" type="submit">Save hiring preferences</button></div>
                            </form>
                        </section>

                        <section id="panel-team" class="settings-panel" role="tabpanel" aria-labelledby="tab-team" data-panel="team" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">COLLABORATION</span><h2>Team Members</h2><p>Invite colleagues and manage their access to employer settings.</p></div></header>
                            <form class="settings-team-invite" data-team-invite>
                                <label class="settings-field"><span>Name</span><input name="name" type="text" maxlength="255" autocomplete="name"></label>
                                <label class="settings-field"><span>Email address</span><input name="email" type="email" required autocomplete="email"></label>
                                <label class="settings-field"><span>Role</span><select name="role"><option value="recruiter">Recruiter</option><option value="viewer">Viewer</option><option value="admin">Admin</option></select></label>
                                <button class="settings-button settings-button-primary" type="submit">Invite member</button>
                            </form>
                            <div class="settings-table-wrap"><table class="settings-table"><thead><tr><th>Team member</th><th>Role</th><th>Status</th><th><span class="sr-only">Actions</span></th></tr></thead><tbody data-team-list><tr><td colspan="4" class="settings-muted">Loading team…</td></tr></tbody></table></div>
                            <p class="settings-help-text" data-team-permission hidden>Only a workspace admin can manage team members.</p>
                        </section>

                        <section id="panel-billing" class="settings-panel" role="tabpanel" aria-labelledby="tab-billing" data-panel="billing" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">SUBSCRIPTION</span><h2>Billing &amp; Plan</h2><p>Plan details are a preview while JobConnect billing is being set up.</p></div><span class="settings-demo-badge">DEMO DATA</span></header>
                            <div class="settings-plan-summary"><div><span>Current plan</span><strong data-plan-name>Starter</strong><small>Demo plan · no payment is collected</small></div><div><span>Job posts used</span><strong><b data-jobs-used>0</b> / <b data-job-limit>10</b></strong><small>Current calendar month</small></div><div><span>Next renewal</span><strong data-renews-at>—</strong><small>Indicative date only</small></div></div>
                            <div class="settings-plan-note">Upgrade and billing history are unavailable until a payment provider is connected.</div>
                            <button type="button" class="settings-button settings-button-secondary" disabled title="Billing is not connected yet">Upgrade plan</button>
                            <div class="settings-table-wrap"><table class="settings-table"><thead><tr><th>Date</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead><tbody data-billing-history><tr><td colspan="4" class="settings-muted">No billing history available.</td></tr></tbody></table></div>
                        </section>

                        <section id="panel-privacy" class="settings-panel" role="tabpanel" aria-labelledby="tab-privacy" data-panel="privacy" hidden>
                            <header class="settings-panel-heading"><div><span class="settings-kicker">YOUR DATA &amp; ACCOUNT</span><h2>Privacy &amp; Danger Zone</h2><p>Control company visibility, export your data, or permanently delete your account.</p></div></header>
                            <form class="settings-form" data-settings-form data-endpoint="/api/employer/settings/privacy" data-method="PUT" data-form-key="privacy">
                                <label class="settings-toggle-field"><span><strong>Show company profile to candidates</strong><small>Allow candidates to view your employer profile.</small></span><input name="public_profile" type="checkbox"></label>
                                <div class="settings-form-footer"><span class="settings-save-feedback" aria-live="polite"></span><button class="settings-button settings-button-primary" type="submit">Save privacy settings</button></div>
                            </form>
                            <div class="settings-account-delete"><div><strong>Deactivate account</strong><small>Temporarily prevent sign-in to your account. Existing data is retained.</small></div><button class="settings-button settings-button-secondary" type="button" data-deactivate-account>Deactivate</button></div>
                            <div class="settings-data-export"><div><strong>Download your data</strong><small>Get a JSON copy of your account, company settings, and job listings.</small></div><a class="settings-button settings-button-secondary" href="/api/employer/settings/export">Download data</a></div>
                            <div class="settings-existing-delete-form">
                                @include('profile.partials.delete-user-form', ['settingsApi' => true, 'requiresDeleteConfirmation' => true])
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <noscript><p class="settings-noscript">Enable JavaScript to manage employer settings.</p></noscript>

        </div>
    </main>
    @include('components.footer')
</body>

</html>
