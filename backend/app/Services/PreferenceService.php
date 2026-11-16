<?php

namespace App\Services;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PreferenceService
{
    /**
     * Full preference matrix for a user: every type, every channel,
     * with defaults filled in for types the user never configured.
     *
     * @return list<array{type: string, label: string, severity: string, in_app: bool, email: bool}>
     */
    public function matrix(User $user): array
    {
        $user->loadMissing('notificationPreferences');

        return array_map(fn (NotificationType $type) => [
            'type' => $type->value,
            'label' => $type->label(),
            'severity' => $type->severity()->value,
            'in_app' => $user->wantsNotification($type, NotificationChannel::InApp),
            'email' => $user->wantsNotification($type, NotificationChannel::Email),
        ], NotificationType::cases());
    }

    /**
     * @param  list<array{type: string, in_app?: bool, email?: bool}>  $preferences
     */
    public function update(User $user, array $preferences): void
    {
        DB::transaction(function () use ($user, $preferences) {
            foreach ($preferences as $row) {
                $type = NotificationType::from($row['type']);

                $user->notificationPreferences()->updateOrCreate(
                    ['type' => $type],
                    [
                        'in_app' => $row['in_app'] ?? $type->enabledByDefault(NotificationChannel::InApp),
                        'email' => $row['email'] ?? $type->enabledByDefault(NotificationChannel::Email),
                    ],
                );
            }
        });

        $user->unsetRelation('notificationPreferences');
    }
}
