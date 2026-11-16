<?php

namespace Tests\Feature\Api;

use App\Enums\NotificationType;
use App\Models\User;
use App\Notifications\AlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    private function notify(User $user, NotificationType $type = NotificationType::TripStarted, array $data = ['trip' => 'B-12'], ?string $message = null): DatabaseNotification
    {
        $user->notifyNow(new AlertNotification($type, $data, $message), ['database']);

        return $user->notifications()->latest()->first();
    }

    #[Test]
    public function it_lists_only_the_authenticated_users_notifications(): void
    {
        $this->notify($this->user);
        $this->notify($this->user, NotificationType::TripCompleted);
        $this->notify(User::factory()->create());

        $this->actingAs($this->user)->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.unread_count', 2)
            ->assertJsonStructure(['data' => [['id', 'type', 'label', 'severity', 'message', 'payload', 'is_read', 'read_at', 'created_at']], 'links', 'meta']);
    }

    #[Test]
    public function messages_are_rendered_in_the_requested_language(): void
    {
        $this->notify($this->user, NotificationType::PassengerBoarded, ['passenger' => 'Yanis', 'trip' => 'B-12']);

        $this->actingAs($this->user)->getJson('/api/v1/notifications')
            ->assertJsonPath('data.0.message', 'Yanis has boarded trip B-12.')
            ->assertJsonPath('data.0.label', 'Passenger boarded');

        $this->actingAs($this->user)->getJson('/api/v1/notifications?lang=fr')
            ->assertJsonPath('data.0.message', 'Yanis est monté(e) à bord du trajet B-12.')
            ->assertJsonPath('data.0.label', 'Passager à bord');
    }

    #[Test]
    public function the_users_saved_locale_wins_over_the_browser_language(): void
    {
        $user = User::factory()->french()->create();
        $this->notify($user);

        $this->withToken($user->createToken('t')->plainTextToken)
            ->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->getJson('/api/v1/notifications')
            ->assertJsonPath('data.0.message', 'Le trajet B-12 a démarré.');
    }

    #[Test]
    public function a_custom_message_is_returned_as_is(): void
    {
        $this->notify($this->user, NotificationType::TripDelayed, [], 'Road works on the bridge.');

        $this->actingAs($this->user)->getJson('/api/v1/notifications')
            ->assertJsonPath('data.0.message', 'Road works on the bridge.');
    }

    #[Test]
    public function it_filters_by_status_and_type(): void
    {
        $this->notify($this->user)->markAsRead();
        $this->notify($this->user, NotificationType::TripDelayed, ['trip' => 'B-1', 'delay' => 5]);
        $this->notify($this->user, NotificationType::TripCompleted);

        $this->actingAs($this->user)->getJson('/api/v1/notifications?status=unread')->assertJsonCount(2, 'data');
        $this->actingAs($this->user)->getJson('/api/v1/notifications?status=read')->assertJsonCount(1, 'data');
        $this->actingAs($this->user)->getJson('/api/v1/notifications?type=trip_delayed')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'trip_delayed')
            ->assertJsonPath('data.0.severity', 'warning');
    }

    #[Test]
    public function it_rejects_invalid_filters(): void
    {
        $this->actingAs($this->user)->getJson('/api/v1/notifications?status=archived&type=nope&per_page=1000')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status', 'type', 'per_page']);
    }

    #[Test]
    public function it_paginates(): void
    {
        foreach (range(1, 20) as $i) {
            $this->notify($this->user, data: ['trip' => "B-{$i}"]);
        }

        $this->actingAs($this->user)->getJson('/api/v1/notifications?per_page=5&page=2')
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 20);
    }

    #[Test]
    public function it_returns_the_unread_count(): void
    {
        $this->notify($this->user);
        $this->notify($this->user)->markAsRead();

        $this->actingAs($this->user)->getJson('/api/v1/notifications/unread-count')
            ->assertOk()
            ->assertExactJson(['unread_count' => 1]);
    }

    #[Test]
    public function a_notification_can_be_marked_as_read_and_unread(): void
    {
        $notification = $this->notify($this->user);

        $this->actingAs($this->user)->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);
        $this->assertNotNull($notification->fresh()->read_at);

        $this->actingAs($this->user)->patchJson("/api/v1/notifications/{$notification->id}/unread")
            ->assertOk()
            ->assertJsonPath('data.is_read', false);
        $this->assertNull($notification->fresh()->read_at);
    }

    #[Test]
    public function all_notifications_can_be_marked_as_read(): void
    {
        $this->notify($this->user);
        $this->notify($this->user);
        $other = User::factory()->create();
        $this->notify($other);

        $this->actingAs($this->user)->patchJson('/api/v1/notifications/read-all')
            ->assertOk()
            ->assertExactJson(['updated' => 2]);

        $this->assertSame(0, $this->user->unreadNotifications()->count());
        $this->assertSame(1, $other->unreadNotifications()->count(), 'Other users must not be affected');
    }

    #[Test]
    public function a_notification_can_be_deleted(): void
    {
        $notification = $this->notify($this->user);

        $this->actingAs($this->user)->deleteJson("/api/v1/notifications/{$notification->id}")->assertNoContent();

        $this->assertModelMissing($notification);
    }

    #[Test]
    public function a_user_cannot_touch_someone_elses_notification(): void
    {
        $notification = $this->notify(User::factory()->create());

        $this->actingAs($this->user)->patchJson("/api/v1/notifications/{$notification->id}/read")->assertNotFound();
        $this->actingAs($this->user)->deleteJson("/api/v1/notifications/{$notification->id}")->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
        $this->assertModelExists($notification);
    }
}
