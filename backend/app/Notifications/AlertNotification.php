<?php

namespace App\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Enums\Severity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A single business alert (trip started, passenger absent, ...).
 *
 * Delivery channels are resolved per recipient from their preferences,
 * and the message is stored as type + data so it can be rendered
 * in the reader's language later on.
 */
class AlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, scalar|null>  $data
     */
    public function __construct(
        public readonly NotificationType $type,
        public readonly array $data = [],
        public readonly ?string $customMessage = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return collect(NotificationChannel::cases())
            ->filter(fn (NotificationChannel $channel) => $notifiable->wantsNotification($this->type, $channel))
            ->map(fn (NotificationChannel $channel) => $channel->driver())
            ->values()
            ->all();
    }

    public function databaseType(object $notifiable): string
    {
        return $this->type->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type->value,
            'severity' => $this->type->severity()->value,
            'data' => $this->data,
            'custom_message' => $this->customMessage,
        ];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject(__('notifications.mail.subject', ['label' => $this->type->label()]))
            ->greeting(__('notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line($this->message())
            ->salutation(__('notifications.mail.salutation'));

        return $this->type->severity() === Severity::Critical ? $mail->error() : $mail;
    }

    public function message(): string
    {
        return $this->customMessage ?? $this->type->message($this->data);
    }
}
