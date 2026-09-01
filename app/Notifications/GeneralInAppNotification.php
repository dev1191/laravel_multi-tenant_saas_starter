<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GeneralInAppNotification extends Notification
{
    use Queueable;

    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info', // info, success, warning, danger
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public array $extra = []
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'extra' => $this->extra,
        ];
    }
}
