<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationType;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\AlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlertDispatchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function an_admin_can_send_an_alert_to_several_users(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        [$sara, $omar] = User::factory()->count(2)->create();

        $this->actingAs($admin)->postJson('/api/v1/alerts', [
            'type' => 'trip_delayed',
            'user_ids' => [$sara->id, $omar->id],
            'data' => ['trip' => 'B-12', 'delay' => 10],
        ])
            ->assertAccepted()
            ->assertJsonPath('recipients', 2);

        Notification::assertSentTo([$sara, $omar], AlertNotification::class, function (AlertNotification $n) {
            return $n->type === NotificationType::TripDelayed && $n->data === ['trip' => 'B-12', 'delay' => 10];
        });
        Notification::assertNotSentTo($admin, AlertNotification::class);
    }

    #[Test]
    public function regular_users_cannot_send_alerts(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/v1/alerts', [
            'type' => 'trip_started', 'user_ids' => [$user->id], 'data' => ['trip' => 'B-1'],
        ])->assertForbidden();

        Notification::assertNothingSent();
    }

    #[Test]
    public function the_required_data_depends_on_the_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/v1/alerts', [
            'type' => 'vehicle_maintenance', 'user_ids' => [$admin->id], 'data' => ['vehicle' => 'Bus 3'],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('data.date')
            ->assertJsonMissingValidationErrors('data.vehicle');
    }

    #[Test]
    public function a_custom_message_makes_the_data_optional(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/v1/alerts', [
            'type' => 'vehicle_maintenance', 'user_ids' => [$admin->id], 'message' => 'All buses to the garage on Friday.',
        ])->assertAccepted();

        Notification::assertSentTo($admin, AlertNotification::class, fn ($n) => $n->customMessage === 'All buses to the garage on Friday.');
    }

    #[Test]
    public function it_validates_type_and_recipients(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/v1/alerts', ['type' => 'meteor_strike', 'user_ids' => [999]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['type', 'user_ids.0']);

        $this->actingAs($admin)->postJson('/api/v1/alerts', ['type' => 'trip_started', 'user_ids' => []])
            ->assertJsonValidationErrors('user_ids');
    }

    #[Test]
    public function preferences_decide_which_channels_are_used_end_to_end(): void
    {
        $admin = User::factory()->admin()->create();
        $muted = User::factory()->create();
        NotificationPreference::factory()->for($muted)->create(['type' => NotificationType::TripStarted, 'in_app' => false, 'email' => false]);
        $listening = User::factory()->create();

        $this->actingAs($admin)->postJson('/api/v1/alerts', [
            'type' => 'trip_started', 'user_ids' => [$muted->id, $listening->id], 'data' => ['trip' => 'B-12'],
        ])->assertAccepted();

        $this->assertSame(0, $muted->notifications()->count());
        $this->assertSame(1, $listening->notifications()->count());
        $this->assertSame('trip_started', $listening->notifications()->first()->type);
    }

    #[Test]
    public function it_lists_the_available_alert_types(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson('/api/v1/alerts/types?lang=fr')
            ->assertOk()
            ->assertJsonCount(count(NotificationType::cases()), 'data')
            ->assertJsonFragment([
                'type' => 'trip_delayed',
                'label' => 'Trajet en retard',
                'severity' => 'warning',
                'required_data' => ['trip', 'delay'],
            ]);
    }
}
