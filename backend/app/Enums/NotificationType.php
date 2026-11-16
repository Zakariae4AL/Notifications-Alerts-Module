<?php

namespace App\Enums;

/**
 * Every business event the module can notify users about.
 *
 * Each type declares its severity, the data it needs to build its message
 * and whether e-mail delivery is enabled by default.
 */
enum NotificationType: string
{
    case TripStarted = 'trip_started';
    case TripDelayed = 'trip_delayed';
    case TripCompleted = 'trip_completed';
    case PassengerBoarded = 'passenger_boarded';
    case PassengerAbsent = 'passenger_absent';
    case VehicleMaintenance = 'vehicle_maintenance';
    case DocumentExpiration = 'document_expiration';

    public function severity(): Severity
    {
        return match ($this) {
            self::TripStarted, self::TripCompleted, self::PassengerBoarded => Severity::Info,
            self::TripDelayed, self::VehicleMaintenance => Severity::Warning,
            self::PassengerAbsent, self::DocumentExpiration => Severity::Critical,
        };
    }

    /**
     * Keys that must be present in the notification payload to render the message.
     *
     * @return list<string>
     */
    public function requiredData(): array
    {
        return match ($this) {
            self::TripStarted, self::TripCompleted => ['trip'],
            self::TripDelayed => ['trip', 'delay'],
            self::PassengerBoarded, self::PassengerAbsent => ['passenger', 'trip'],
            self::VehicleMaintenance => ['vehicle', 'date'],
            self::DocumentExpiration => ['document', 'date'],
        };
    }

    /**
     * Whether a channel is enabled when the user has not set a preference yet.
     * In-app is always on; e-mail is on for anything that is not purely informational.
     */
    public function enabledByDefault(NotificationChannel $channel): bool
    {
        return match ($channel) {
            NotificationChannel::InApp => true,
            NotificationChannel::Email => $this->severity() !== Severity::Info,
        };
    }

    public function label(): string
    {
        return __("notifications.types.{$this->value}.label");
    }

    /**
     * @param  array<string, scalar|null>  $data
     */
    public function message(array $data = []): string
    {
        return __("notifications.types.{$this->value}.message", array_map('strval', $data));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
