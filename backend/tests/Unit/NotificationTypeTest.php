<?php

namespace Tests\Unit;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Enums\Severity;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{NotificationType}>
     */
    public static function types(): iterable
    {
        foreach (NotificationType::cases() as $type) {
            yield $type->value => [$type];
        }
    }

    #[Test]
    #[DataProvider('types')]
    public function every_type_is_translated_in_english_and_french(NotificationType $type): void
    {
        foreach (['en', 'fr'] as $locale) {
            App::setLocale($locale);

            $this->assertNotSame("notifications.types.{$type->value}.label", $type->label(), "Missing {$locale} label");
            $this->assertNotSame("notifications.types.{$type->value}.message", $type->message(), "Missing {$locale} message");
        }
    }

    #[Test]
    #[DataProvider('types')]
    public function every_placeholder_in_the_message_is_a_required_data_key(NotificationType $type): void
    {
        $data = array_fill_keys($type->requiredData(), 'X');

        foreach (['en', 'fr'] as $locale) {
            App::setLocale($locale);
            $this->assertDoesNotMatchRegularExpression('/:[a-z_]+/', $type->message($data), "Unfilled placeholder in {$locale}");
        }
    }

    #[Test]
    public function it_builds_a_localized_message_from_data(): void
    {
        App::setLocale('en');
        $this->assertSame('Trip B-12 is delayed by 15 minutes.', NotificationType::TripDelayed->message(['trip' => 'B-12', 'delay' => 15]));

        App::setLocale('fr');
        $this->assertSame('Le trajet B-12 a un retard de 15 minutes.', NotificationType::TripDelayed->message(['trip' => 'B-12', 'delay' => 15]));
    }

    #[Test]
    public function in_app_is_always_enabled_by_default_and_email_only_for_important_types(): void
    {
        foreach (NotificationType::cases() as $type) {
            $this->assertTrue($type->enabledByDefault(NotificationChannel::InApp));
            $this->assertSame($type->severity() !== Severity::Info, $type->enabledByDefault(NotificationChannel::Email));
        }
    }

    #[Test]
    public function severities_are_mapped_as_expected(): void
    {
        $this->assertSame(Severity::Info, NotificationType::TripStarted->severity());
        $this->assertSame(Severity::Warning, NotificationType::TripDelayed->severity());
        $this->assertSame(Severity::Critical, NotificationType::PassengerAbsent->severity());
    }

    #[Test]
    public function channels_map_to_laravel_drivers(): void
    {
        $this->assertSame('database', NotificationChannel::InApp->driver());
        $this->assertSame('mail', NotificationChannel::Email->driver());
    }
}
