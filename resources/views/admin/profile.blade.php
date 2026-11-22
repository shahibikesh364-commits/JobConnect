@extends('admin.layout')

@section('title', 'Admin Profile')
@section('page_title', 'Admin Profile')

@section('content')
    <section class="admin-page-intro">
        <div>
            <p class="admin-eyebrow">ACCOUNT INFORMATION</p>
            <h2>Administrator Profile</h2>
            <p>View your account information and administrator details.</p>
        </div>
    </section>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h3>Personal Information</h3>
            <span>Account details</span>
        </div>

        <div class="admin-profile-details">
            <div>
                <span>Full name</span>
                <strong>{{ $user->name }}</strong>
            </div>

            <div>
                <span>Email address</span>
                <strong>{{ $user->email }}</strong>
            </div>

            <div>
                <span>Account role</span>
                <strong>Administrator</strong>
            </div>

            <div>
                <span>Member since</span>
                <strong>{{ $user->created_at?->format('F d, Y') ?? 'N/A' }}</strong>
            </div>
        </div>

        <div class="admin-panel-actions">
            <a href="{{ route('profile.edit') }}">Edit account information</a>
            <a href="{{ route('admin.settings') }}">Account settings</a>
        </div>
    </section>
@endsection