<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    @if (auth()->user()?->isJobSeeker())
        <div class="job-seeker-profile-shell">
            <x-job-seeker-sidebar active="profile" />
            <div class="job-seeker-profile-main">
                @isset($header)
                    <header class="job-seeker-profile-header">
                        {{ $header }}
                        <div class="profile-header-actions">
                            <x-notification-bell />
                            <x-theme-toggle />
                            <x-job-seeker-profile-menu />
                        </div>
                    </header>
                @endisset

                <main>
                    {{ $slot }}
                </main>

            </div>
        </div>
        @include('components.footer')
    @else
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
        @include('components.footer')
    @endif
</body>

</html>
