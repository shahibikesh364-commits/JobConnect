@props(['name'])
@php($gradientId = 'jc-icon-' . \Illuminate\Support\Str::uuid())

<svg class="job-seeker-nav-icon-art" viewBox="0 0 32 32" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $gradientId }}" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#91baff" />
            <stop offset="1" stop-color="#5146e5" />
        </linearGradient>
        <linearGradient id="{{ $gradientId }}-side" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#6558eb" />
            <stop offset="1" stop-color="#342baf" />
        </linearGradient>
    </defs>

    @switch($name)
        @case('dashboard')
            <path d="m16 3 12 6.5v13L16 29 4 22.5v-13L16 3Z" fill="#dbe6ff" />
            <path d="m4 9.5 12 6.7v12.6L4 22.5v-13Z" fill="url(#{{ $gradientId }}-side)" />
            <path d="m16 16.2 12-6.7v13L16 28.8V16.2Z" fill="#5548cf" />
            <path d="m16 3 12 6.5-12 6.7L4 9.5 16 3Z" fill="url(#{{ $gradientId }})" />
            <path d="m10 10 2.3-1.3 2.3 1.3-2.3 1.3L10 10Zm5.1 2.8 2.3-1.3 2.3 1.3-2.3 1.3-2.3-1.3Z" fill="#fff" opacity=".9" />
            <path d="M8 14v5.8l2.1 1.2v-5.8L8 14Zm5.2 3 2.1 1.2V24l-2.1-1.2V17Z" fill="#dce6ff" opacity=".9" />
            @break
        @case('find')
            <circle cx="13" cy="12" r="8.5" fill="#dce8ff" />
            <path d="M18.7 18.2 27 26.5l-3 2.2-8.1-8.1 2.8-2.4Z" fill="#3932a6" />
            <path d="m17.1 17 2 2-7.1 8.3-3-2.4 8.1-7.9Z" fill="url(#{{ $gradientId }}-side)" />
            <circle cx="12.5" cy="11.5" r="6.4" fill="url(#{{ $gradientId }})" />
            <circle cx="12.5" cy="11.5" r="3.6" fill="#eef5ff" />
            <path d="m10.5 8.7 2-.8" stroke="#fff" stroke-linecap="round" stroke-width="1.3" />
            @break
        @case('applications')
            <path d="m7 5 17-1 3 3v18l-17 2-3-3V5Z" fill="#becdf8" />
            <path d="m5 7 17-2v19L5 26V7Z" fill="#4b43c7" />
            <path d="m8 4 17-1 3 3-17 2-6-1 3-3Z" fill="#b9d4ff" />
            <path d="M8 6.5 25 4.8v18.4L8 25V6.5Z" fill="url(#{{ $gradientId }})" />
            <path d="m11 11 10-.9M11 15l10-.9M11 19l7-.6" stroke="#fff" stroke-linecap="round" stroke-width="1.7" opacity=".95" />
            @break
        @case('saved-jobs')
            <path d="m16 3 3.7 7.5 8.3 1.2-6 5.9 1.4 8.3-7.4-3.9-7.4 3.9 1.4-8.3-6-5.9 8.3-1.2L16 3Z" fill="#382eae" transform="translate(1 1)" />
            <path d="m16 2 3.7 7.5 8.3 1.2-6 5.9 1.4 8.3-7.4-3.9-7.4 3.9 1.4-8.3-6-5.9 8.3-1.2L16 2Z" fill="url(#{{ $gradientId }})" />
            <path d="m16 7 2.3 4.7 5.2.8-3.8 3.7.9 5.2-4.6-2.5-4.6 2.5.9-5.2-3.8-3.7 5.2-.8L16 7Z" fill="#dce8ff" opacity=".85" />
            @break
        @case('companies')
            <path d="m4 10 13-7 11 6v15l-13 6-11-6V10Z" fill="#dee9ff" />
            <path d="m4 10 11 6v14L4 24V10Z" fill="#4138b7" />
            <path d="m15 16 13-7v15l-13 6V16Z" fill="#5548cf" />
            <path d="m4 10 13-7 11 6-13 7-11-6Z" fill="url(#{{ $gradientId }})" />
            <path d="m9 11 8-4.3 6 3.2-8 4.3-6-3.2Z" fill="#fff" opacity=".92" />
            <path d="M11 17v4l2 1v-4l-2-1Zm5 2v4l2 1v-4l-2-1Zm5-2v4l2 1v-4l-2-1Z" fill="#dfe8ff" />
            @break
        @case('interviews')
            <circle cx="15" cy="15" r="12" fill="#dce6ff" />
            <path d="M15 3a12 12 0 0 1 0 24V3Z" fill="#5548cf" />
            <circle cx="14" cy="14" r="9" fill="url(#{{ $gradientId }})" />
            <circle cx="14" cy="14" r="6.5" fill="#eef4ff" />
            <path d="M14 9v5l3.5 2" fill="none" stroke="#4e43d1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            <circle cx="14" cy="14" r="1.2" fill="#4e43d1" />
            @break
        @case('messages')
            <path d="M5 7h19a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H13l-6 4v-4H5a3 3 0 0 1-3-3V10a3 3 0 0 1 3-3Z" fill="#3b32ac" />
            <path d="M5 4h18a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3H12l-7 4v-4a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3Z" fill="url(#{{ $gradientId }})" />
            <path d="m5 7 9 7 9-7" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" />
            <path d="m5 17 6-5m12 5-6-5" fill="none" stroke="#dce8ff" stroke-linecap="round" stroke-width="1.5" />
            @break
        @case('profile')
            <path d="m16 3 12 6.5v13L16 29 4 22.5v-13L16 3Z" fill="#dbe6ff" />
            <path d="m4 9.5 12 6.7v12.6L4 22.5v-13Z" fill="#4339b6" />
            <path d="m16 16.2 12-6.7v13L16 28.8V16.2Z" fill="#5548cf" />
            <path d="m16 3 12 6.5-12 6.7L4 9.5 16 3Z" fill="url(#{{ $gradientId }})" />
            <circle cx="16" cy="9" r="2.5" fill="#fff" />
            <path d="M11.5 15.8c0-2 2-3.4 4.5-3.4s4.5 1.4 4.5 3.4v1.1h-9v-1.1Z" fill="#edf3ff" />
            @break
        @case('help')
            <path d="m16 3 12 6.5v13L16 29 4 22.5v-13L16 3Z" fill="#dbe6ff" />
            <path d="m4 9.5 12 6.7v12.6L4 22.5v-13Z" fill="#4339b6" />
            <path d="m16 16.2 12-6.7v13L16 28.8V16.2Z" fill="#5548cf" />
            <path d="m16 3 12 6.5-12 6.7L4 9.5 16 3Z" fill="url(#{{ $gradientId }})" />
            <path d="M13.2 8.5c.4-1.3 1.5-2.1 3-2.1 1.9 0 3.1 1.1 3.1 2.7 0 1.4-.8 2-2 2.8-.9.6-1.2 1-1.2 1.9" fill="none" stroke="#fff" stroke-linecap="round" stroke-width="1.8" />
            <circle cx="16.1" cy="16.4" r="1" fill="#fff" />
            @break
    @endswitch
</svg>
