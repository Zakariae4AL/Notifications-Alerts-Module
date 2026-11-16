<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\AlertNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Entry point for the rest of the application to raise alerts.
 */
class AlertDispatcher
{
    /**
     * Send an alert to the given users and return how many were targeted.
     *
     * @param  iterable<User>  $recipients
     * @param  array<string, scalar|null>  $data
     */
    public function dispatch(
        NotificationType $type,
        iterable $recipients,
        array $data = [],
        ?string $customMessage = null,
    ): int {
        $users = collect($recipients)->each->loadMissing('notificationPreferences');

        if ($users->isEmpty()) {
            return 0;
        }

        Notification::send($users, new AlertNotification($type, $data, $customMessage));

        return $users->count();
    }
}
