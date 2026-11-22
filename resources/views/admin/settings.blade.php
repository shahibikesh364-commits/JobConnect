@extends('admin.layout')

@section('title', 'Admin Settings')
@section('page_title', 'Settings')

@section('content')
    <section class="admin-page-intro">
        <div>
            <p class="admin-eyebrow">SYSTEM PREFERENCES</p>
            <h2>Account Settings</h2>
            <p>Manage your administrator account and security preferences.</p>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h3>Profile Management</h3>
            <span>Account</span>
        </div>

        <p>Update your personal information and account details.</p>

        <div class="admin-panel-actions">
            <a href="{{ route('profile.edit') }}">Manage profile</a>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h3>Password and Security</h3>
            <span>Security</span>
        </div>

        <p>Keep your administrator account secure by updating your password regularly.</p>

        <div class="admin-panel-actions">
            <a href="{{ route('password.request') }}">Password assistance</a>
        </div>
    </section>
@endsection