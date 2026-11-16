<?php

namespace Database\Seeders;

use App\Enums\NotificationType;
use App\Models\User;
use App\Services\AlertDispatcher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data: one dispatcher (admin) and one parent with a populated inbox.
     */
    public function run(AlertDispatcher $alerts): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin Dispatcher',
            'email' => 'admin@example.com',
        ]);

        $parent = User::factory()->french()->create([
            'name' => 'Sara Parent',
            'email' => 'parent@example.com',
        ]);

        $samples = [
            [NotificationType::TripStarted, ['trip' => 'B-12']],
            [NotificationType::PassengerBoarded, ['passenger' => 'Yanis', 'trip' => 'B-12']],
            [NotificationType::TripDelayed, ['trip' => 'B-12', 'delay' => 15]],
            [NotificationType::TripCompleted, ['trip' => 'B-12']],
            [NotificationType::PassengerAbsent, ['passenger' => 'Lina', 'trip' => 'B-07']],
            [NotificationType::VehicleMaintenance, ['vehicle' => 'Bus 3', 'date' => now()->addDays(3)->toDateString()]],
            [NotificationType::DocumentExpiration, ['document' => 'Insurance', 'date' => now()->addWeek()->toDateString()]],
        ];

        // Spread the samples over the last few hours so the inbox looks realistic.
        $now = now();
        foreach ($samples as $i => [$type, $data]) {
            Carbon::setTestNow($now->copy()->subMinutes((count($samples) - $i) * 25));
            $alerts->dispatch($type, [$parent], $data);
        }
        Carbon::setTestNow();

        // Leave the oldest ones read so the inbox shows both states.
        $parent->notifications()->reorder('created_at')->take(2)->get()->each->markAsRead();
    }
}
