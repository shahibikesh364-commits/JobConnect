<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JobListing;
use App\Models\JobApplication;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // Platform user statistics
        $totalUsers = User::count();
        $totalEmployers = User::where('role', 'employer')->count();
        $totalJobSeekers = User::where('role', 'job_seeker')->count();
        $totalAdmins = User::where('role', 'admin')->count();

        // Job listing statistics
        $totalJobs = JobListing::count();
        $openJobs = JobListing::where('status', 'open')->count();
        $closedJobs = JobListing::where('status', 'closed')->count();

        // Application statistics
        $totalApplications = JobApplication::count();
        $pendingApplications = JobApplication::where('status', 'pending')->count();
        $reviewedApplications = JobApplication::whereIn('status', ['reviewed', 'shortlisted', 'accepted'])->count();

        // Growth & Activity this week (last 7 days)
        $newUsersThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        $newJobsThisWeek = JobListing::where('created_at', '>=', now()->subDays(7))->count();
        $newApplicationsThisWeek = JobApplication::where('created_at', '>=', now()->subDays(7))->count();

        // Ratios & Calculations
        $avgApplicationsPerJob = $totalJobs > 0 ? round($totalApplications / $totalJobs, 1) : 0;
        $activeEmployersWithJobs = User::where('role', 'employer')->has('jobListings')->count();

        // System & Platform Diagnostics
        $systemInfo = [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'env' => config('app.env', 'production'),
            'db' => config('database.default', 'sqlite'),
            'server_time' => now()->format('H:i:s T'),
            'status' => 'Operational',
        ];

        // Recent registrations
        $recentUsers = User::latest()
            ->take(6)
            ->get();

        // Recently created job listings
        $recentJobs = JobListing::with('employer')
            ->latest()
            ->take(5)
            ->get();

        // Recent applications
        $recentApplications = JobApplication::with([
            'applicant',
            'job',
        ])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalEmployers',
            'totalJobSeekers',
            'totalAdmins',
            'totalJobs',
            'totalApplications',
            'openJobs',
            'closedJobs',
            'pendingApplications',
            'reviewedApplications',
            'newUsersThisWeek',
            'newJobsThisWeek',
            'newApplicationsThisWeek',
            'avgApplicationsPerJob',
            'activeEmployersWithJobs',
            'systemInfo',
            'recentUsers',
            'recentJobs',
            'recentApplications'
        ));
    }
}
