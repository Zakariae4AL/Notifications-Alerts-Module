<?php

namespace Tests\Unit;

use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\AlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlertNotificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_type_defaults_when_the_user_has_no_preference(): void
    {
        $user = User::factory()->create();

        $this->assertSame(['database'], (new AlertNotification(NotificationType::TripStarted))->via($user));
        $this->assertSame(['database', 'mail'], (new AlertNotification(NotificationType::PassengerAbsent))->via($user));
    }

    #[Test]
    public function it_respects_stored_preferences(): void
    {
        $user = User::factory()->create();
        NotificationPreference::factory()->for($user)->create([
            'type' => NotificationType::TripStarted, 'in_app' => false, 'email' => true,
        ]);

        $this->assertSame(['mail'], (new AlertNotification(NotificationType::TripStarted))->via($user->fresh()));
    }

    #[Test]
    public function it_sends_nothing_when_every_channel_is_disabled(): void
    {
        $user = User::factory()->create();
        NotificationPreference::factory()->for($user)->create([
            'type' => NotificationType::PassengerAbsent, 'in_app' => false, 'email' => false,
        ]);

        $this->assertSame([], (new AlertNotification(NotificationType::PassengerAbsent))->via($user->fresh()));
    }

    #[Test]
    public function it_stores_type_and_data_rather_than_a_rendered_message(): void
    {
        $user = User::factory()->create();
        $notification = new AlertNotification(NotificationType::TripStarted, ['trip' => 'B-12']);

        $this->assertSame('trip_started', $notification->databaseType($user));
        $this->assertSame([
            'type' => 'trip_started',
            'severity' => 'info',
            'data' => ['trip' => 'B-12'],
            'custom_message' => null,
        ], $notification->toArray($user));
    }

    #[Test]
    public function the_mail_is_built_in_the_current_locale(): void
    {
        $user = User::factory()->create(['name' => 'Sara']);
        $notification = new AlertNotification(NotificationType::TripStarted, ['trip' => 'B-12']);

        App::setLocale('fr');
        $mail = $notification->toMail($user);

        $this->assertSame('Alerte : Trajet démarré', $mail->subject);
        $this->assertSame('Bonjour Sara,', $mail->greeting);
        $this->assertContains('Le trajet B-12 a démarré.', $mail->introLines);
    }

    #[Test]
    public function critical_alerts_use_the_error_mail_style(): void
    {
        $user = User::factory()->create();

        $this->assertSame('error', (new AlertNotification(NotificationType::DocumentExpiration, ['document' => 'Insurance', 'date' => '2026-12-01']))->toMail($user)->level);
        $this->assertSame('info', (new AlertNotification(NotificationType::TripStarted, ['trip' => 'B-1']))->toMail($user)->level);
    }

    #[Test]
    public function a_custom_message_overrides_the_template(): void
    {
        $notification = new AlertNotification(NotificationType::TripDelayed, [], 'Road closed, expect delays.');

        $this->assertSame('Road closed, expect delays.', $notification->message());
    }
}
