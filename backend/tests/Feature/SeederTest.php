<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function the_demo_seeder_creates_usable_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $parent = User::where('email', 'parent@example.com')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertFalse($parent->is_admin);
        $this->assertSame(7, $parent->notifications()->count());
        $this->assertSame(5, $parent->unreadNotifications()->count());
    }

    #[Test]
    public function the_root_and_health_endpoints_respond(): void
    {
        $this->getJson('/')->assertOk()->assertJsonStructure(['name', 'api', 'health']);
        $this->get('/up')->assertOk();
    }
}
