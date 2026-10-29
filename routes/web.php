<?php

use App\Http\Controllers\JobListingController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobSeekerDashboardController;
use App\Http\Controllers\EmployerJobController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('jobs.index');
});

// Public job listings
Route::get('/jobs', [JobListingController::class, 'index'])
    ->name('jobs.index');

Route::get('/jobs/{job}', [JobListingController::class, 'show'])
    ->name('jobs.show');

// Job seeker routes
Route::middleware(['auth', 'role:job_seeker'])->group(function () {
    Route::get('/dashboard', [JobSeekerDashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/jobs/{job}/apply', [JobApplicationController::class, 'start'])
        ->name('jobs.apply.start');

    Route::post('/jobs/{job}/apply', [JobApplicationController::class, 'store'])
        ->name('jobs.apply.store');

    Route::get('/my-applications', [JobApplicationController::class, 'mine'])
        ->name('applications.mine');
});

// Employer routes
Route::middleware(['auth', 'role:employer'])
    ->prefix('employer')
    ->name('employer.')
    ->group(function () {
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

        Route::delete('/jobs/{job}', [EmployerJobController::class, 'destroy'])
            ->name('jobs.destroy');

        Route::get('/jobs/{job}/applicants', [EmployerJobController::class, 'applicants'])
            ->name('jobs.applicants');

        Route::patch('/jobs/{job}/applications/{application}', [EmployerJobController::class, 'updateApplication'])
            ->name('applications.update');
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
        Route::get('/jobs', [AdminManagementController::class, 'jobs'])->name('jobs.index');
        Route::patch('/jobs/{job}/status', [AdminManagementController::class, 'updateJobStatus'])->name('jobs.status');
        Route::delete('/jobs/{job}', [AdminManagementController::class, 'destroyJob'])->name('jobs.destroy');
        Route::get('/applications', [AdminManagementController::class, 'applications'])->name('applications.index');
        Route::patch('/applications/{application}/status', [AdminManagementController::class, 'updateApplicationStatus'])->name('applications.status');
    });

// Breeze authentication routes
require __DIR__ . '/auth.php';
