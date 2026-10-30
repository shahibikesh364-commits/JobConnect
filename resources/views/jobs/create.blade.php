<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($job) ? 'Edit Job' : 'Post a Job' }} | JobConnect</title>
    @vite(['resources/css/jobs/management.css', 'resources/css/notifications.css', 'resources/js/app.js'])
</head>
<body>
    <main class="management-container job-editor-page">
        @include('employer.partials.topbar')

        <header class="management-header">
            <div class="header-title">
                <span class="eyebrow">EMPLOYER WORKSPACE</span>
                <h1>{{ isset($job) ? 'Edit job listing' : 'Create a job listing' }}</h1>
                <p>Share the role details candidates need to decide if it is right for them.</p>
            </div>
            <div class="header-actions">
                <a class="button secondary" href="{{ route('employer.jobs.index') }}">Back to listings</a>
            </div>
        </header>

        @if ($errors->any())
            <div class="alert error-message" role="alert">
                <strong>Please review these fields:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="job-editor-card">
            <div class="job-editor-intro">
                <span class="job-editor-icon" aria-hidden="true">＋</span>
                <div>
                    <h2>Listing details</h2>
                    <p>Fields marked with <span aria-hidden="true">*</span> are required.</p>
                </div>
            </div>

            <form class="job-editor-form" action="{{ isset($job) ? route('employer.jobs.update', $job) : route('employer.jobs.store') }}" method="POST">
                @csrf
                @if (isset($job)) @method('PUT') @endif

                <div class="job-form-grid">
                    <div class="job-form-field">
                        <label for="title">Job title <span>*</span></label>
                        <input type="text" id="title" name="title" maxlength="255" value="{{ old('title', $job->title ?? '') }}" placeholder="e.g. Frontend Developer" required>
                        @error('title')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field">
                        <label for="company_name">Company name <span>*</span></label>
                        <input type="text" id="company_name" name="company_name" maxlength="255" value="{{ old('company_name', $job->company_name ?? '') }}" placeholder="Your company" required>
                        @error('company_name')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field">
                        <label for="location">Location <span>*</span></label>
                        <input type="text" id="location" name="location" maxlength="255" value="{{ old('location', $job->location ?? '') }}" placeholder="e.g. Kathmandu, Nepal" required>
                        @error('location')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field">
                        <label for="job_type">Employment type <span>*</span></label>
                        <select id="job_type" name="job_type" required>
                            @foreach (['Full-time', 'Part-time', 'Internship', 'Remote', 'Contract'] as $type)
                                <option value="{{ $type }}" @selected(old('job_type', $job->job_type ?? 'Full-time') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('job_type')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field">
                        <label for="salary_min">Minimum monthly salary <small>(NPR)</small></label>
                        <input type="number" id="salary_min" name="salary_min" min="0" step="0.01" value="{{ old('salary_min', $job->salary_min ?? '') }}" placeholder="e.g. 25000">
                        @error('salary_min')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field">
                        <label for="salary_max">Maximum monthly salary <small>(NPR)</small></label>
                        <input type="number" id="salary_max" name="salary_max" min="0" step="0.01" value="{{ old('salary_max', $job->salary_max ?? '') }}" placeholder="e.g. 45000">
                        @error('salary_max')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field full-width">
                        <label for="description">Job description <span>*</span></label>
                        <textarea id="description" name="description" rows="6" required placeholder="Describe the role, key responsibilities, and what success looks like.">{{ old('description', $job->description ?? '') }}</textarea>
                        @error('description')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field full-width">
                        <label for="requirements">Skills and requirements</label>
                        <textarea id="requirements" name="requirements" rows="5" placeholder="List essential skills, experience, and qualifications.">{{ old('requirements', $job->requirements ?? '') }}</textarea>
                        @error('requirements')<small class="field-error">{{ $message }}</small>@enderror
                    </div>

                    <div class="job-form-field">
                        <label for="application_deadline">Application deadline</label>
                        <input type="date" id="application_deadline" name="application_deadline" value="{{ old('application_deadline', isset($job) && $job->application_deadline ? $job->application_deadline->format('Y-m-d') : '') }}">
                        @error('application_deadline')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="job-form-actions">
                    <a class="button secondary" href="{{ route('employer.jobs.index') }}">Cancel</a>
                    <button class="button" type="submit">{{ isset($job) ? 'Save changes' : 'Publish listing' }}</button>
                </div>
            </form>
        </section>

        @include('components.footer')
    </main>
</body>
</html>
