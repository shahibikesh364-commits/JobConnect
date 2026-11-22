<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\EmployerSetting;
use App\Models\JobListing;
use App\Models\JobApplication;
use App\Notifications\EmployerSettingsNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    EmployerSetting::where('auto_close_jobs', true)->pluck('employer_id')->each(function ($employerId) {
        JobListing::where('employer_id', $employerId)
            ->where('status', 'open')
            ->whereNotNull('application_deadline')
            ->whereDate('application_deadline', '<', today())
            ->update(['status' => 'closed', 'updated_at' => now()]);
    });

    JobListing::where('status', 'open')->whereDate('application_deadline', today()->addDay())
        ->with('employer')->get()->each(function (JobListing $job) {
            $job->employer?->notify(new EmployerSettingsNotification(
                'job_expiring',
                'Job post expiring soon',
                "The application deadline for {$job->title} is tomorrow.",
                ['job_id' => $job->id],
            ));
        });
})->dailyAt('08:00')->name('employer-settings-daily-jobs')->withoutOverlapping();

Schedule::call(function () {
    EmployerSetting::whereNotNull('notification_preferences')->with('employer')->get()
        ->each(function (EmployerSetting $settings) {
            $employer = $settings->employer;
            if (!$employer) return;
            $jobIds = JobListing::where('employer_id', $settings->employer_id)->pluck('id');
            $applicationCount = JobApplication::whereIn('job_listing_id', $jobIds)
                ->whereBetween('created_at', [now()->startOfWeek(), now()])->count();
            $employer->notify(new EmployerSettingsNotification(
                'weekly_summary',
                'Your weekly hiring summary',
                "You received {$applicationCount} application(s) this week.",
                ['application_count' => $applicationCount],
            ));
        });
})->weeklyOn(1, '08:15')->name('employer-settings-weekly-summary')->withoutOverlapping();
