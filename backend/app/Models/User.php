<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const SUPPORTED_LOCALES = ['en', 'fr'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * @return HasMany<NotificationPreference, $this>
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Whether the user wants to receive the given type on the given channel,
     * falling back to the type's default when no preference is stored.
     */
    public function wantsNotification(NotificationType $type, NotificationChannel $channel): bool
    {
        $preference = $this->notificationPreferences->firstWhere('type', $type);

        if ($preference === null) {
            return $type->enabledByDefault($channel);
        }

        return (bool) $preference->{$channel->value};
    }

    /**
     * Used by Laravel to send e-mails in the user's language.
     */
    public function preferredLocale(): string
    {
        return $this->locale ?? config('app.locale');
    }
}
