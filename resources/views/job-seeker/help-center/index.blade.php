<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-favicon />
    <title>Help Center - JobConnect</title>

    @vite(['resources/css/job-seeker-sidebar.css', 'resources/css/notifications.css', 'resources/css/help-center.css'])
    @vite('resources/js/app.js')
</head>

<body class="help-center-page">

    <div class="help-center-shell">
        <x-job-seeker-sidebar active="help-desk" />

        <main class="help-center-main">
            <header class="help-center-topbar">
                <div>
                    <p>JOB SEEKER PORTAL</p>
                    <span>Help & Support</span>
                </div>

                <div class="help-center-actions">
                    <x-notification-bell />
                    <x-theme-toggle />
                    <x-job-seeker-profile-menu />
                </div>
            </header>

            <div class="help-center-content">

                <section class="help-hero">
                    <span class="help-eyebrow">JOBCONNECT SUPPORT</span>
                    <h1>How can we help you?</h1>
                    <p>Find answers to common questions and get the support you need.</p>

                    <div class="help-search">
                        <span>⌕</span>
                        <input type="search" id="faqSearch" placeholder="Search for help..."
                            aria-label="Search frequently asked questions">
                    </div>

                    <div class="help-hero-note">
                        <span class="help-live-dot"></span>
                        We're here to make your job search easier.
                    </div>
                </section>

                <section class="help-topics">
                    <div class="help-section-heading">
                        <div>
                            <span class="help-kicker">HELP RESOURCES</span>
                            <h2>Browse help topics</h2>
                            <p>Choose a topic to find the information you need.</p>
                        </div>
                    </div>

                    <div class="help-topic-grid">
                        <button class="help-topic active" data-category="all" aria-pressed="true">
                            <span class="help-topic-icon">✦</span>
                            <strong>All topics</strong>
                            <small>Browse everything</small>
                        </button>

                        <button class="help-topic" data-category="account" aria-pressed="false">
                            <span class="help-topic-icon">♙</span>
                            <strong>Account & Profile</strong>
                            <small>Manage your account</small>
                        </button>

                        <button class="help-topic" data-category="jobs" aria-pressed="false">
                            <span class="help-topic-icon">▣</span>
                            <strong>Finding Jobs</strong>
                            <small>Explore opportunities</small>
                        </button>

                        <button class="help-topic" data-category="applications" aria-pressed="false">
                            <span class="help-topic-icon">▤</span>
                            <strong>Applications</strong>
                            <small>Track your progress</small>
                        </button>

                        <button class="help-topic" data-category="security" aria-pressed="false">
                            <span class="help-topic-icon">◇</span>
                            <strong>Security</strong>
                            <small>Keep your account safe</small>
                        </button>
                    </div>
                </section>

                <section class="help-faq-section">
                    <div class="help-section-heading">
                        <div>
                            <span class="help-kicker">QUICK ANSWERS</span>
                            <h2>Frequently asked questions</h2>
                            <p>Find quick answers to common questions.</p>
                        </div>
                        <span class="help-faq-count" id="faqCount" aria-live="polite">8 questions</span>
                    </div>

                    <div class="help-faq-list" id="faqList">

                        <article class="help-faq-item" data-category="account">
                            <button class="help-faq-question">
                                <span>How do I update my profile?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Open My Profile from your dashboard, select Edit Profile, update your information,
                                    and save your changes.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="account">
                            <button class="help-faq-question">
                                <span>What should I do if I forget my password?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Go to the login page and select Forgot Password. Follow the instructions sent to your
                                    registered email address.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="jobs">
                            <button class="help-faq-question">
                                <span>How do I find available jobs?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Visit Browse Jobs to explore available opportunities and open a listing to view its
                                    details.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="jobs">
                            <button class="help-faq-question">
                                <span>Can I save a job for later?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Yes. Use the Save Job option on a listing and visit Saved Jobs from your dashboard to
                                    find it later.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="applications">
                            <button class="help-faq-question">
                                <span>How do I apply for a job?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Open a job listing, review its requirements, and use the Apply Now option to submit
                                    your application.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="applications">
                            <button class="help-faq-question">
                                <span>Where can I check my application status?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Open My Applications from your dashboard to see the current status of your submitted
                                    applications.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="security">
                            <button class="help-faq-question">
                                <span>How can I keep my account secure?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Use a strong, unique password and never share your login credentials with anyone.</p>
                            </div>
                        </article>

                        <article class="help-faq-item" data-category="security">
                            <button class="help-faq-question">
                                <span>Can I change my account password?</span>
                                <span class="faq-toggle">+</span>
                            </button>
                            <div class="help-faq-answer">
                                <p>Yes. Visit Account Settings and use the password update section to change your
                                    password.</p>
                            </div>
                        </article>

                    </div>

                    <div class="help-no-results" id="noFaqResults" hidden>
                        <span>⌕</span>
                        <h3>No matching questions found</h3>
                        <p>Try another search term or choose a different category.</p>
                    </div>
                </section>

                <section class="help-contact-section">
                    <div>
                        <span class="help-kicker">NEED MORE HELP?</span>
                        <h2>Still have questions?</h2>
                        <p>Our support team is here to help you with your JobConnect experience.</p>
                    </div>

                    <a href="#support-contact" class="help-contact-button">
                        Contact Support <span aria-hidden="true">↓</span>
                    </a>
                </section>

                <section class="help-support-placeholder" id="support-contact">
                    <div>
                        <span class="help-kicker">CONTACT US</span>
                        <h2>Get in touch with support</h2>
                        <p>Tell us what you need help with. We’ll reply to {{ auth()->user()->email }}.</p>
                    </div>
                    @if (session('support_submitted'))
                        <div class="help-support-feedback is-success" role="status">
                            {{ session('support_submitted') }}</div>
                    @endif
                    @error('support')
                        <div class="help-support-feedback is-error" role="alert">{{ $message }}</div>
                    @enderror

                    <form class="help-support-form" method="POST" action="{{ route('job-seeker.support.store') }}">
                        @csrf
                        <div class="help-support-form-grid">
                            <label>
                                Topic
                                <select name="topic" required>
                                    <option value="">Choose a topic</option>
                                    @foreach (['account' => 'Account & profile', 'jobs' => 'Finding jobs', 'applications' => 'Applications', 'security' => 'Security', 'other' => 'Other'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('topic') === $value)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('topic')
                                    <small class="help-field-error">{{ $message }}</small>
                                @enderror
                            </label>
                            <label>
                                Subject
                                <input type="text" name="subject" value="{{ old('subject') }}" maxlength="120"
                                    required placeholder="Briefly describe the issue">
                                @error('subject')
                                    <small class="help-field-error">{{ $message }}</small>
                                @enderror
                            </label>
                        </div>
                        <label>
                            Message
                            <textarea name="message" rows="5" minlength="10" maxlength="3000" required
                                placeholder="Include the details that will help us resolve your issue.">{{ old('message') }}</textarea>
                            @error('message')
                                <small class="help-field-error">{{ $message }}</small>
                            @enderror
                        </label>
                        <button class="help-support-submit" type="submit">Send support request <span
                                aria-hidden="true">→</span></button>
                    </form>
                </section>

            </div>
        </main>
    </div>
    @include('components.footer')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('faqSearch');
            const categoryButtons = document.querySelectorAll('.help-topic');
            const faqItems = document.querySelectorAll('.help-faq-item');
            const faqCount = document.getElementById('faqCount');
            const noResults = document.getElementById('noFaqResults');
            const faqSection = document.querySelector('.help-faq-section');
            const categoryLabels = new Map(Array.from(categoryButtons, function(button) {
                return [button.dataset.category, button.querySelector('strong').textContent];
            }));

            let selectedCategory = 'all';

            function filterFAQs() {
                const search = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                faqItems.forEach(function(item) {
                    const question = item.querySelector('.help-faq-question').textContent;
                    const answer = item.querySelector('.help-faq-answer').textContent;
                    const category = categoryLabels.get(item.dataset.category) || '';
                    const searchableText = `${question} ${answer} ${category}`.toLocaleLowerCase();

                    const matchesCategory =
                        selectedCategory === 'all' ||
                        item.dataset.category === selectedCategory;

                    const matchesSearch = searchableText.includes(search);

                    const visible = matchesCategory && matchesSearch;

                    item.hidden = !visible;

                    if (visible) {
                        visibleCount++;
                    }
                });

                faqCount.textContent = visibleCount + (visibleCount === 1 ? ' question' : ' questions');
                noResults.hidden = visibleCount !== 0;
            }

            searchInput.addEventListener('input', filterFAQs);
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    searchInput.value = '';
                    filterFAQs();
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    faqSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });

            categoryButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    selectedCategory = button.dataset.category;

                    categoryButtons.forEach(function(item) {
                        item.classList.toggle('active', item === button);
                        item.setAttribute('aria-pressed', item === button ? 'true' :
                            'false');
                    });

                    filterFAQs();
                });
            });

            document.querySelectorAll('.help-faq-question').forEach(function(button) {
                button.addEventListener('click', function() {
                    const item = button.closest('.help-faq-item');
                    const isOpen = item.classList.contains('open');

                    document.querySelectorAll('.help-faq-item').forEach(function(faq) {
                        faq.classList.remove('open');
                    });

                    if (!isOpen) {
                        item.classList.add('open');
                    }
                });
            });
        });
    </script>

</body>

</html>
