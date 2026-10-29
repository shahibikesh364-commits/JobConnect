<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | JobConnect</title>
    @vite('resources/css/admin.css')
</head>
<body class="admin-page">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}"><x-jobconnect-logo class="admin-brand-logo" /><strong>JobConnect</strong></a>
            <p class="nav-label">ADMINISTRATION</p>
            <nav>
                <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>▦</span> Overview</a>
                <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><span>♙</span> Users</a>
                <a class="{{ request()->routeIs('admin.employers.*') ? 'active' : '' }}" href="{{ route('admin.employers.index') }}"><span>▣</span> Employers</a>
                <a class="{{ request()->routeIs('admin.jobs.*') ? 'active' : '' }}" href="{{ route('admin.jobs.index') }}"><span>▤</span> Job listings</a>
                <a class="{{ request()->routeIs('admin.applications.*') ? 'active' : '' }}" href="{{ route('admin.applications.index') }}"><span>✓</span> Applications</a>
            </nav>
            <div class="admin-sidebar-bottom">
                <a href="{{ route('jobs.index') }}">← View public site</a>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <div><p class="topbar-label">JOBCONNECT ADMIN</p><h1>@yield('page_title', 'Overview')</h1></div>
                <div class="admin-user">
                    <span class="admin-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log out</button></form>
                </div>
            </header>
            <main class="admin-content">
                @if (session('success'))<div class="admin-alert success">{{ session('success') }}</div>@endif
                @if ($errors->any())<div class="admin-alert error">{{ $errors->first() }}</div>@endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
