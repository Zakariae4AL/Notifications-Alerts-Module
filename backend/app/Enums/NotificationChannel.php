<?php

namespace App\Enums;

/**
 * Delivery channels exposed to users, mapped to Laravel notification channels.
 */
enum NotificationChannel: string
{
    case InApp = 'in_app';
    case Email = 'email';

    public function driver(): string
    {
        return match ($this) {
            self::InApp => 'database',
            self::Email => 'mail',
        };
    }
}
