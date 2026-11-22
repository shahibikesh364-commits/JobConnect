<?php

namespace App\Notifications;

use App\Models\EmployerSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployerSettingsNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $category,
        private readonly string $title,
        private readonly string $message,
        private readonly array $data = [],
    ) {}

    public function via(object $notifiable): array
    {
        if (!$notifiable instanceof User || !$notifiable->isEmployer()) return [];

        $preferences = EmployerSetting::where('employer_id', $notifiable->employerWorkspaceId())
            ->first()?->notification_preferences ?? [];
        $channels = $preferences[$this->category] ?? ['email' => true, 'in_app' => true];
        $via = [];
        // Missing channel keys should keep the app's enabled-by-default behavior.
        if ($channels['in_app'] ?? true) $via[] = 'database';
        if ($channels['email'] ?? true) $via[] = 'mail';

        return $via;
    }

    public function toDatabase(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, ...$this->data];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->message)
            ->action('Open JobConnect', route('employer.dashboard'));
    }
}
