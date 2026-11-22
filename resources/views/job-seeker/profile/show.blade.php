<x-app-layout>
    <x-slot name="title">My Profile</x-slot>
    <x-slot name="header">
        <div class="profile-header-main">
            <div class="profile-page-heading">
                <p class="profile-label">MY CAREER</p>
                <h1>Profile overview</h1>
                <p>Your experience, skills, and professional details in one place.</p>
            </div>
        </div>
    </x-slot>

    <div class="profile-page profile-overview-page">
        <section class="candidate-profile-hero" aria-labelledby="candidate-name">
            <div class="candidate-profile-hero-glow" aria-hidden="true"></div>
            <div class="candidate-identity">
                <div class="candidate-avatar-group">
                    <div class="candidate-avatar">
                        @if ($profile?->profile_photo)
                            <img src="{{ asset('storage/' . $profile->profile_photo) }}" alt="Profile photo of {{ $user->name }}">
                        @else
                            <span aria-hidden="true">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                </div>
                <div class="candidate-identity-copy">
                    <span class="candidate-eyebrow"><span aria-hidden="true">✦</span> JOB SEEKER PROFILE</span>
                    <h2 id="candidate-name">{{ $user->name }}</h2>
                    <p class="candidate-headline">{{ $profile?->headline ?: 'Add a professional headline to introduce yourself.' }}</p>
                    <div class="candidate-identity-meta">
                        <span><span aria-hidden="true">✉</span> {{ $user->email }}</span>
                        @if ($profile?->address)
                            <span><span aria-hidden="true">⌖</span> {{ $profile->address }}</span>
                        @endif
                    </div>
                    <div class="candidate-tags">
                        @if ($profile?->employment_status)
                            @php($isSeeking = in_array(mb_strtolower($profile->employment_status), ['actively looking', 'open to opportunities'], true))
                            <span class="candidate-tag {{ $isSeeking ? 'available' : '' }}">@if ($isSeeking)<i aria-hidden="true"></i>@endif{{ $profile->employment_status }}</span>
                        @endif
                        @if ($profile?->preferred_job_type)
                            <span class="candidate-tag">{{ $profile->preferred_job_type }}</span>
                        @endif
                        @if ($profile?->experience_level)
                            <span class="candidate-tag">{{ $profile->experience_level }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <a class="candidate-hero-edit" href="{{ route('job-seeker.profile.edit') }}">
                <span aria-hidden="true">✎</span><span>Edit profile</span>
            </a>
        </section>

        <section class="profile-snapshot" aria-label="Profile snapshot">
            <article class="snapshot-card">
                <span class="snapshot-icon violet" aria-hidden="true">▤</span>
                <div><small>Experience</small><strong>{{ $workExperiences->count() }}</strong><p>{{ \Illuminate\Support\Str::plural('role', $workExperiences->count()) }} listed</p></div>
            </article>
            <article class="snapshot-card">
                <span class="snapshot-icon blue" aria-hidden="true">⌂</span>
                <div><small>Education</small><strong>{{ $education->count() }}</strong><p>{{ \Illuminate\Support\Str::plural('entry', $education->count()) }} listed</p></div>
            </article>
            <article class="snapshot-card">
                <span class="snapshot-icon green" aria-hidden="true">✧</span>
                <div><small>Skills</small><strong>{{ $skills->count() }}</strong><p>{{ \Illuminate\Support\Str::plural('skill', $skills->count()) }} added</p></div>
            </article>
        </section>

        <div class="candidate-profile-grid">
            <div class="candidate-profile-main">
                <section class="candidate-panel" aria-labelledby="about-heading">
                    <div class="candidate-panel-heading">
                        <div><span class="candidate-section-kicker">INTRODUCTION</span><h2 id="about-heading">About me</h2></div>
                    </div>
                    @if ($profile?->about_me)
                        <p class="candidate-prose">{{ $profile->about_me }}</p>
                    @else
                        <div class="candidate-empty-inline"><span aria-hidden="true">✦</span><div><strong>Tell your story</strong><p>Add a short introduction about your background, strengths, and the work you enjoy.</p></div></div>
                    @endif
                    @if ($profile?->career_objective)
                        <div class="candidate-objective"><span class="candidate-section-kicker">CAREER OBJECTIVE</span><p>{{ $profile->career_objective }}</p></div>
                    @endif
                </section>

                <section class="candidate-panel" aria-labelledby="experience-heading">
                    <div class="candidate-panel-heading">
                        <div><span class="candidate-section-kicker">CAREER HISTORY</span><h2 id="experience-heading">Work experience</h2></div>
                    </div>
                    @forelse ($workExperiences as $experience)
                        <article class="candidate-timeline-item">
                            <span class="candidate-timeline-mark" aria-hidden="true">▤</span>
                            <div class="candidate-timeline-copy">
                                <div class="candidate-timeline-title"><div><h3>{{ $experience->job_title }}</h3><p>{{ $experience->company_name }}</p></div><span class="candidate-date-range">{{ $experience->start_date?->format('M Y') ?? 'Date not set' }} – {{ $experience->currently_working ? 'Present' : ($experience->end_date?->format('M Y') ?? 'Present') }}</span></div>
                                @if ($experience->employment_type)<span class="candidate-detail-pill">{{ $experience->employment_type }}</span>@endif
                                @if ($experience->description)<p class="candidate-prose timeline-description">{{ $experience->description }}</p>@endif
                            </div>
                        </article>
                    @empty
                        <div class="candidate-empty-state"><span class="candidate-empty-icon" aria-hidden="true">↗</span><div><strong>Your experience goes here</strong><p>Add roles, responsibilities, and achievements to give employers more context.</p></div><a href="{{ route('job-seeker.profile.edit') }}">Add a role <span aria-hidden="true">→</span></a></div>
                    @endforelse
                </section>

                <section class="candidate-panel" aria-labelledby="education-heading">
                    <div class="candidate-panel-heading">
                        <div><span class="candidate-section-kicker">LEARNING</span><h2 id="education-heading">Education</h2></div>
                    </div>
                    @forelse ($education as $item)
                        <article class="candidate-timeline-item">
                            <span class="candidate-timeline-mark education" aria-hidden="true">⌂</span>
                            <div class="candidate-timeline-copy">
                                <div class="candidate-timeline-title"><div><h3>{{ $item->degree }}</h3><p>{{ $item->institution }}</p></div>@if ($item->graduation_year)<span class="candidate-date-range">{{ $item->graduation_year }}</span>@endif</div>
                                @if ($item->field_of_study)<span class="candidate-detail-pill">{{ $item->field_of_study }}</span>@endif
                                @if ($item->description)<p class="candidate-prose timeline-description">{{ $item->description }}</p>@endif
                            </div>
                        </article>
                    @empty
                        <div class="candidate-empty-state"><span class="candidate-empty-icon" aria-hidden="true">⌂</span><div><strong>Add your academic background</strong><p>Include your qualifications and field of study.</p></div><a href="{{ route('job-seeker.profile.edit') }}">Add education <span aria-hidden="true">→</span></a></div>
                    @endforelse
                </section>

                <section class="candidate-panel" aria-labelledby="skills-heading">
                    <div class="candidate-panel-heading">
                        <div><span class="candidate-section-kicker">WHAT YOU DO WELL</span><h2 id="skills-heading">Skills</h2></div>
                    </div>
                    @if ($skills->isNotEmpty())
                        <div class="candidate-skill-list">
                            @foreach ($skills as $skill)<span class="candidate-skill">{{ $skill->name }}</span>@endforeach
                        </div>
                    @else
                        <div class="candidate-empty-inline"><span aria-hidden="true">✧</span><div><strong>Showcase your strengths</strong><p>Add the skills you want employers to notice.</p></div></div>
                    @endif
                </section>

                <section class="candidate-panel candidate-resume-panel" aria-labelledby="resume-heading">
                    <div class="candidate-panel-heading compact"><div><span class="candidate-section-kicker">APPLICATION READY</span><h2 id="resume-heading">Resume / CV</h2></div></div>
                    @if ($profile?->resume_path)
                        <div class="resume-file-row"><span class="resume-file-icon" aria-hidden="true">{{ strtoupper(pathinfo($profile->resume_original_name ?? $profile->resume_path, PATHINFO_EXTENSION) ?: 'FILE') }}</span><div><strong>{{ $profile->resume_original_name ?? 'Resume file' }}</strong><small>Available on your profile</small></div></div>
                        <a class="candidate-panel-footer-link" href="{{ asset('storage/' . $profile->resume_path) }}" target="_blank" rel="noopener noreferrer">Open resume <span aria-hidden="true">↗</span></a>
                    @else
                        <div class="candidate-empty-inline resume-empty"><span aria-hidden="true">⇧</span><div><strong>No resume uploaded</strong><p>Add a current resume to make applications easier.</p></div></div>
                    @endif
                </section>

            </div>

            <aside class="candidate-profile-aside" aria-label="Profile details">
                <section class="candidate-completion-panel">
                    <div class="completion-panel-top"><span class="completion-panel-icon" aria-hidden="true">◉</span><span class="completion-panel-caption">PROFILE STRENGTH</span></div>
                    <div class="completion-panel-score">{{ $completion }}<span>%</span></div>
                    <p>{{ $completion >= 100 ? 'Your profile is filled out and ready to represent you.' : 'Complete more details to give employers a fuller picture of you.' }}</p>
                    <progress max="100" value="{{ $completion }}" aria-label="Profile completion">{{ $completion }}%</progress>
                </section>

                <section class="candidate-panel candidate-contact-panel" aria-labelledby="contact-heading">
                    <div class="candidate-panel-heading compact"><div><span class="candidate-section-kicker">GET IN TOUCH</span><h2 id="contact-heading">Contact</h2></div></div>
                    <div class="candidate-contact-list">
                        <div><span class="contact-symbol" aria-hidden="true">✉</span><span><small>Email</small><strong>{{ $user->email }}</strong></span></div>
                        <div><span class="contact-symbol phone" aria-hidden="true">⌕</span><span><small>Phone</small><strong>{{ $profile?->phone ?: 'Add a phone number' }}</strong></span></div>
                        <div><span class="contact-symbol location" aria-hidden="true">⌖</span><span><small>Location</small><strong>{{ $profile?->address ?: 'Add your location' }}</strong></span></div>
                        @if ($profile?->date_of_birth)
                            <div><span class="contact-symbol birthday" aria-hidden="true">✦</span><span><small>Date of birth</small><strong>{{ $profile->date_of_birth->format('M j, Y') }}</strong></span></div>
                        @endif
                    </div>
                </section>

                <section class="candidate-panel" aria-labelledby="links-heading">
                    <div class="candidate-panel-heading compact"><div><span class="candidate-section-kicker">AROUND THE WEB</span><h2 id="links-heading">Professional links</h2></div></div>
                    <div class="candidate-social-links">
                        @foreach ([['LinkedIn', $profile?->linkedin_url, 'in'], ['GitHub', $profile?->github_url, 'gh'], ['Portfolio', $profile?->portfolio_url, '↗']] as [$label, $url, $symbol])
                            @if ($url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"><span>{{ $symbol }}</span><strong>{{ $label }}</strong><span class="external-arrow" aria-hidden="true">↗</span></a>
                            @else
                                <div class="candidate-social-missing"><span>{{ $symbol }}</span><strong>{{ $label }}</strong><small>Not added</small></div>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="candidate-panel candidate-preferences-panel" aria-labelledby="preferences-heading">
                    <div class="candidate-panel-heading">
                        <div><span class="candidate-section-kicker">YOUR NEXT MOVE</span><h2 id="preferences-heading">Career preferences</h2></div>
                        <a href="{{ route('job-seeker.profile.edit') }}">Edit <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="candidate-preference-list">
                        <div><small>Looking for</small><strong>{{ $profile?->employment_status ?: 'Add your job search status' }}</strong></div>
                        <div><small>Preferred work type</small><strong>{{ $profile?->preferred_job_type ?: 'Add a preferred job type' }}</strong></div>
                        <div><small>Experience level</small><strong>{{ $profile?->experience_level ?: 'Add your experience level' }}</strong></div>
                    </div>
                </section>

            </aside>
        </div>
    </div>
</x-app-layout>
