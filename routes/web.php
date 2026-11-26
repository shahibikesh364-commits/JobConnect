<?php

use App\Http\Controllers\JobListingController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobSeekerDashboardController;
use App\Http\Controllers\JobSeekerFeatureController;
use App\Http\Controllers\JobSeekerCompanyProfileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployerJobController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\SavedJobController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployerAnalyticsController;
use App\Http\Controllers\EmployerSettingsController;

Route::get('/', function () {
    if (request()->user()?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('jobs.index');
});

// Public job listings
Route::get('/jobs', [JobListingController::class, 'index'])
    ->name('jobs.index');

Route::get('/jobs/{job}', [JobListingController::class, 'show'])
    ->name('jobs.show');

// Authenticated routes
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/account-settings', [ProfileController::class, 'settings'])->name('account.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->whereUuid('notification')->name('notifications.read');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])
        ->whereUuid('notification')->name('notifications.open');

    // Messages (job seekers and employers)
    Route::get('/messages', [MessageController::class, 'index'])
        ->name('messages.index');

    // Job Seeker direct conversations
    Route::middleware('role:job_seeker')->group(function () {

        Route::get('/messages/new', [MessageController::class, 'findEmployers'])
            ->name('messages.new');

        Route::post('/messages/direct', [MessageController::class, 'startDirectForJobSeeker'])
            ->middleware('throttle:10,1')
            ->name('messages.start-direct');
    });

    Route::get('/messages/{conversation}', [MessageController::class, 'show'])
        ->name('messages.show');

    Route::post('/messages/{conversation}', [MessageController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('messages.store');

    Route::patch('/messages/{conversation}/messages/{message}', [MessageController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('messages.update');

    Route::delete('/messages/{conversation}/messages/{message}', [MessageController::class, 'destroy'])
        ->name('messages.destroy');

    Route::patch('/messages/{conversation}/archive', [MessageController::class, 'archive'])
        ->name('messages.archive');
});

// Job seeker routes
Route::middleware(['auth', 'role:job_seeker'])->group(function () {

    Route::get('/companies/{employer}', [JobSeekerCompanyProfileController::class, 'show'])
        ->whereNumber('employer')
        ->name('job-seeker.companies.show');

    Route::get('/dashboard', [JobSeekerDashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/features/{feature}', [JobSeekerFeatureController::class, 'show'])
        ->whereIn('feature', ['notifications', 'saved-jobs', 'companies', 'interviews', 'messages', 'settings', 'help-desk'])
        ->name('job-seeker.features.show');

    Route::get('/jobs/{job}/apply', [JobApplicationController::class, 'start'])
        ->name('jobs.apply.start');

    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])
        ->name('jobs.apply.store');

    Route::get('/my-applications', [JobApplicationController::class, 'mine'])
        ->name('applications.mine');

    Route::get('/saved-jobs', [SavedJobController::class, 'index'])
        ->name('saved-jobs.index');

    Route::post('/jobs/{job}/save', [SavedJobController::class, 'store'])
        ->name('jobs.save');

    Route::delete('/saved-jobs/{savedJob}', [SavedJobController::class, 'destroy'])
        ->name('saved-jobs.destroy');

    // Job seeker profile
    Route::get('/my-profile', [\App\Http\Controllers\JobSeeker\ProfileController::class, 'show'])
        ->name('job-seeker.profile.show');

    Route::get('/my-profile/edit', [\App\Http\Controllers\JobSeeker\ProfileController::class, 'edit'])
        ->name('job-seeker.profile.edit');

    Route::put('/my-profile', [\App\Http\Controllers\JobSeeker\ProfileController::class, 'update'])
        ->name('job-seeker.profile.update');

    // Help Center
    Route::post('/help-center/contact', [JobSeekerFeatureController::class, 'contactSupport'])
        ->name('job-seeker.support.store');

    // Start a conversation with an employer
    Route::get('/jobs/{job}/messages/start', [MessageController::class, 'start'])
        ->name('messages.start');
});

// Employer routes
Route::middleware(['auth', 'role:employer'])
    ->prefix('employer')
    ->name('employer.')
    ->group(function () {

        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::get('/company-profile', [JobSeekerCompanyProfileController::class, 'preview'])
            ->name('company-profile.preview');

        Route::get('/team/invitations/{token}/accept', [EmployerSettingsController::class, 'acceptInvitation'])
            ->name('team.accept');

        Route::get('/dashboard', [EmployerJobController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/analytics', [EmployerAnalyticsController::class, 'index'])
            ->name('analytics');

        Route::get('/applicants', [EmployerJobController::class, 'applicantsIndex'])
            ->name('applicants.index');

        Route::get('/jobs', [EmployerJobController::class, 'index'])
            ->name('jobs.index');

        Route::get('/jobs/create', [EmployerJobController::class, 'create'])
            ->name('jobs.create');

        Route::post('/jobs', [EmployerJobController::class, 'store'])
            ->name('jobs.store');

        Route::get('/jobs/{job}/edit', [EmployerJobController::class, 'edit'])
            ->name('jobs.edit');

        Route::put('/jobs/{job}', [EmployerJobController::class, 'update'])
            ->name('jobs.update');

        Route::patch('/jobs/{job}/status', [EmployerJobController::class, 'updateStatus'])
            ->name('jobs.status');

        Route::delete('/jobs/{job}', [EmployerJobController::class, 'destroy'])
            ->name('jobs.destroy');

        Route::get('/jobs/{job}/applicants', [EmployerJobController::class, 'applicants'])
            ->name('jobs.applicants');

        Route::patch('/jobs/{job}/applications/{application}', [EmployerJobController::class, 'updateApplication'])
            ->name('applications.update');

        Route::get('/jobs/{job}/applicants/{applicant}/messages/start', [MessageController::class, 'startForApplicant'])
            ->name('messages.start-for-applicant');

        Route::get('/messages/new', [MessageController::class, 'createDirect'])
            ->name('messages.new');

        Route::post('/messages/direct', [MessageController::class, 'startDirect'])
            ->middleware('throttle:10,1')
            ->name('messages.start-direct');
    });

// Session-authenticated employer settings API. Kept in the web route stack for CSRF protection.
Route::middleware(['auth', 'role:employer'])
    ->prefix('api/employer')
    ->name('api.employer.')
    ->group(function () {
        Route::get('/settings', [EmployerSettingsController::class, 'show'])->name('settings.show');
        Route::put('/settings/company', [EmployerSettingsController::class, 'updateCompany'])->name('settings.company');
        Route::put('/settings/account', [EmployerSettingsController::class, 'updateAccount'])->name('settings.account');
        Route::put('/settings/password', [EmployerSettingsController::class, 'updatePassword'])->middleware('throttle:6,1')->name('settings.password');
        Route::put('/settings/notifications', [EmployerSettingsController::class, 'updateNotifications'])->name('settings.notifications');
        Route::put('/settings/hiring-preferences', [EmployerSettingsController::class, 'updateHiringPreferences'])->name('settings.hiring');
        Route::put('/settings/privacy', [EmployerSettingsController::class, 'updatePrivacy'])->name('settings.privacy');
        Route::post('/settings/logo', [EmployerSettingsController::class, 'uploadLogo'])->name('settings.logo');
        Route::delete('/settings/logo', [EmployerSettingsController::class, 'deleteLogo'])->name('settings.logo.delete');
        Route::get('/settings/sessions', [EmployerSettingsController::class, 'sessions'])->name('settings.sessions');
        Route::post('/settings/sessions/logout-others', [EmployerSettingsController::class, 'logoutOtherSessions'])->name('settings.sessions.logout-others');
        Route::get('/settings/export', [EmployerSettingsController::class, 'exportData'])->name('settings.export');
        Route::post('/settings/deactivate', [EmployerSettingsController::class, 'deactivateAccount'])->name('settings.deactivate');
        Route::get('/team', [EmployerSettingsController::class, 'teamIndex'])->name('team.index');
        Route::post('/team', [EmployerSettingsController::class, 'inviteTeamMember'])->name('team.invite');
        Route::patch('/team/{member}', [EmployerSettingsController::class, 'updateTeamMember'])->name('team.update');
        Route::delete('/team/{member}', [EmployerSettingsController::class, 'removeTeamMember'])->name('team.remove');
        Route::post('/team/{member}/resend', [EmployerSettingsController::class, 'resendInvitation'])->name('team.resend');
        Route::delete('/account', [EmployerSettingsController::class, 'deleteAccount'])->name('account.delete');
    });

// Admin Panel routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/users', [AdminManagementController::class, 'users'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminManagementController::class, 'updateUserRole'])->name('users.role');
        Route::delete('/users/{user}', [AdminManagementController::class, 'destroyUser'])->name('users.destroy');

        Route::get('/employers', [AdminManagementController::class, 'employers'])->name('employers.index');
        Route::get('/employers/{employer}/company-profile', [JobSeekerCompanyProfileController::class, 'show'])
            ->whereNumber('employer')
            ->name('employers.profile');

        Route::get('/jobs', [AdminManagementController::class, 'jobs'])->name('jobs.index');
        Route::patch('/jobs/{job}/status', [AdminManagementController::class, 'updateJobStatus'])->name('jobs.status');
        Route::delete('/jobs/{job}', [AdminManagementController::class, 'destroyJob'])->name('jobs.destroy');

        Route::get('/applications', [AdminManagementController::class, 'applications'])->name('applications.index');
        Route::patch('/applications/{application}/status', [AdminManagementController::class, 'updateApplicationStatus'])->name('applications.status');

        Route::get('/job-seekers', [AdminManagementController::class, 'jobSeekers'])
            ->name('job-seekers.index');

        Route::get('/profile', [ProfileController::class, 'edit'])
            ->name('profile');

        Route::get('/settings', [ProfileController::class, 'settings'])
            ->name('settings');

    });

// Breeze authentication routes
require __DIR__ . '/auth.php';
