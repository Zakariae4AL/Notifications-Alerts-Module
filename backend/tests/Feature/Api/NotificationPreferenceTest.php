<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationPreferenceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_the_full_matrix_with_defaults(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/notification-preferences')
            ->assertOk()
            ->assertJsonCount(count(NotificationType::cases()), 'data');

        $rows = collect($response->json('data'))->keyBy('type');
        $this->assertSame(['type' => 'trip_started', 'label' => 'Trip started', 'severity' => 'info', 'in_app' => true, 'email' => false], $rows['trip_started']);
        $this->assertTrue($rows['passenger_absent']['email']);
    }

    #[Test]
    public function preferences_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', [
            'preferences' => [
                ['type' => 'trip_started', 'in_app' => false, 'email' => true],
                ['type' => 'passenger_absent', 'email' => false],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('message', 'Your notification preferences have been saved.');

        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id, 'type' => 'trip_started', 'in_app' => false, 'email' => true]);
        // Omitted channel falls back to the type default.
        $this->assertDatabaseHas('notification_preferences', ['user_id' => $user->id, 'type' => 'passenger_absent', 'in_app' => true, 'email' => false]);

        $rows = collect($this->actingAs($user)->getJson('/api/v1/notification-preferences')->json('data'))->keyBy('type');
        $this->assertFalse($rows['trip_started']['in_app']);
        $this->assertTrue($rows['trip_started']['email']);
    }

    #[Test]
    public function updating_twice_does_not_create_duplicates(): void
    {
        $user = User::factory()->create();
        $payload = ['preferences' => [['type' => 'trip_started', 'in_app' => false]]];

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', $payload)->assertOk();
        $this->actingAs($user)->putJson('/api/v1/notification-preferences', $payload)->assertOk();

        $this->assertSame(1, $user->notificationPreferences()->count());
    }

    #[Test]
    public function it_validates_the_payload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('preferences');

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', [
            'preferences' => [
                ['type' => 'unknown_type'],
                ['type' => 'trip_started', 'in_app' => 'maybe'],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['preferences.0.type', 'preferences.1.in_app']);

        $this->actingAs($user)->putJson('/api/v1/notification-preferences', [
            'preferences' => [['type' => 'trip_started'], ['type' => 'trip_started']],
        ])->assertJsonValidationErrors('preferences.0.type');
    }

    #[Test]
    public function preferences_are_scoped_to_the_user(): void
    {
        $sara = User::factory()->create();
        $omar = User::factory()->create();

        $this->actingAs($sara)->putJson('/api/v1/notification-preferences', [
            'preferences' => [['type' => 'trip_started', 'in_app' => false]],
        ])->assertOk();

        $rows = collect($this->actingAs($omar)->getJson('/api/v1/notification-preferences')->json('data'))->keyBy('type');
        $this->assertTrue($rows['trip_started']['in_app']);
    }
}
