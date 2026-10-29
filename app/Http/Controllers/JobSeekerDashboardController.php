<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JobSeekerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $applications = $user->applications()
            ->with('job')
            ->latest()
            ->get();

        $totalApplications = $applications->count();

        $statusCounts = [
            'submitted' => $applications->where('status', 'submitted')->count(),
            'reviewing' => $applications->where('status', 'reviewing')->count(),
            'shortlisted' => $applications->where('status', 'shortlisted')->count(),
            'rejected' => $applications->where('status', 'rejected')->count(),
        ];

        $recentApplications = $applications->take(5);

        return view('dashboard.index', compact(
            'totalApplications',
            'statusCounts',
            'recentApplications'
        ));
    }
}
