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
        $totalUsers = User::count();

        $totalEmployers = User::where('role', 'employer')->count();

        $totalJobSeekers = User::where('role', 'job_seeker')->count();

        $totalJobs = JobListing::count();

        $totalApplications = JobApplication::count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalEmployers',
            'totalJobSeekers',
            'totalJobs',
            'totalApplications'
        ));
    }
}
