<x-app-layout>
    <x-slot name="title">Edit Profile</x-slot>
    <x-slot name="header">
        <div class="profile-header-main">
            <div class="profile-page-heading">
                <p class="profile-label">MY CAREER</p>
                <h1>Edit profile</h1>
                <p>Keep your details current so employers can understand your experience.</p>
            </div>
            <a class="profile-back-link" href="{{ route('job-seeker.profile.show') }}">← Back to profile</a>
        </div>
    </x-slot>

    <div class="profile-editor-page">
        @if ($errors->any())
            <div class="profile-editor-alert" role="alert">
                <strong>Please review the highlighted information.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form class="profile-editor-form" method="POST" action="{{ route('job-seeker.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <section class="profile-editor-card profile-photo-editor-card">
                <div class="profile-editor-section-heading">
                    <span class="profile-editor-icon violet" aria-hidden="true">◎</span>
                    <div><h2>Profile photo</h2><p>Choose a clear photo so people can recognize your profile.</p></div>
                </div>
                <div class="profile-photo-editor-row">
                    @if ($profile?->profile_photo)
                        <img class="profile-photo-editor-avatar" src="{{ asset('storage/' . $profile->profile_photo) }}" alt="Current profile photo">
                    @else
                        <span class="profile-photo-editor-avatar placeholder" aria-hidden="true">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    @endif
                    <div class="profile-photo-editor-copy">
                        <label class="profile-editor-field">
                            <span>Upload a photo <small>JPG, PNG, or WebP · up to 4 MB</small></span>
                            <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <small class="profile-photo-editor-note">Your new photo appears after you save your profile.</small>
                    </div>
                </div>
            </section>

            <section class="profile-editor-card">
                <div class="profile-editor-section-heading">
                    <span class="profile-editor-icon" aria-hidden="true">♙</span>
                    <div><h2>Personal information</h2><p>Your name and contact details.</p></div>
                </div>
                <div class="profile-editor-grid">
                    <label class="profile-editor-field"><span>Full name</span><input name="name" value="{{ old('name', $user->name) }}" required autocomplete="name"></label>
                    <label class="profile-editor-field"><span>Email address</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email"></label>
                    <label class="profile-editor-field"><span>Phone number</span><input type="tel" name="phone" value="{{ old('phone', $profile?->phone) }}" autocomplete="tel"></label>
                    <label class="profile-editor-field"><span>Date of birth</span><input type="date" name="date_of_birth" value="{{ old('date_of_birth', $profile?->date_of_birth?->format('Y-m-d')) }}"></label>
                    <label class="profile-editor-field full"><span>Address</span><input name="address" value="{{ old('address', $profile?->address) }}" autocomplete="street-address"></label>
                </div>
            </section>

            <section class="profile-editor-card">
                <div class="profile-editor-section-heading">
                    <span class="profile-editor-icon amber" aria-hidden="true">⌂</span>
                    <div><h2>Education</h2><p>Update an existing entry or add one new entry.</p></div>
                </div>
                @foreach ($education as $index => $item)
                    <fieldset class="profile-editor-record">
                        <legend>Education {{ $index + 1 }}</legend>
                        <input type="hidden" name="education[{{ $index }}][id]" value="{{ $item->id }}">
                        <div class="profile-editor-grid">
                            <label class="profile-editor-field"><span>Degree or qualification</span><input name="education[{{ $index }}][degree]" value="{{ old("education.$index.degree", $item->degree) }}"></label>
                            <label class="profile-editor-field"><span>Institution</span><input name="education[{{ $index }}][institution]" value="{{ old("education.$index.institution", $item->institution) }}"></label>
                            <label class="profile-editor-field"><span>Field of study</span><input name="education[{{ $index }}][field_of_study]" value="{{ old("education.$index.field_of_study", $item->field_of_study) }}"></label>
                            <label class="profile-editor-field"><span>Graduation year</span><input type="number" min="1950" max="{{ now()->year + 10 }}" name="education[{{ $index }}][graduation_year]" value="{{ old("education.$index.graduation_year", $item->graduation_year) }}"></label>
                            <label class="profile-editor-field full"><span>Description</span><textarea name="education[{{ $index }}][description]" rows="3">{{ old("education.$index.description", $item->description) }}</textarea></label>
                        </div>
                        <label class="profile-editor-delete"><input type="checkbox" name="education[{{ $index }}][delete]" value="1"> Remove this education entry</label>
                    </fieldset>
                @endforeach
                @php($newEducationIndex = $education->count())
                <fieldset class="profile-editor-record">
                    <legend>Add education</legend>
                    <div class="profile-editor-grid">
                        <label class="profile-editor-field"><span>Degree or qualification</span><input name="education[{{ $newEducationIndex }}][degree]" value="{{ old("education.$newEducationIndex.degree") }}"></label>
                        <label class="profile-editor-field"><span>Institution</span><input name="education[{{ $newEducationIndex }}][institution]" value="{{ old("education.$newEducationIndex.institution") }}"></label>
                        <label class="profile-editor-field"><span>Field of study</span><input name="education[{{ $newEducationIndex }}][field_of_study]" value="{{ old("education.$newEducationIndex.field_of_study") }}"></label>
                        <label class="profile-editor-field"><span>Graduation year</span><input type="number" min="1950" max="{{ now()->year + 10 }}" name="education[{{ $newEducationIndex }}][graduation_year]" value="{{ old("education.$newEducationIndex.graduation_year") }}"></label>
                        <label class="profile-editor-field full"><span>Description</span><textarea name="education[{{ $newEducationIndex }}][description]" rows="3">{{ old("education.$newEducationIndex.description") }}</textarea></label>
                    </div>
                </fieldset>
            </section>

            <section class="profile-editor-card">
                <div class="profile-editor-section-heading">
                    <span class="profile-editor-icon blue" aria-hidden="true">▤</span>
                    <div><h2>Work experience</h2><p>Update an existing role or add one new role.</p></div>
                </div>
                @foreach ($workExperiences as $index => $experience)
                    <fieldset class="profile-editor-record">
                        <legend>Position {{ $index + 1 }}</legend>
                        <input type="hidden" name="work_experiences[{{ $index }}][id]" value="{{ $experience->id }}">
                        <div class="profile-editor-grid">
                            <label class="profile-editor-field"><span>Job title</span><input name="work_experiences[{{ $index }}][job_title]" value="{{ old("work_experiences.$index.job_title", $experience->job_title) }}"></label>
                            <label class="profile-editor-field"><span>Company</span><input name="work_experiences[{{ $index }}][company_name]" value="{{ old("work_experiences.$index.company_name", $experience->company_name) }}"></label>
                            <label class="profile-editor-field"><span>Employment type</span><input name="work_experiences[{{ $index }}][employment_type]" value="{{ old("work_experiences.$index.employment_type", $experience->employment_type) }}"></label>
                            <label class="profile-editor-field"><span>Start date</span><input type="date" name="work_experiences[{{ $index }}][start_date]" value="{{ old("work_experiences.$index.start_date", $experience->start_date?->format('Y-m-d')) }}"></label>
                            <label class="profile-editor-field"><span>End date</span><input type="date" name="work_experiences[{{ $index }}][end_date]" value="{{ old("work_experiences.$index.end_date", $experience->end_date?->format('Y-m-d')) }}"></label>
                            <label class="profile-editor-field full"><span>Description</span><textarea name="work_experiences[{{ $index }}][description]" rows="3">{{ old("work_experiences.$index.description", $experience->description) }}</textarea></label>
                        </div>
                        <input type="hidden" name="work_experiences[{{ $index }}][currently_working]" value="0">
                        <label class="profile-editor-checkbox"><input type="checkbox" name="work_experiences[{{ $index }}][currently_working]" value="1" @checked(old("work_experiences.$index.currently_working", $experience->currently_working))> I currently work here</label>
                        <label class="profile-editor-delete"><input type="checkbox" name="work_experiences[{{ $index }}][delete]" value="1"> Remove this work experience</label>
                    </fieldset>
                @endforeach
                @php($newExperienceIndex = $workExperiences->count())
                <fieldset class="profile-editor-record">
                    <legend>Add work experience</legend>
                    <div class="profile-editor-grid">
                        <label class="profile-editor-field"><span>Job title</span><input name="work_experiences[{{ $newExperienceIndex }}][job_title]" value="{{ old("work_experiences.$newExperienceIndex.job_title") }}"></label>
                        <label class="profile-editor-field"><span>Company</span><input name="work_experiences[{{ $newExperienceIndex }}][company_name]" value="{{ old("work_experiences.$newExperienceIndex.company_name") }}"></label>
                        <label class="profile-editor-field"><span>Employment type</span><input name="work_experiences[{{ $newExperienceIndex }}][employment_type]" value="{{ old("work_experiences.$newExperienceIndex.employment_type") }}"></label>
                        <label class="profile-editor-field"><span>Start date</span><input type="date" name="work_experiences[{{ $newExperienceIndex }}][start_date]" value="{{ old("work_experiences.$newExperienceIndex.start_date") }}"></label>
                        <label class="profile-editor-field"><span>End date</span><input type="date" name="work_experiences[{{ $newExperienceIndex }}][end_date]" value="{{ old("work_experiences.$newExperienceIndex.end_date") }}"></label>
                        <label class="profile-editor-field full"><span>Description</span><textarea name="work_experiences[{{ $newExperienceIndex }}][description]" rows="3">{{ old("work_experiences.$newExperienceIndex.description") }}</textarea></label>
                    </div>
                    <input type="hidden" name="work_experiences[{{ $newExperienceIndex }}][currently_working]" value="0">
                    <label class="profile-editor-checkbox"><input type="checkbox" name="work_experiences[{{ $newExperienceIndex }}][currently_working]" value="1" @checked(old("work_experiences.$newExperienceIndex.currently_working"))> I currently work here</label>
                </fieldset>
            </section>

            <section class="profile-editor-card">
                <div class="profile-editor-section-heading">
                    <span class="profile-editor-icon violet" aria-hidden="true">▤</span>
                    <div><h2>Professional profile</h2><p>Help employers quickly understand what you do and what you want next.</p></div>
                </div>
                <div class="profile-editor-grid">
                    <label class="profile-editor-field full"><span>Professional headline</span><input name="headline" value="{{ old('headline', $profile?->headline) }}" maxlength="160" placeholder="For example: Product designer with 5 years of experience"></label>
                    <label class="profile-editor-field"><span>Experience level</span><select name="experience_level"><option value="">Choose experience level</option>@foreach (['Entry level', 'Mid level', 'Senior', 'Executive'] as $level)<option value="{{ $level }}" @selected(old('experience_level', $profile?->experience_level) === $level)>{{ $level }}</option>@endforeach</select></label>
                    <label class="profile-editor-field"><span>Employment status</span><select name="employment_status"><option value="">Choose status</option>@foreach (['Actively looking', 'Open to opportunities', 'Employed', 'Not looking'] as $status)<option value="{{ $status }}" @selected(old('employment_status', $profile?->employment_status) === $status)>{{ $status }}</option>@endforeach</select></label>
                    <label class="profile-editor-field"><span>Preferred job type</span><select name="preferred_job_type"><option value="">Choose job type</option>@foreach (['Full-time', 'Part-time', 'Internship', 'Remote', 'Contract'] as $type)<option value="{{ $type }}" @selected(old('preferred_job_type', $profile?->preferred_job_type) === $type)>{{ $type }}</option>@endforeach</select></label>
                    <label class="profile-editor-field full"><span>About me</span><textarea name="about_me" rows="5" maxlength="5000" placeholder="Share a short introduction about your background and strengths.">{{ old('about_me', $profile?->about_me) }}</textarea></label>
                    <label class="profile-editor-field full"><span>Career objective</span><textarea name="career_objective" rows="4" maxlength="5000" placeholder="Describe the kind of role or opportunity you are looking for.">{{ old('career_objective', $profile?->career_objective) }}</textarea></label>
                </div>
            </section>

            <section class="profile-editor-card">
                <div class="profile-editor-section-heading">
                    <span class="profile-editor-icon green" aria-hidden="true">↗</span>
                    <div><h2>Links and documents</h2><p>Add professional links and keep your profile files up to date.</p></div>
                </div>
                <div class="profile-editor-grid">
                    <label class="profile-editor-field"><span>LinkedIn URL</span><input type="url" name="linkedin_url" value="{{ old('linkedin_url', $profile?->linkedin_url) }}" placeholder="https://"></label>
                    <label class="profile-editor-field"><span>GitHub URL</span><input type="url" name="github_url" value="{{ old('github_url', $profile?->github_url) }}" placeholder="https://"></label>
                    <label class="profile-editor-field full"><span>Portfolio URL</span><input type="url" name="portfolio_url" value="{{ old('portfolio_url', $profile?->portfolio_url) }}" placeholder="https://"></label>
                    <label class="profile-editor-field full"><span>Skills <small>Separate skills with commas</small></span><textarea name="skills" rows="3" placeholder="Communication, Project management, PHP">{{ old('skills', $skills) }}</textarea></label>
                    <label class="profile-editor-field"><span>Resume <small>PDF or Word · up to 10 MB</small></span><input type="file" name="resume" accept=".pdf,.doc,.docx">@if ($profile?->resume_path)<small>Current file: {{ $profile->resume_original_name ?? 'Resume uploaded' }}</small>@endif</label>
                </div>
            </section>

            <div class="profile-editor-actions">
                <a class="profile-cancel-button" href="{{ route('job-seeker.profile.show') }}">Cancel</a>
                <button class="profile-save-button" type="submit">Save profile</button>
            </div>
        </form>
    </div>
</x-app-layout>
