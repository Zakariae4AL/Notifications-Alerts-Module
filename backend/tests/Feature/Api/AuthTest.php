<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_user_can_log_in_and_receive_a_token(): void
    {
        $user = User::factory()->create(['email' => 'sara@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'sara@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'locale', 'is_admin']])
            ->assertJsonPath('user.id', $user->id);

        $this->assertCount(1, $user->tokens);
    }

    #[Test]
    public function wrong_credentials_are_rejected(): void
    {
        User::factory()->create(['email' => 'sara@example.com']);

        $this->postJson('/api/v1/auth/login', ['email' => 'sara@example.com', 'password' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    #[Test]
    public function errors_are_returned_in_french_when_asked(): void
    {
        $this->withHeader('Accept-Language', 'fr')
            ->postJson('/api/v1/auth/login', ['email' => 'nobody@example.com', 'password' => 'x'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Ces identifiants ne correspondent à aucun compte.');
    }

    #[Test]
    public function login_is_rate_limited(): void
    {
        foreach (range(1, 5) as $_) {
            $this->postJson('/api/v1/auth/login', ['email' => 'x@example.com', 'password' => 'x'])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'x@example.com', 'password' => 'x'])->assertTooManyRequests();
    }

    #[Test]
    public function protected_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/notifications')->assertUnauthorized();
        $this->getJson('/api/v1/notification-preferences')->assertUnauthorized();
        $this->postJson('/api/v1/alerts')->assertUnauthorized();
    }

    #[Test]
    public function a_user_can_fetch_and_update_their_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', $user->email);

        $this->withToken($token)->patchJson('/api/v1/me', ['locale' => 'fr'])
            ->assertOk()
            ->assertJsonPath('data.locale', 'fr');

        $this->withToken($token)->patchJson('/api/v1/me', ['locale' => 'de'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('locale');
    }

    #[Test]
    public function the_admin_flag_cannot_be_mass_assigned(): void
    {
        $user = User::factory()->create();

        $this->withToken($user->createToken('t')->plainTextToken)
            ->patchJson('/api/v1/me', ['is_admin' => true])
            ->assertOk()
            ->assertJsonPath('data.is_admin', false);
    }

    #[Test]
    public function logging_out_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
