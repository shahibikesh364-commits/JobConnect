<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\EmployerSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class MessageReceived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Message $message,
        private readonly string $senderName,
        private readonly ?string $jobTitle,
    ) {}

    public function via(object $notifiable): array
    {
        if (!$notifiable instanceof User || !$notifiable->isEmployer()) return ['database'];
        $preferences = EmployerSetting::where('employer_id', $notifiable->employerWorkspaceId())
            ->first()?->notification_preferences ?? [];
        $channels = $preferences['candidate_message'] ?? ['email' => true, 'in_app' => true];
        $via = [];
        if ($channels['in_app'] ?? false) $via[] = 'database';
        if ($channels['email'] ?? false) $via[] = 'mail';
        return $via;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New message from ' . $this->senderName,
            'message' => Str::limit($this->message->body, 180),
            'conversation_id' => $this->message->conversation_id,
            'message_id' => $this->message->getKey(),
            'job_id' => $this->message->conversation?->job_listing_id,
            'job_title' => $this->jobTitle,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('New candidate message from ' . $this->senderName)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line(Str::limit($this->message->body, 180))
            ->action('Open messages', route('messages.index'));
    }
}
