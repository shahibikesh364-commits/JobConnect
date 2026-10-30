<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        private readonly JobApplication $application,
        private readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jobTitle = $this->application->job?->title ?? 'a job';

        [$title, $message] = match ($this->status) {
            'submitted' => ['Application received', "Your application for {$jobTitle} has been received."],
            'reviewing' => ['Application under review', "Your application for {$jobTitle} is under review."],
            'shortlisted' => ['You have been shortlisted', "You have been shortlisted for {$jobTitle}."],
            'rejected' => ['Application update', "Your application for {$jobTitle} was not selected."],
            default => ['Application status updated', "Your application for {$jobTitle} has a status update."],
        };

        return [
            'title' => $title,
            'message' => $message,
            'status' => $this->status,
            'application_id' => $this->application->getKey(),
            'job_id' => $this->application->job_listing_id,
        ];
    }
}
